<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\system\merchant\MerchantRepository;
use think\exception\ValidateException;

$reflection = new ReflectionClass(MerchantRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$method = $reflection->getMethod('normalizeHuimaidanSettlementConfig');
$method->setAccessible(true);

$data = $method->invoke($repository, [
    'huimaidan_settlement_mode' => '2',
    'huimaidan_withdraw_rate' => '2.5',
]);
if ($data['huimaidan_settlement_mode'] !== 2 || $data['huimaidan_withdraw_rate'] !== '2.50') {
    throw new RuntimeException('商户惠买单提现模式配置应被规范化');
}

$data = $method->invoke($repository, []);
if ($data['huimaidan_settlement_mode'] !== 1 || $data['huimaidan_withdraw_rate'] !== '0.00') {
    throw new RuntimeException('未传惠买单配置时应默认垫资池模式和0费率');
}

try {
    $method->invoke($repository, ['huimaidan_settlement_mode' => 3]);
    throw new RuntimeException('非法合作模式应报错');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '惠买单合作模式有误') {
        throw $e;
    }
}

try {
    $method->invoke($repository, ['huimaidan_settlement_mode' => 2, 'huimaidan_withdraw_rate' => '101']);
    throw new RuntimeException('提现费率超过100应报错');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '惠买单提现手续费率必须在0到100之间') {
        throw $e;
    }
}

echo "MerchantSettlementModeTest passed\n";
