<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\WithdrawRepository;

$reflection = new ReflectionClass(WithdrawRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$feeMethod = $reflection->getMethod('feeAmounts');
$feeMethod->setAccessible(true);
$fee = $feeMethod->invoke($repository, '1000.00', '2.50');
if ($fee !== ['fee_amount' => '25.00', 'real_transfer_amount' => '975.00']) {
    throw new RuntimeException('惠买单提现手续费和实际打款金额计算错误');
}

$fee = $feeMethod->invoke($repository, '500.01', '0');
if ($fee !== ['fee_amount' => '0.00', 'real_transfer_amount' => '500.01']) {
    throw new RuntimeException('0费率时不应扣手续费');
}

$unfinished = $reflection->getMethod('unfinishedStatusWhere');
$unfinished->setAccessible(true);
$where = $unfinished->invoke($repository, 88);
if ($where['business_type'] !== WithdrawRepository::BUSINESS_TYPE || $where['mer_id'] !== 88 || $where['is_del'] !== 0) {
    throw new RuntimeException('未完结申请查询必须限定惠买单业务和当前商户');
}
if ($where['unfinished_status'] !== [[0, 0], [1, 0]]) {
    throw new RuntimeException('未完结申请仅包含待审核和审核通过待打款');
}

$payload = $reflection->getMethod('currentPayload');
$payload->setAccessible(true);
if ($payload->invoke($repository, null) !== ['current' => null]) {
    throw new RuntimeException('无当前提现申请时必须返回可被success响应封装处理的数组');
}

echo "WithdrawCalculatorTest passed\n";
