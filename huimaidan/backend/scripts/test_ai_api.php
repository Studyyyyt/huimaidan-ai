<?php

// AI 推荐接口端到端测试脚本
// 生成有效用户 token 并调用 banner/chat 接口，验证响应时间和文案质量

require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use think\facade\Cache;
use think\App;

$app = new App();
$app->initialize();

// 查找一个有效用户
$user = app()->make(\app\common\repositories\user\UserRepository::class)->get(1);
if (!$user || !$user['status'] || $user['cancel_time']) {
    $user = \think\facade\Db::name('user')->where('status', 1)->where('cancel_time', 0)->order('uid', 'asc')->find();
}
if (!$user) {
    throw new RuntimeException('未找到有效用户');
}
$uid = (int)$user['uid'];
echo "使用测试用户 uid={$uid}\n";

$appKey = \think\facade\Config::get('app.app_key', 'default');
$time = time();
$payload = [
    'iss' => 'localhost',
    'aud' => 'localhost',
    'iat' => $time,
    'nbf' => $time,
    'exp' => $time + 3600,
    'jti' => [$uid, 'user'],
];
$token = JWT::encode($payload, $appKey);
Cache::set('user_' . $token, 1, 3600);

// 清理当日速率限制，确保测试不受历史调用影响
Cache::delete('ai:rate:' . $uid . ':' . date('Ymd'));

$baseUrl = 'http://127.0.0.1:8324';
$queries = [
    ['message' => '想吃火锅', 'latitude' => 40.800861, 'longitude' => 111.690894, 'city_name' => '呼和浩特'],
    ['message' => '适合朋友聚餐有包间的地方', 'latitude' => 40.800861, 'longitude' => 111.690894, 'city_name' => '呼和浩特'],
    ['message' => '性价比高一点的火锅', 'latitude' => 40.800861, 'longitude' => 111.690894, 'city_name' => '呼和浩特'],
];

function callApi(string $url, array $data, string $token, string $method = 'POST'): array {
    $method = strtoupper($method);
    if ($method === 'GET' && $data) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
    }
    $ch = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Token: ' . $token,
        ],
    ];
    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    curl_setopt_array($ch, $options);
    $start = microtime(true);
    $response = curl_exec($ch);
    $elapsed = microtime(true) - $start;
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return [
        'http_code' => $httpCode,
        'elapsed_ms' => round($elapsed * 1000, 2),
        'error' => $error,
        'body' => $response,
    ];
}

function assertUserFacingTextClean(string $text, string $label): void
{
    if ($text === '') {
        return;
    }
    $blocked = [
        'rule_score', 'score_factors', 'JSON', '候选商户', '排序依据', '排序核心',
        '意图标签', '规则解析', '规则排序', 'LLM', '模型分析', '内部', 'rank',
        '用户明确', '核心诉求', '核心需求', '三家中', '商户均', '标注', '字段',
        '后端召回', '召回得分',
        'facility', 'facilities', 'category', 'scene', 'backend', 'score',
        'has_private_room', 'has_parking', 'has_baby_chair', 'has_large_table', 'is_non_smoking',
        'business_hours', 'meal_is_default', 'late_night', 'breakfast', 'brunch', 'lunch',
        'dinner', 'tea',
        '标签权重', '完全匹配', '显著提升', '权重', '匹配度', '未明示',
        '适配性', '高度契合', '高度匹配', '标签明确', '候选中',
    ];
    foreach ($blocked as $word) {
        if (mb_stripos($text, $word) !== false) {
            echo "文案检查失败：{$label} 包含内部分析词 {$word}\n";
            exit(1);
        }
    }
    echo "文案检查通过：{$label}\n";
}

echo "\n=== Banner 接口测试 ===\n";
$bannerResult = callApi($baseUrl . '/api/huimaidan/ai/banner', [
    'latitude' => 40.800861,
    'longitude' => 111.690894,
    'city_name' => '呼和浩特',
], $token, 'GET');
echo "HTTP {$bannerResult['http_code']}，耗时 {$bannerResult['elapsed_ms']}ms\n";
if ($bannerResult['error']) {
    echo "CURL 错误: {$bannerResult['error']}\n";
}
$bannerBody = json_decode($bannerResult['body'], true);
echo json_encode($bannerBody, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

echo "\n=== Chat 接口测试 ===\n";
foreach ($queries as $idx => $query) {
    echo "\n--- 用例 " . ($idx + 1) . ": {$query['message']} ---\n";
    $result = callApi($baseUrl . '/api/huimaidan/ai/chat', $query, $token);
    echo "HTTP {$result['http_code']}，耗时 {$result['elapsed_ms']}ms\n";
    if ($result['error']) {
        echo "CURL 错误: {$result['error']}\n";
    }
    $body = json_decode($result['body'], true);
    if (!empty($body['data']['content']['text'])) {
        echo "AI回复: " . $body['data']['content']['text'] . "\n";
        assertUserFacingTextClean((string)$body['data']['content']['text'], 'AI回复');
    }
    if (!empty($body['data']['content']['intent_tags'])) {
        echo "意图标签: " . json_encode($body['data']['content']['intent_tags'], JSON_UNESCAPED_UNICODE) . "\n";
    }
    if (!empty($body['data']['content']['merchants'])) {
        echo "推荐商户:\n";
        foreach ($body['data']['content']['merchants'] as $merchant) {
            echo "  - {$merchant['mer_name']} ({$merchant['distance']})\n";
            assertUserFacingTextClean((string)($merchant['recommend_reason'] ?? ''), '商户推荐理由');
        }
    }
    if (!empty($body['data']['degraded'])) {
        echo "降级模式: true\n";
    }
    if (!empty($body['data']['error_message'])) {
        echo "错误信息: {$body['data']['error_message']}\n";
    }
    if (empty($body['data']['content']['text']) && empty($body['data']['content']['merchants'])) {
        echo "原始响应:\n" . $result['body'] . "\n";
    }
}

echo "\n测试完成\n";
