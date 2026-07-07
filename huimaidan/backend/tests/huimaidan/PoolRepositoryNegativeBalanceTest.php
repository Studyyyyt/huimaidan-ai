<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\PoolRepository;
use think\exception\ValidateException;

$reflection = new ReflectionClass(PoolRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$repository->setDao(new class {
    public function get($poolId)
    {
        return (object)[
            'pool_id' => $poolId,
            'mer_id' => 9,
            'status' => PoolRepository::STATUS_ENABLED,
            'balance' => '-10.00',
        ];
    }
});

$pool = $repository->ensureUsable(1, '88.00', 9);
if ((int)$pool->pool_id !== 1) {
    throw new RuntimeException('负余额垫资池仍应允许参与惠买单结账');
}

$deductBalance = $reflection->getMethod('deductBalance');
$deductBalance->setAccessible(true);

try {
    $deductBalance->invoke($repository, '10.00', '30.00', false);
    throw new RuntimeException('未开启透支时，扣减超过余额应失败');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '垫资池余额不足') {
        throw $e;
    }
}

$after = $deductBalance->invoke($repository, '10.00', '30.00', true);
if ($after !== '-20.00') {
    throw new RuntimeException('开启透支后，订单扣减应允许垫资池变成负数');
}

echo "PoolRepositoryNegativeBalanceTest passed\n";
