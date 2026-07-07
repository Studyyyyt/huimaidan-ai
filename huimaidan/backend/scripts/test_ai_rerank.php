<?php

// AI 动态排序端到端测试脚本
// 覆盖开发计划要求的动态排序用例，并校验推荐日志中的候选池变化

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

$baseUrl = getenv('HUIMD_AI_TEST_URL') ?: 'http://127.0.0.1:8324';
$location = [
    'latitude' => 40.800861,
    'longitude' => 111.690894,
    'city_name' => '呼和浩特',
];

$failures = 0;
$passes = 0;

function callApi(string $url, array $data, string $token, string $method = 'POST'): array
{
    $method = strtoupper($method);
    if ($method === 'GET' && $data) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
    }
    $ch = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
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

function ok(string $message): void
{
    global $passes;
    $passes++;
    echo "  ✓ {$message}\n";
}

function fail(string $message): void
{
    global $failures;
    $failures++;
    echo "  ✗ {$message}\n";
}

function discountRate(array $merchant): ?float
{
    if (isset($merchant['member_discount']) && is_numeric($merchant['member_discount']) && $merchant['member_discount'] > 0 && $merchant['member_discount'] <= 1) {
        return round((float)$merchant['member_discount'] * 10, 2);
    }
    $label = (string)($merchant['discount_label'] ?? '');
    if (preg_match('/(\d+(?:\.\d+)?)\s*折/u', $label, $match)) {
        return (float)$match[1];
    }
    return null;
}

function fetchLog(int $logId): ?array
{
    if ($logId <= 0) {
        return null;
    }
    $row = \app\common\model\huimaidan\AiRecommendLog::getDB()->where('log_id', $logId)->find();
    return $row ? $row->toArray() : null;
}

function assertLogCandidates(?array $log, array $responseMerchants): void
{
    if (!$log) {
        fail('未找到推荐日志记录');
        return;
    }
    $before = json_decode((string)($log['candidate_mer_ids_before'] ?? '[]'), true) ?: [];
    $after = json_decode((string)($log['candidate_mer_ids_after'] ?? '[]'), true) ?: [];
    $result = json_decode((string)($log['result_mer_ids'] ?? '[]'), true) ?: [];

    if (count($before) > 0) {
        ok('日志已记录排序前候选池：' . count($before) . ' 家');
    } else {
        fail('排序前候选池为空');
    }

    if (!$after) {
        ok('LLM 动态排序未生效或发生降级，未记录排序后候选池');
    } else {
        $afterInBefore = count(array_diff($after, $before)) === 0;
        if ($afterInBefore) {
            ok('排序后候选池是排序前候选池的子集');
        } else {
            fail('排序后候选池包含排序前候选池之外的商户');
        }
    }

    $responseIds = array_column($responseMerchants, 'mer_id');
    if (count($responseIds) === count($result)) {
        ok('日志返回商户与接口返回商户一致');
    } else {
        fail('日志返回商户数与接口不一致');
    }
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
            fail($label . '包含内部分析词：' . $word);
            return;
        }
    }
    ok($label . '未暴露内部分析词');
}

function isSorted(array $merchants, string $field, bool $descending = true): bool
{
    $count = count($merchants);
    if ($count < 2) {
        return true;
    }
    for ($i = 0; $i < $count - 1; $i++) {
        $left = (float)($merchants[$i][$field] ?? 0);
        $right = (float)($merchants[$i + 1][$field] ?? 0);
        if ($descending && $left < $right) {
            return false;
        }
        if (!$descending && $left > $right) {
            return false;
        }
    }
    return true;
}

$testCases = [
    [
        'label' => '最远的火锅',
        'message' => '我要吃最远的火锅',
        'assert' => function (array $merchants) {
            if (count($merchants) < 2) {
                fail('返回商户不足 2 家，无法校验距离排序');
                return;
            }
            if (isSorted($merchants, 'distance_km', true)) {
                ok('已按距离从远到近排序');
            } else {
                fail('未按距离从远到近排序');
            }
        },
    ],
    [
        'label' => '最近的火锅',
        'message' => '我要吃最近的火锅',
        'assert' => function (array $merchants) {
            if (count($merchants) < 2) {
                fail('返回商户不足 2 家，无法校验距离排序');
                return;
            }
            if (isSorted($merchants, 'distance_km', false)) {
                ok('已按距离从近到远排序');
            } else {
                fail('未按距离从近到远排序');
            }
        },
    ],
    [
        'label' => '最贵的火锅',
        'message' => '我要吃最贵的火锅',
        'assert' => function (array $merchants) {
            if (count($merchants) < 2) {
                fail('返回商户不足 2 家，无法校验价格排序');
                return;
            }
            if (isSorted($merchants, 'per_capita', true)) {
                ok('已按人均消费从高到低排序');
            } else {
                fail('未按人均消费从高到低排序');
            }
        },
    ],
    [
        'label' => '便宜点的火锅',
        'message' => '我要吃便宜点的火锅',
        'assert' => function (array $merchants) {
            if (count($merchants) < 2) {
                fail('返回商户不足 2 家，无法校验价格排序');
                return;
            }
            if (isSorted($merchants, 'per_capita', false)) {
                ok('已按人均消费从低到高排序');
            } else {
                fail('未按人均消费从低到高排序');
            }
        },
    ],
    [
        'label' => '8折左右',
        'message' => '8折左右',
        'assert' => function (array $merchants) {
            $rates = [];
            foreach ($merchants as $merchant) {
                $rate = discountRate($merchant);
                if ($rate !== null) {
                    $rates[] = $rate;
                }
            }
            if (!$rates) {
                fail('返回商户中未解析到折扣信息');
                return;
            }
            $firstRate = $rates[0];
            if ($firstRate >= 7.5 && $firstRate <= 8.5) {
                ok('首位商户折扣接近 8 折：' . $firstRate . ' 折');
            } else {
                fail('首位商户折扣偏离 8 折：' . $firstRate . ' 折');
            }
            $offCount = 0;
            foreach ($rates as $rate) {
                if ($rate < 7.0 || $rate > 9.0) {
                    $offCount++;
                }
            }
            if ($offCount === 0) {
                ok('所有返回商户折扣均在 7-9 折范围内');
            } else {
                fail("有 {$offCount} 家商户折扣不在 7-9 折范围内");
            }
        },
    ],
    [
        'label' => '口碑别太差',
        'message' => '口碑别太差的店',
        'assert' => function (array $merchants) {
            $minRating = PHP_FLOAT_MAX;
            foreach ($merchants as $merchant) {
                $rating = (float)($merchant['rating'] ?? 0);
                if ($rating > 0 && $rating < $minRating) {
                    $minRating = $rating;
                }
            }
            if ($minRating === PHP_FLOAT_MAX) {
                fail('返回商户中未解析到评分');
                return;
            }
            if ($minRating >= 3.0) {
                ok('返回商户最低评分 ' . $minRating . '，符合口碑不太差的要求');
            } else {
                fail('返回商户中存在低分店铺：' . $minRating);
            }
        },
    ],
    [
        'label' => '评分最高的店',
        'message' => '评分最高的店',
        'assert' => function (array $merchants) {
            if (count($merchants) < 2) {
                fail('返回商户不足 2 家，无法校验评分排序');
                return;
            }
            if (isSorted($merchants, 'rating', true)) {
                ok('已按评分从高到低排序');
            } else {
                fail('未按评分从高到低排序');
            }
            $maxRating = max(array_map(function ($m) {
                return (float)($m['rating'] ?? 0);
            }, $merchants));
            $firstRating = (float)($merchants[0]['rating'] ?? 0);
            if ($firstRating === $maxRating) {
                ok('首位商户为评分最高商户：' . $firstRating);
            } else {
                fail('首位商户评分 ' . $firstRating . ' 不是最高 ' . $maxRating);
            }
        },
    ],
];

echo "\n=== AI 动态排序测试（{$baseUrl}）===\n";

foreach ($testCases as $idx => $case) {
    echo "\n--- 用例 " . ($idx + 1) . ": {$case['label']} ---\n";
    echo "用户输入: {$case['message']}\n";

    $payload = array_merge($location, [
        'message' => $case['message'],
        'session_id' => 'test_rerank_' . md5($case['message']),
    ]);
    $result = callApi($baseUrl . '/api/huimaidan/ai/chat', $payload, $token);

    if ($result['error']) {
        fail('CURL 错误: ' . $result['error']);
        continue;
    }
    if ($result['http_code'] !== 200) {
        fail('HTTP 状态码异常: ' . $result['http_code']);
        continue;
    }
    ok('接口响应正常，耗时 ' . $result['elapsed_ms'] . 'ms');

    $body = json_decode($result['body'], true);
    assertUserFacingTextClean((string)($body['data']['content']['text'] ?? ''), 'AI回复文案');
    $merchants = $body['data']['content']['merchants'] ?? [];
    $degraded = !empty($body['data']['degraded']);
    $logId = (int)($body['data']['log_id'] ?? 0);

    if (!$merchants) {
        fail('未返回推荐商户');
        continue;
    }
    if ($degraded) {
        ok('当前处于降级模式，将回退到规则排序');
    } else {
        ok('LLM 动态排序已生效');
    }

    echo "  返回商户:\n";
    foreach (array_slice($merchants, 0, 5) as $merchant) {
        $distance = $merchant['distance'] ?? '未知';
        $price = $merchant['per_capita'] ?? '未知';
        $rating = $merchant['rating'] ?? '未知';
        $discount = $merchant['discount_label'] ?? '无';
        echo "    - {$merchant['mer_name']} | 距离 {$distance} | 人均 ¥{$price} | 评分 {$rating} | {$discount}\n";
        assertUserFacingTextClean((string)($merchant['recommend_reason'] ?? ''), '商户推荐理由');
    }

    $case['assert']($merchants);

    // 校验推荐日志中的候选池变化
    $log = fetchLog($logId);
    assertLogCandidates($log, $merchants);
}

echo "\n=== 测试结果 ===\n";
echo "通过: {$passes}\n";
echo "失败: {$failures}\n";

exit($failures > 0 ? 1 : 0);
