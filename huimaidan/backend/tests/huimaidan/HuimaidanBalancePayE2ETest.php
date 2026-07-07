<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\model\store\order\StoreGroupOrder;
use app\common\repositories\huimaidan\DiscountEngineRepository;
use app\common\repositories\huimaidan\OrderRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\system\config\ConfigValueRepository;
use app\common\repositories\user\UserRepository;
use think\App;
use think\facade\Db;

(new App())->initialize();

$options = getopt('', [
    'run',
    'fund',
    'enable-balance',
    'uid::',
    'mer-id::',
    'amount::',
]);

$run = array_key_exists('run', $options);
$fund = array_key_exists('fund', $options);
$enableBalance = array_key_exists('enable-balance', $options);
$uid = (int)($options['uid'] ?? 2);
$merId = (int)($options['mer-id'] ?? 2061);
$amount = money($options['amount'] ?? '10.00');
$mark = 'HMD_E2E_TEST_' . date('Ymd_His');

/** @var UserRepository $userRepo */
$userRepo = app()->make(UserRepository::class);
/** @var MerchantRepository $merchantRepo */
$merchantRepo = app()->make(MerchantRepository::class);
/** @var DiscountEngineRepository $discountEngine */
$discountEngine = app()->make(DiscountEngineRepository::class);
/** @var OrderRepository $orderRepo */
$orderRepo = app()->make(OrderRepository::class);

$user = $userRepo->get($uid);
if (!$user) {
    fail("测试用户不存在：uid={$uid}");
}
$merchant = $merchantRepo->get($merId);
if (!$merchant) {
    fail("测试商户不存在：mer_id={$merId}");
}

$discount = $discountEngine->calculate($merId, $amount, $uid, true);
$before = snapshot($uid, $merId);

line('惠买单余额支付闭环测试');
line('模式：' . ($run ? '真实运行' : 'dry-run'));
line("用户：uid={$uid}, member_level={$user->member_level}, now_money={$before['user_money']}");
line("商户：mer_id={$merId}, settlement_mode={$merchant->huimaidan_settlement_mode}, mer_money={$before['merchant_money']}");
line("金额：original={$discount['original_amount']}, pay={$discount['pay_amount']}, merchant_cost={$discount['merchant_cost_amount']}, platform_profit={$discount['platform_profit']}");
line('余额支付配置：yue_pay_status=' . json_encode(systemConfig('yue_pay_status')) . ', balance_func_status=' . json_encode(systemConfig('balance_func_status')));

if (!$run) {
    line('dry-run 完成。真实执行请追加：--run --fund --enable-balance');
    exit(0);
}

$configBackup = backupConfig(['yue_pay_status', 'balance_func_status']);

try {
    if ($enableBalance) {
        setConfig(['yue_pay_status' => '1', 'balance_func_status' => '1']);
        line('已临时开启余额支付配置');
    }

    if (!truthy(systemConfig('yue_pay_status')) || !truthy(systemConfig('balance_func_status'))) {
        fail('余额支付未开启；如需脚本临时开启，请增加 --enable-balance');
    }

    $needFund = bcsub($discount['pay_amount'], $before['user_money'], 2);
    if (bccomp($needFund, '0.00', 2) > 0) {
        if (!$fund) {
            fail("测试用户余额不足，还差 {$needFund}；如需自动补足，请增加 --fund");
        }
        $userRepo->changeNowMoney($uid, 0, 1, $needFund);
        line("已给测试用户补足余额：{$needFund}");
    }

    $prepared = $orderRepo->prepare($userRepo->get($uid), [
        'mer_id' => $merId,
        'amount' => $amount,
        'mark' => $mark,
        'use_member_discount' => 1,
    ]);

    $groupOrderId = (int)$prepared['group_order_id'];
    if ($groupOrderId <= 0) {
        fail('创建待支付订单失败：未返回 group_order_id');
    }

    $payResult = $orderRepo->pay($userRepo->get($uid), $groupOrderId, 'balance');
    $groupOrder = StoreGroupOrder::getDB()->where('group_order_id', $groupOrderId)->with(['orderList'])->find();
    if (!$groupOrder || empty($groupOrder->orderList[0])) {
        fail("支付后未找到订单：group_order_id={$groupOrderId}");
    }

    $order = $groupOrder->orderList[0];
    assertSame('1', (string)$groupOrder->paid, '组合订单应已支付');
    assertSame('1', (string)$order->paid, '惠买单订单应已支付');
    assertSame($discount['pay_amount'], money($order->pay_price), '用户实付金额不一致');
    assertSame($discount['saved_amount'], money($order->coupon_price), '优惠金额不一致');
    assertSame($discount['merchant_cost_amount'], money($order->merchant_cost_amount), '商户结算金额不一致');
    assertSame($discount['platform_profit'], money($order->platform_profit), '平台差价不一致');

    $after = snapshot($uid, $merId);
    $merchantDelta = money(bcsub($after['merchant_money'], $before['merchant_money'], 2));
    if ((int)$order->settlement_mode === MerchantRepository::HUIMAIDAN_SETTLEMENT_WITHDRAW) {
        assertSame($discount['merchant_cost_amount'], $merchantDelta, '提现模式商户余额应增加商户结算金额');
        $financial = Db::name('financial_record')
            ->where('financial_type', 'huimaidan_order_income')
            ->where('order_id', (int)$order->order_id)
            ->where('mer_id', $merId)
            ->find();
        if (!$financial) {
            fail('提现模式未生成商户财务流水');
        }
        assertSame($discount['merchant_cost_amount'], money($financial['number']), '商户财务流水金额应等于商户结算金额');
    } else {
        if (empty($order->pool_transaction_id)) {
            fail('垫资池模式未生成垫资池扣减流水');
        }
        $poolTransaction = Db::name('huimaidan_pool_transaction')->where('transaction_id', (int)$order->pool_transaction_id)->find();
        if (!$poolTransaction) {
            fail('垫资池流水不存在');
        }
        assertSame($discount['merchant_cost_amount'], money($poolTransaction['amount']), '垫资池扣减金额应等于商户结算金额');
    }

    $stats = app()->make(\app\common\repositories\huimaidan\SettlementRepository::class)->stats(['mer_id' => $merId]);

    line('闭环测试通过');
    line("订单：group_order_id={$groupOrderId}, order_id={$order->order_id}, order_sn={$order->order_sn}, mark={$mark}");
    line("对账：pay={$order->pay_price}, merchant_cost={$order->merchant_cost_amount}, platform_profit={$order->platform_profit}, merchant_delta={$merchantDelta}");
    line('结算统计：' . json_encode($stats, JSON_UNESCAPED_UNICODE));
    line('支付返回：' . json_encode($payResult, JSON_UNESCAPED_UNICODE));
} finally {
    if ($enableBalance) {
        restoreConfig($configBackup);
        line('已恢复余额支付配置');
    }
}

function snapshot(int $uid, int $merId): array
{
    return [
        'user_money' => money(Db::name('user')->where('uid', $uid)->value('now_money') ?? 0),
        'merchant_money' => money(Db::name('merchant')->where('mer_id', $merId)->value('mer_money') ?? 0),
    ];
}

function backupConfig(array $keys): array
{
    $rows = Db::name('system_config_value')->where('mer_id', 0)->whereIn('config_key', $keys)->select()->toArray();
    $backup = [];
    foreach ($keys as $key) {
        $backup[$key] = null;
    }
    foreach ($rows as $row) {
        $backup[$row['config_key']] = $row;
    }
    return $backup;
}

function setConfig(array $values): void
{
    foreach ($values as $key => $value) {
        $exists = Db::name('system_config_value')->where('mer_id', 0)->where('config_key', $key)->find();
        if ($exists) {
            Db::name('system_config_value')->where('config_value_id', $exists['config_value_id'])->update(['value' => $value]);
        } else {
            Db::name('system_config_value')->insert(['config_key' => $key, 'value' => $value, 'mer_id' => 0]);
        }
    }
    app()->make(ConfigValueRepository::class)->syncConfig();
}

function restoreConfig(array $backup): void
{
    foreach ($backup as $key => $row) {
        if ($row === null) {
            Db::name('system_config_value')->where('mer_id', 0)->where('config_key', $key)->delete();
        } else {
            Db::name('system_config_value')->where('config_value_id', $row['config_value_id'])->update(['value' => $row['value']]);
        }
    }
    app()->make(ConfigValueRepository::class)->syncConfig();
}

function assertSame(string $expected, string $actual, string $message): void
{
    if ($expected !== $actual) {
        fail($message . "：expected={$expected}, actual={$actual}");
    }
}

function truthy($value): bool
{
    return in_array($value, [1, '1', true, 'true', 'on'], true);
}

function money($amount): string
{
    return number_format(round((float)$amount, 2), 2, '.', '');
}

function line(string $message): void
{
    echo $message . PHP_EOL;
}

function fail(string $message): void
{
    throw new RuntimeException($message);
}
