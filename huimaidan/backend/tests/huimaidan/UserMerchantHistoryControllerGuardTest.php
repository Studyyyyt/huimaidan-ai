<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$root = dirname(__DIR__, 2);
$controller = file_get_contents($root . '/app/controller/api/huimaidan/User.php');

foreach (['merchantHistory', 'deleteMerchantHistory', 'deleteMerchantHistoryBatch'] as $method) {
    if (strpos($controller, 'function ' . $method . '(') === false) {
        throw new RuntimeException('用户中心控制器缺少方法: ' . $method);
    }
}

$route = file_get_contents($root . '/route/api.php');
foreach ([
    "user/merchant_history",
    "user/merchant_history/delete/:id",
    "user/merchant_history/batch_delete",
] as $needle) {
    if (strpos($route, $needle) === false) {
        throw new RuntimeException('路由缺少店铺浏览历史接口: ' . $needle);
    }
}

echo "UserMerchantHistoryControllerGuardTest passed\n";
