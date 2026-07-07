<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\PoolRepository;
use app\common\repositories\huimaidan\PoolRulePolicy;
use think\exception\ValidateException;

$policy = new PoolRulePolicy();

try {
    $policy->poolId(0);
    throw new RuntimeException('未绑定垫资池的优惠规则应失败');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '惠买单优惠规则必须绑定垫资池') {
        throw $e;
    }
}

$rules = $policy->usableRules([
    ['rule_id' => 1, 'pool_id' => 0, 'pool' => null],
    ['rule_id' => 2, 'pool_id' => 2, 'pool' => ['status' => PoolRepository::STATUS_DISABLED]],
    ['rule_id' => 3, 'pool_id' => 3, 'pool' => ['status' => PoolRepository::STATUS_ENABLED]],
]);
if (array_column($rules, 'rule_id') !== [3]) {
    throw new RuntimeException('仅应保留绑定已启用垫资池的优惠规则');
}

echo "PoolRulePolicyTest passed\n";
