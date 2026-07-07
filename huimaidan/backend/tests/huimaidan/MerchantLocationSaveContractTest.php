<?php

$root = dirname(__DIR__, 2);

$controller = file_get_contents($root . '/app/controller/admin/system/merchant/Merchant.php');
$validate = file_get_contents($root . '/app/validate/admin/MerchantValidate.php');
$repository = file_get_contents($root . '/app/common/repositories/system/merchant/MerchantRepository.php');
$presenter = file_get_contents($root . '/app/common/repositories/huimaidan/MerchantPresenter.php');

if (!preg_match("/request->params\\(\\[.*'long'.*'lat'.*\\]\\)/s", $controller)) {
    fwrite(STDERR, "平台店铺新增/编辑保存白名单必须接收 long、lat\n");
    exit(1);
}

foreach (['long|经度', 'lat|纬度'] as $field) {
    if (strpos($validate, $field) === false) {
        fwrite(STDERR, "MerchantValidate 必须校验 {$field}\n");
        exit(1);
    }
}

foreach (["input('long'", "input('lat'"] as $field) {
    if (strpos($repository, $field) === false) {
        fwrite(STDERR, "后端动态表单必须包含 {$field}\n");
        exit(1);
    }
}

foreach (["'longitude' => (string)(\$merchant['long'] ?? '')", "'latitude' => (string)(\$merchant['lat'] ?? '')"] as $snippet) {
    if (strpos($presenter, $snippet) === false) {
        fwrite(STDERR, "惠买单展示数据必须返回经纬度字段\n");
        exit(1);
    }
}

echo "MerchantLocationSaveContractTest passed\n";
