<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\MerchantPresenter;

function assertMerchantSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, 'Actual:   ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$presenter = new MerchantPresenter();

$merchant = $presenter->present([
    'mer_id' => 7,
    'mer_name' => '测试门店',
    'mer_avatar' => '/avatar.png',
    'mer_address' => '测试路1号',
    'service_phone' => '15780282354',
    'mer_phone' => '13800000000',
    'category_id' => 2,
    'category_name' => '餐饮',
    'city_id' => 11,
    'city_name' => '测试市',
    'status' => 1,
    'mer_state' => 1,
    'long' => '120.000000',
    'lat' => '30.000000',
    'product_score' => '5.0',
    'service_score' => '4.8',
    'postage_score' => '4.6',
    'sales' => 50,
    'create_time' => strtotime('2024-06-11 10:00:00'),
    'branch_name' => '摩尔城店',
    'configured_sales' => 350000,
    'per_capita' => '88.00',
    'business_hours' => '[{"day":"周一至周日","time":"10:00-22:00"}]',
    'facilities' => '{"has_large_table":1,"has_baby_chair":1,"can_phone_reserve":1,"is_non_smoking":0}',
    'promo_image' => '/promo.png',
    'slogan' => '到店买单更划算',
], [
    'rule_type' => 1,
    'title' => '平台6折',
    'platform_discount' => '0.60',
], null);

assertMerchantSame(false, array_key_exists('distance', $merchant), '缺少定位时不得伪造距离');
assertMerchantSame(true, $merchant['has_discount'], '存在规则时应明确标记有优惠');
assertMerchantSame('6折', $merchant['discount_label'], '折扣规则应生成业务化标签');
assertMerchantSame('营业中', $merchant['business_status_text'], '营业商户应返回营业中文案');
assertMerchantSame('15780282354', $merchant['phone'], '公开联系电话应使用 service_phone');
assertMerchantSame(false, array_key_exists('mer_phone', $merchant), '展示摘要不得返回后台商户手机号');
assertMerchantSame(4.8, $merchant['rating'], '评分应使用三项评分均值');
assertMerchantSame(['product_score' => 5.0, 'service_score' => 4.8, 'postage_score' => 4.6], $merchant['rating_detail'], '评分明细应返回真实评分');
assertMerchantSame(50, $merchant['real_sales'], '真实销量应保留 CRMEB 原始销量');
assertMerchantSame(350000, $merchant['configured_sales'], '配置销量应单独返回便于核对');
assertMerchantSame(350050, $merchant['sales'], '展示销量应为配置销量加真实销量');
assertMerchantSame('半年售35万+', $merchant['sales_text'], '销量文案应按半年售展示');
assertMerchantSame('摩尔城店', $merchant['store'], 'store 应返回分店名');
assertMerchantSame('摩尔城店', $merchant['store_branch_name'], 'store_branch_name 应返回分店名');
assertMerchantSame(88, $merchant['price_per_person'], '人均消费应返回数值');
assertMerchantSame('人均 ¥88', $merchant['price_per_person_text'], '人均消费应返回展示文案');
assertMerchantSame([['day' => '周一至周日', 'time' => '10:00-22:00']], $merchant['business_hours'], '营业时间应返回结构化数组');
assertMerchantSame([
    'has_large_table' => true,
    'has_baby_chair' => true,
    'has_private_room' => false,
    'can_phone_reserve' => true,
    'is_non_smoking' => false,
], $merchant['facilities'], '设施配置应返回布尔值');
assertMerchantSame(['大桌', '宝宝椅', '电话预订'], $merchant['facility_tags'], '设施标签应只展示已开启项');
assertMerchantSame('/promo.png', $merchant['promo_image'], '促销图应返回扩展表配置');
assertMerchantSame('到店买单更划算', $merchant['slogan'], '商户标语应返回扩展表配置');
assertMerchantSame(2, $merchant['settled_years'], '收录年限应按商户 create_time 计算');
assertMerchantSame('收录2年', $merchant['settled_years_text'], '收录年限文案应按商户 create_time 生成');

$noDiscount = $presenter->present([
    'mer_id' => 8,
    'mer_name' => '无优惠门店',
    'status' => 1,
    'mer_state' => 1,
], null, null);

assertMerchantSame(false, $noDiscount['has_discount'], '无有效规则时应明确标记无优惠');
assertMerchantSame(null, $noDiscount['discount_label'], '无有效规则时不得伪造优惠标签');

$withDistance = $presenter->present([
    'mer_id' => 9,
    'mer_name' => '附近门店',
    'status' => 1,
    'mer_state' => 1,
], null, '1.23');

assertMerchantSame('1.23km', $withDistance['distance'], '传入真实距离时应格式化展示值');
assertMerchantSame('1.23', $withDistance['distance_km'], '传入真实距离时应保留排序数值');

// service_phone 为空时应 fallback 到 mer_phone
$fallbackMerchant = $presenter->present([
    'mer_id' => 10,
    'mer_name' => '无客服电话门店',
    'status' => 1,
    'mer_state' => 1,
    'service_phone' => '',
    'mer_phone' => '19999999999',
], null, null);

assertMerchantSame('19999999999', $fallbackMerchant['phone'], 'service_phone 为空时应回退到 mer_phone');

// service_phone 和 mer_phone 都为空时
$emptyPhoneMerchant = $presenter->present([
    'mer_id' => 11,
    'mer_name' => '无电话门店',
    'status' => 1,
    'mer_state' => 1,
    'service_phone' => '',
    'mer_phone' => '',
], null, null);

assertMerchantSame('', $emptyPhoneMerchant['phone'], '两个电话字段都为空时应返回空字符串');

echo "MerchantPresenterTest passed\n";
