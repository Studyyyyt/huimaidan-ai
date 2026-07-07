<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\OrderRepository;

$reflection = new ReflectionClass(OrderRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$method = $reflection->getMethod('paySuccessRewardPayloads');
$method->setAccessible(true);

$payloads = $method->invoke($repository, 1001, 2002, '88.50', 3003);

if ($payloads['member_value'] !== [
    'uid' => 1001,
    'type' => 'member_pay_num',
    'link_id' => 3003,
    'money' => '88.50',
]) {
    throw new RuntimeException('惠买单支付成功应增加用户会员成长值');
}

$expectedJobs = [
    ['uid' => 2002, 'type' => 'spread_pay_num', 'inc' => 1],
    ['uid' => 2002, 'type' => 'spread_money', 'inc' => '88.50'],
    ['uid' => 1001, 'type' => 'pay_money', 'inc' => '88.50'],
    ['uid' => 1001, 'type' => 'pay_num', 'inc' => 1],
];
if ($payloads['jobs'] !== $expectedJobs) {
    throw new RuntimeException('惠买单支付成功会员/VIP统计队列任务不正确');
}

$payloads = $method->invoke($repository, 1001, 0, '88.50', 3003);
if ($payloads['jobs'] !== [
    ['uid' => 1001, 'type' => 'pay_money', 'inc' => '88.50'],
    ['uid' => 1001, 'type' => 'pay_num', 'inc' => 1],
]) {
    throw new RuntimeException('无推荐人时不应生成推荐人统计任务');
}

echo "OrderRepositoryRewardPayloadTest passed\n";
