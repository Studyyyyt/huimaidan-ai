<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\AiBannerConfigRepository;
use app\common\repositories\huimaidan\AiRecommendRepository;
use app\common\repositories\huimaidan\AiTagRepository;

$bannerReflection = new ReflectionClass(AiBannerConfigRepository::class);
$banner = $bannerReflection->newInstanceWithoutConstructor();
$currentMealType = $bannerReflection->getMethod('currentMealType');
if ($currentMealType->invoke($banner, 8) !== 'breakfast'
    || $currentMealType->invoke($banner, 12) !== 'lunch'
    || $currentMealType->invoke($banner, 19) !== 'dinner'
    || $currentMealType->invoke($banner, 23) !== 'late_night') {
    throw new RuntimeException('餐段判断不符合预期');
}

$tagReflection = new ReflectionClass(AiTagRepository::class);
$tagRepository = $tagReflection->newInstanceWithoutConstructor();
$defaultKeywordMap = $tagReflection->getMethod('defaultKeywordMap');
$defaultKeywordMap->setAccessible(true);
$map = $defaultKeywordMap->invoke($tagRepository);
if ($map['便宜']['type'] !== 'price' || $map['火锅']['value'] !== '火锅' || $map['包间']['type'] !== 'facility') {
    throw new RuntimeException('默认 AI 关键词兜底词表不完整');
}

$recommendReflection = new ReflectionClass(AiRecommendRepository::class);
$recommend = $recommendReflection->newInstanceWithoutConstructor();
$priceMatches = $recommendReflection->getMethod('priceMatches');
$priceMatches->setAccessible(true);
if (!$priceMatches->invoke($recommend, 55, '30-60') || $priceMatches->invoke($recommend, 90, '30-60')) {
    throw new RuntimeException('AI 推荐价格区间匹配异常');
}
if (!$priceMatches->invoke($recommend, 90, '0-100') || $priceMatches->invoke($recommend, 120, '0-100')) {
    throw new RuntimeException('AI 推荐必须兼容 LLM 返回的宽价格区间');
}

$priceTagValues = $recommendReflection->getMethod('priceTagValues');
$priceTagValues->setAccessible(true);
$expanded = $priceTagValues->invoke($recommend, '0-100');
if ($expanded !== ['0-30', '30-60', '60-100']) {
    throw new RuntimeException('AI 推荐宽价格区间必须展开为可查询的价格标签');
}

$tagScore = $recommendReflection->getMethod('tagScore');
$tagScore->setAccessible(true);
$spicyBudgetIntent = [
    'taste' => ['辣'],
    'price' => '30-60',
    'meal' => ['breakfast'],
    'meal_is_default' => true,
];
$spicyBudgetScore = $tagScore->invoke($recommend, [
    ['tag_type' => 'taste', 'tag_value' => '辣', 'tag_weight' => 85],
    ['tag_type' => 'price', 'tag_value' => '30-60', 'tag_weight' => 85],
], $spicyBudgetIntent);
$breakfastOnlyScore = $tagScore->invoke($recommend, [
    ['tag_type' => 'meal', 'tag_value' => 'breakfast', 'tag_weight' => 90],
    ['tag_type' => 'price', 'tag_value' => '0-30', 'tag_weight' => 90],
], $spicyBudgetIntent);
if ($spicyBudgetScore <= $breakfastOnlyScore) {
    throw new RuntimeException('AI 推荐不得让默认餐段压过用户明确的口味和预算需求');
}

$isOpenNow = $recommendReflection->getMethod('isOpenNow');
$isOpenNow->setAccessible(true);
if (!$isOpenNow->invoke($recommend, ['status' => 1, 'mer_state' => 1, 'business_hours' => [['time' => '10:00-22:00']]], 12 * 60)) {
    throw new RuntimeException('AI 推荐应保留当前营业商户');
}
if ($isOpenNow->invoke($recommend, ['status' => 1, 'mer_state' => 1, 'business_hours' => [['time' => '10:00-22:00']]], 23 * 60)) {
    throw new RuntimeException('AI 推荐应过滤当前未营业商户');
}
if (!$isOpenNow->invoke($recommend, ['status' => 1, 'mer_state' => 1, 'business_hours' => [['time' => '22:00-02:00']]], 1 * 60)) {
    throw new RuntimeException('AI 推荐应支持跨午夜营业时间');
}
if (!$isOpenNow->invoke($recommend, ['status' => 1, 'mer_state' => 1, 'business_hours' => []], 23 * 60)) {
    throw new RuntimeException('未配置营业时间的商户不应被 AI 误过滤');
}
if (!$isOpenNow->invoke($recommend, ['status' => 1, 'mer_state' => 1, 'business_hours' => [['time' => '全天营业']]], 3 * 60)) {
    throw new RuntimeException('AI 推荐应支持全天营业时间');
}
if ($isOpenNow->invoke($recommend, ['status' => 0, 'mer_state' => 1, 'business_hours' => []], 12 * 60)) {
    throw new RuntimeException('停用商户不得进入 AI 推荐');
}

echo "AiRecommendationContractTest passed\n";
