<?php

// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\common\repositories\huimaidan;

use app\common\dao\huimaidan\AiRecommendLogDao;
use app\common\model\huimaidan\AiRecommendLog;
use app\common\repositories\BaseRepository;

/**
 * @mixin AiRecommendLogDao
 */
class AiRecommendLogRepository extends BaseRepository
{
    public function __construct(AiRecommendLogDao $dao)
    {
        $this->dao = $dao;
    }

    public function record(array $data): int
    {
        try {
            $row = $this->dao->create([
                'uid' => (int)($data['uid'] ?? 0),
                'session_id' => (string)($data['session_id'] ?? ''),
                'query_text' => mb_substr((string)($data['query_text'] ?? ''), 0, 512),
                'intent_tags' => json_encode($data['intent_tags'] ?? [], JSON_UNESCAPED_UNICODE),
                'recall_count' => (int)($data['recall_count'] ?? 0),
                'candidate_mer_ids_before' => json_encode(array_values(array_map('intval', (array)($data['candidate_mer_ids_before'] ?? []))), JSON_UNESCAPED_UNICODE),
                'candidate_mer_ids_after' => json_encode(array_values(array_map('intval', (array)($data['candidate_mer_ids_after'] ?? []))), JSON_UNESCAPED_UNICODE),
                'rerank_source' => mb_substr((string)($data['rerank_source'] ?? ''), 0, 32),
                'fallback_reason' => mb_substr((string)($data['fallback_reason'] ?? ''), 0, 255),
                'result_mer_ids' => json_encode(array_values(array_map('intval', (array)($data['result_mer_ids'] ?? []))), JSON_UNESCAPED_UNICODE),
                'degraded' => !empty($data['degraded']) ? 1 : 0,
                'error_message' => mb_substr((string)($data['error_message'] ?? ''), 0, 255),
                'response_time_ms' => max(0, (int)($data['response_time_ms'] ?? 0)),
            ]);
            return (int)($row->log_id ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function trackEvent(int $uid, array $data): bool
    {
        if ($uid <= 0) {
            return false;
        }
        $payload = [];
        $event = (string)($data['event'] ?? '');
        if (in_array($event, ['click', 'detail', 'navigate'], true)) {
            $payload['click_mer_id'] = max(0, (int)($data['mer_id'] ?? 0));
        } elseif ($event === 'order') {
            $payload['order_mer_id'] = max(0, (int)($data['mer_id'] ?? 0));
        } elseif ($event === 'feedback') {
            $feedback = (int)($data['feedback'] ?? 0);
            $payload['user_feedback'] = max(-1, min(1, $feedback));
        }
        if (!$payload) {
            return false;
        }

        $logId = (int)($data['log_id'] ?? 0);
        if ($logId > 0) {
            return (bool)AiRecommendLog::getDB()->where('log_id', $logId)->where('uid', $uid)->update($payload);
        }

        $sessionId = (string)($data['session_id'] ?? '');
        if ($sessionId === '') {
            return false;
        }
        return (bool)AiRecommendLog::getDB()
            ->where('uid', $uid)
            ->where('session_id', $sessionId)
            ->order('log_id DESC')
            ->limit(1)
            ->update($payload);
    }

    /**
     * 统计近 24 小时推荐日志聚合指标
     * @param array $where 与 search 相同的查询条件
     * @return array
     */
    public function summary(array $where): array
    {
        $query = $this->dao->search($where);
        $total = (int)$query->count();
        if ($total === 0) {
            return [
                'total' => 0,
                'avg_response_time_ms' => 0,
                'degraded_rate' => 0.0,
                'empty_recall_rate' => 0.0,
                'llm_rerank_rate' => 0.0,
                'top_fallback_reasons' => [],
            ];
        }

        $avgResponseTime = round((float)(clone $query)->avg('response_time_ms'), 2);
        $degradedCount = (int)(clone $query)->where('degraded', 1)->count();
        $emptyRecallCount = (int)(clone $query)->where('recall_count', 0)->count();
        $llmRerankCount = (int)(clone $query)->where('rerank_source', 'llm')->count();

        $fallbackRows = (clone $query)
            ->where('fallback_reason', '<>', '')
            ->field('fallback_reason, COUNT(*) AS cnt')
            ->group('fallback_reason')
            ->order('cnt DESC')
            ->limit(5)
            ->select()
            ->toArray();

        return [
            'total' => $total,
            'avg_response_time_ms' => $avgResponseTime,
            'degraded_rate' => round($degradedCount / $total, 4),
            'empty_recall_rate' => round($emptyRecallCount / $total, 4),
            'llm_rerank_rate' => round($llmRerankCount / $total, 4),
            'top_fallback_reasons' => array_map(function ($row) {
                return [
                    'reason' => (string)$row['fallback_reason'],
                    'count' => (int)$row['cnt'],
                ];
            }, $fallbackRows),
        ];
    }
}
