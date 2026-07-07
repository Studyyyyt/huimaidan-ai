<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\AiRepository;

$reflection = new ReflectionClass(AiRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$isNonDiningQuestion = $reflection->getMethod('isNonDiningQuestion');
$isNonDiningQuestion->setAccessible(true);
$replyText = $reflection->getMethod('nonDiningReplyText');
$replyText->setAccessible(true);

$autoMealOnlyIntent = [
    'category' => [],
    'scene' => [],
    'taste' => [],
    'facility' => [],
    'feature' => [],
    'promotion' => [],
    'meal' => ['lunch'],
    'price' => '',
    'distance' => '',
    'action' => '',
    'meal_is_default' => true,
];

if (!$isNonDiningQuestion->invoke($repository, '你是谁', $autoMealOnlyIntent)) {
    throw new RuntimeException('AI 应识别“你是谁”为非餐饮问题，不能强行推荐商户');
}

if (!$isNonDiningQuestion->invoke($repository, '你叫什么名字', $autoMealOnlyIntent)) {
    throw new RuntimeException('AI 应识别“你叫什么名字”为身份问题，不能强行推荐商户');
}

if (!$isNonDiningQuestion->invoke($repository, '今天天气怎么样', $autoMealOnlyIntent)) {
    throw new RuntimeException('AI 应识别天气类问题为非餐饮问题');
}

if (!$isNonDiningQuestion->invoke($repository, '今天有nab', $autoMealOnlyIntent)) {
    throw new RuntimeException('AI 应把未知无关问题拦截为非餐饮问题，不能强行推荐商户');
}

if ($isNonDiningQuestion->invoke($repository, '附近有什么优惠推荐', $autoMealOnlyIntent)) {
    throw new RuntimeException('AI 不应把附近优惠推荐误判为非餐饮问题');
}

if ($isNonDiningQuestion->invoke($repository, '随便推荐一下附近优惠', $autoMealOnlyIntent)) {
    throw new RuntimeException('AI 不应把模糊但明确的推荐需求误判为非餐饮问题');
}

$foodIntent = $autoMealOnlyIntent;
$foodIntent['taste'] = ['辣'];
$foodIntent['price'] = '30-60';
if ($isNonDiningQuestion->invoke($repository, '想吃辣但不贵', $foodIntent)) {
    throw new RuntimeException('AI 不应把明确吃饭需求误判为非餐饮问题');
}

if (strpos($replyText->invoke($repository, '你叫什么名字'), 'AI 小惠') === false) {
    throw new RuntimeException('AI 身份问题应直接介绍自己，不应使用商户推荐兜底文案');
}

if ($replyText->invoke($repository) !== '我主要帮你找附近优惠商家。你可以告诉我想吃什么、预算、人数、距离要求，我来帮你推荐。') {
    throw new RuntimeException('非餐饮引导文案不符合产品要求');
}

echo "AiNonDiningReplyContractTest passed\n";
