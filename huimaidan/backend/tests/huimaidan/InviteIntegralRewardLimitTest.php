<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\user\UserRepository;

$reflection = new ReflectionClass(UserRepository::class);
if (!$reflection->hasMethod('canGiveSpreadIntegralReward')) {
    throw new RuntimeException('UserRepository 必须提供邀请积分奖励上限判断方法');
}

$method = $reflection->getMethod('canGiveSpreadIntegralReward');
$method->setAccessible(true);
$repository = $reflection->newInstanceWithoutConstructor();

$baseConfig = [
    'integral_status' => 1,
    'integral_user_give' => 5,
    'integral_user_give_limit' => 1,
];

$cases = [
    [$baseConfig, true, 0, true, '未达到上限时应发放邀请积分'],
    [$baseConfig, true, 1, false, '达到邀请奖励上限后不应再发放积分'],
    [array_merge($baseConfig, ['integral_user_give_limit' => 0]), true, 99, true, '邀请奖励上限为 0 时应不限制'],
    [$baseConfig, false, 0, false, '非新用户绑定不应发放邀请积分'],
    [array_merge($baseConfig, ['integral_status' => 0]), true, 0, false, '积分关闭时不应发放邀请积分'],
    [array_merge($baseConfig, ['integral_user_give' => 0]), true, 0, false, '邀请积分配置为 0 时不应发放邀请积分'],
];

foreach ($cases as [$config, $isNewUser, $rewardCount, $expected, $message]) {
    $actual = $method->invoke($repository, $config, $isNewUser, $rewardCount);
    if ($actual !== $expected) {
        throw new RuntimeException($message . ': ' . var_export($actual, true));
    }
}

echo "InviteIntegralRewardLimitTest passed\n";
