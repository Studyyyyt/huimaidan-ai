<?php

// 对比测试：相同 query 在是否包含折扣词时的排序差异

require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use think\facade\Cache;
use think\App;

$app = new App();
$app->initialize();

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

$baseUrl = 'http://127.0.0.1:8324';
$baseParams = [
    'latitude' => 40.800861,
    'longitude' => 111.690894,
    'city_name' => '呼和浩特',
];

function callChat(string $message, array $baseParams, string $token, string $baseUrl, int $uid): array {
    Cache::delete('ai:rate:' . $uid . ':' . date('Ymd'));
    $ch = curl_init($baseUrl . '/api/huimaidan/ai/chat');
    $data = array_merge($baseParams, ['message' => $message]);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Token: ' . $token,
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $body = json_decode($response, true);
    return [
        'http_code' => $httpCode,
        'intent_tags' => $body['data']['content']['intent_tags'] ?? [],
        'merchants' => $body['data']['content']['merchants'] ?? [],
        'text' => $body['data']['content']['text'] ?? '',
        'raw' => $response,
    ];
}

function extractScores(array $merchants): array {
    $result = [];
    foreach ($merchants as $m) {
        $result[] = [
            'name' => $m['mer_name'] ?? '',
            'distance' => $m['distance'] ?? '',
            'discount' => $m['discount_label'] ?? '',
            'score' => $m['score'] ?? 0,
            'reason' => $m['recommend_reason'] ?? '',
        ];
    }
    return $result;
}

// 注意：每次请求后都清掉 session，确保两次请求独立
$queries = [
    '我要吃火锅',
    '我要吃8折的火锅',
    '我要吃7.5折的火锅',
    '我要吃距离最远的火锅',
];

$results = [];
foreach ($queries as $query) {
    $result = callChat($query, $baseParams, $token, $baseUrl, $uid);
    $results[$query] = $result;
    echo "\n=== {$query} ===\n";
    echo "HTTP: {$result['http_code']}\n";
    echo "意图标签: " . json_encode($result['intent_tags'], JSON_UNESCAPED_UNICODE) . "\n";
    echo "AI回复: {$result['text']}\n";
    echo "推荐商户（按分数排序）:\n";
    foreach (extractScores($result['merchants']) as $idx => $m) {
        echo sprintf("  %d. %s | %s | %s | score=%.4f | %s\n", $idx + 1, $m['name'], $m['distance'], $m['discount'], $m['score'], $m['reason']);
    }
}

// 简单对比
$baseList = array_column(extractScores($results['我要吃火锅']['merchants']), 'name');
$discountList = array_column(extractScores($results['我要吃8折的火锅']['merchants']), 'name');
$farList = array_column(extractScores($results['我要吃距离最远的火锅']['merchants']), 'name');
echo "\n=== 对比 ===\n";
echo "普通火锅排序: " . implode(' > ', $baseList) . "\n";
echo "8折火锅排序: " . implode(' > ', $discountList) . "\n";
echo "最远火锅排序: " . implode(' > ', $farList) . "\n";
if ($baseList === $discountList) {
    echo "结论：8折排序未变化\n";
} else {
    echo "结论：8折排序已变化\n";
}
if ($baseList === $farList) {
    echo "结论：最远排序未变化\n";
} else {
    echo "结论：最远排序已变化\n";
}

echo "\n测试完成\n";
