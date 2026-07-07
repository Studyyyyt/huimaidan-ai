<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\AiNluRepository;

$reflection = new ReflectionClass(AiNluRepository::class);
$nlu = $reflection->newInstanceWithoutConstructor();

$tagRepository = new class {
    public function keywordMap(): array
    {
        return [
            '火锅' => ['type' => 'category', 'value' => '火锅'],
            '川菜' => ['type' => 'category', 'value' => '川菜'],
            '辣' => ['type' => 'taste', 'value' => '辣'],
            '包间' => ['type' => 'facility', 'value' => '包间'],
            '聚餐' => ['type' => 'scene', 'value' => '聚餐'],
            '便宜' => ['type' => 'price', 'value' => '30-60'],
        ];
    }
};

$property = $reflection->getProperty('tagRepository');
$property->setAccessible(true);
$property->setValue($nlu, $tagRepository);

$ruleParse = $reflection->getMethod('ruleParse');
$ruleParse->setAccessible(true);

$firstIntent = $ruleParse->invoke($nlu, '想吃辣但不贵的火锅', []);
if (!in_array('火锅', $firstIntent['category'], true) || !in_array('辣', $firstIntent['taste'], true) || $firstIntent['price'] !== '30-60') {
    throw new RuntimeException('AI NLU 规则解析必须识别模糊意图：品类、口味和低预算');
}
if (empty($firstIntent['meal']) || $firstIntent['price_range'] !== $firstIntent['price'] || $firstIntent['time'] !== $firstIntent['meal']) {
    throw new RuntimeException('AI NLU 必须补齐餐段，并兼容 price_range/time 字段');
}

$history = [
    [
        'role' => 'user',
        'text' => '想吃火锅',
        'intent_tags' => [
            'category' => ['火锅'],
            'taste' => ['辣'],
            'price' => '60-100',
        ],
    ],
    [
        'role' => 'ai',
        'text' => '推荐这些店',
        'mer_ids' => [1001, 1002, 1003],
    ],
];

$cheaperIntent = $ruleParse->invoke($nlu, '便宜点', $history);
if (($cheaperIntent['action'] ?? '') !== 'cheaper' || $cheaperIntent['price'] !== '30-60') {
    throw new RuntimeException('AI NLU 多轮“便宜点”必须下调上一轮预算');
}
if ($cheaperIntent['category'] !== ['火锅'] || $cheaperIntent['taste'] !== ['辣']) {
    throw new RuntimeException('AI NLU 多轮“便宜点”必须继承上一轮品类和口味');
}

$replaceIntent = $ruleParse->invoke($nlu, '换一家', $history);
if (($replaceIntent['action'] ?? '') !== 'replace' || $replaceIntent['exclude_mer_ids'] !== [1001, 1002, 1003]) {
    throw new RuntimeException('AI NLU 多轮“换一家”必须排除上一轮推荐商户');
}

$partyIntent = $ruleParse->invoke($nlu, '附近有包间适合聚餐吗', []);
if (($partyIntent['distance'] ?? '') !== 'near' || $partyIntent['facility'] !== ['包间'] || $partyIntent['scene'] !== ['聚餐']) {
    throw new RuntimeException('AI NLU 必须识别附近、包间、聚餐场景');
}

$normalizeIntent = $reflection->getMethod('normalizeIntent');
$normalizeIntent->setAccessible(true);
$normalized = $normalizeIntent->invoke($nlu, [
    'category' => '火锅',
    'text' => 'LLM原始回复',
    'merchants' => [['mer_id' => 1]],
    'intent_tags' => ['dirty' => true],
    'price_range' => '0-100',
    'time' => ['dinner'],
]);
foreach (['text', 'merchants', 'intent_tags'] as $dirtyKey) {
    if (array_key_exists($dirtyKey, $normalized)) {
        throw new RuntimeException('AI NLU 结构化意图不得混入原始回复字段：' . $dirtyKey);
    }
}
if ($normalized['category'] !== ['火锅'] || $normalized['price'] !== '0-100' || $normalized['meal'] !== ['dinner']) {
    throw new RuntimeException('AI NLU 清理未知字段时必须保留有效意图');
}

echo "AiNluContractTest passed\n";
