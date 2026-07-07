<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\wechat\RoutinePhoneBindingPolicy;

function assertRoutinePhoneBindingSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, 'Actual:   ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$policy = new RoutinePhoneBindingPolicy();

assertRoutinePhoneBindingSame(true, $policy->requiresBinding(null), '小程序用户不存在时必须进入手机号绑定中间态');
assertRoutinePhoneBindingSame(true, $policy->requiresBinding(['phone' => '']), '小程序用户未绑定手机号时必须进入手机号绑定中间态');
assertRoutinePhoneBindingSame(false, $policy->requiresBinding(['phone' => '13800000000']), '已绑定手机号的小程序用户可直接登录');

assertRoutinePhoneBindingSame([
    'bindPhone' => true,
    'key' => 'Uabc123',
    'wechat_phone_switch' => '1',
], $policy->loginTypePayload(null, 'Uabc123'), '未绑定手机号时预检必须返回快速验证组件绑定态');

assertRoutinePhoneBindingSame([
    'bindPhone' => false,
    'key' => '',
    'wechat_phone_switch' => '1',
], $policy->loginTypePayload(['phone' => '13800000000'], 'Uabc123'), '已绑定手机号时预检不得返回无效绑定 key');

echo "RoutinePhoneBindingPolicyTest passed\n";
