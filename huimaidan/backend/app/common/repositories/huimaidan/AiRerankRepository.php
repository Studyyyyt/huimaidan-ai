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

use crmeb\services\ai\LlmClientService;

/**
 * LLM 动态排序仓库
 *
 * 职责：接收用户原话、意图标签和候选商户池，调用大模型对候选商户进行动态排序，
 * 并生成每家商户的推荐理由和整体回复文案。LLM 只能在候选池中排序，不能编造商户。
 */
class AiRerankRepository
{
    protected $configRepository;
    protected $promptRepository;
    protected $parser;
    protected $llmClient;

    public function __construct(
        AiConfigRepository $configRepository,
        AiPromptRepository $promptRepository,
        AiResponseParser $parser,
        LlmClientService $llmClient
    ) {
        $this->configRepository = $configRepository;
        $this->promptRepository = $promptRepository;
        $this->parser = $parser;
        $this->llmClient = $llmClient;
    }

    /**
     * 对候选商户进行 LLM 动态排序
     *
     * @param string $message 用户原话
     * @param array $intentTags NLU 解析后的意图标签
     * @param array $candidates 候选商户完整摘要数组
     * @param array $history 历史对话上下文
     * @return array [
     *   'sorted_mer_ids' => int[],    // 排序后的商户 ID
     *   'reasons' => [mer_id => reason], // 推荐理由
     *   'summary' => string,          // 整体一句话说明
     *   'degraded' => bool,
     *   'error_message' => string,
     *   'raw' => mixed,
     * ]
     */
    public function rerank(string $message, array $intentTags, array $candidates, array $history = []): array
    {
        $result = [
            'sorted_mer_ids' => [],
            'reasons' => [],
            'summary' => '',
            'degraded' => false,
            'error_message' => '',
            'raw' => null,
        ];

        if (!$candidates) {
            return $result;
        }

        if (!$this->enabled()) {
            $result['degraded'] = true;
            $result['error_message'] = 'LLM 动态排序未启用';
            return $result;
        }

        if (empty(config('huimaidan.ai.enabled', true))) {
            $result['degraded'] = true;
            $result['error_message'] = 'AI 服务未启用';
            return $result;
        }

        try {
            $prompt = $this->promptRepository->rerankPrompt($message, $intentTags, $candidates, $history);
            $timeout = $this->timeout();
            $overrides = ['response_format' => ['type' => 'json_object']];
            $response = $this->llmClient->completion($prompt['user'], '', [], $prompt['system'], $this->maxTokens(), $timeout, $overrides);
            $result['raw'] = $response;
            $json = $this->parser->parseJson((string)($response['text'] ?? ''));

            if (!is_array($json)) {
                $result['degraded'] = true;
                $result['error_message'] = 'LLM 未返回结构化排序结果';
                return $result;
            }

            return $this->normalizeRerankResult($json, $candidates);
        } catch (\Throwable $e) {
            $result['degraded'] = true;
            $result['error_message'] = 'LLM 动态排序失败：' . $e->getMessage();
            return $result;
        }
    }

    /**
     * 将 LLM 返回的排序结果校验、去重、过滤并补齐为可用格式
     */
    protected function normalizeRerankResult(array $json, array $candidates): array
    {
        $result = [
            'sorted_mer_ids' => [],
            'reasons' => [],
            'summary' => $this->sanitizeUserText((string)($json['summary'] ?? ''), 90),
            'degraded' => false,
            'error_message' => '',
            'raw' => $json,
        ];

        $validIds = [];
        foreach ($candidates as $candidate) {
            $merId = (int)($candidate['mer_id'] ?? 0);
            if ($merId > 0) {
                $validIds[$merId] = true;
            }
        }

        $items = isset($json['items']) && is_array($json['items']) ? $json['items'] : [];
        $seen = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $merId = (int)($item['mer_id'] ?? 0);
            if ($merId <= 0 || !isset($validIds[$merId]) || isset($seen[$merId])) {
                continue;
            }
            $seen[$merId] = true;
            $result['sorted_mer_ids'][] = $merId;
            $reason = $this->sanitizeUserText((string)($item['reason'] ?? ''), 80);
            if ($reason !== '') {
                $result['reasons'][$merId] = $reason;
            }
        }

        if (!$result['sorted_mer_ids']) {
            $result['degraded'] = true;
            $result['error_message'] = 'LLM 返回的排序结果未包含有效商户';
        }

        return $result;
    }

    /**
     * 使用 LLM 排序结果对规则排序结果进行重排和补齐
     *
     * @param array $ruleRanked 规则排序后的商户列表
     * @param array $rerankResult rerank 返回结果
     * @param int $limit 最终返回数量
     * @return array 重排后的商户列表
     */
    public function applyRerank(array $ruleRanked, array $rerankResult, int $limit): array
    {
        if (empty($rerankResult['sorted_mer_ids'])) {
            return array_slice($ruleRanked, 0, max(1, $limit));
        }

        $byId = [];
        foreach ($ruleRanked as $merchant) {
            $merId = (int)($merchant['mer_id'] ?? 0);
            if ($merId > 0) {
                $byId[$merId] = $merchant;
            }
        }

        $sorted = [];
        $used = [];
        foreach ($rerankResult['sorted_mer_ids'] as $merId) {
            if (isset($byId[$merId])) {
                $merchant = $byId[$merId];
                if (!empty($rerankResult['reasons'][$merId])) {
                    $merchant['recommend_reason'] = $rerankResult['reasons'][$merId];
                    $merchant['recommend_reason_source'] = 'llm';
                }
                $sorted[] = $merchant;
                $used[$merId] = true;
            }
        }

        // 补齐 LLM 未覆盖的商户，保持规则排序
        foreach ($ruleRanked as $merchant) {
            $merId = (int)($merchant['mer_id'] ?? 0);
            if ($merId > 0 && !isset($used[$merId])) {
                $sorted[] = $merchant;
            }
        }

        return array_slice($sorted, 0, max(1, $limit));
    }

    /**
     * 是否启用 LLM 动态排序
     */
    public function enabled(): bool
    {
        return (bool)$this->configRepository->int('llm_rerank_enabled', (int)config('huimaidan.ai.rerank.enabled', 1));
    }

    /**
     * 候选池数量上限
     */
    public function candidateLimit(): int
    {
        return $this->configRepository->int('llm_rerank_candidate_limit', (int)config('huimaidan.ai.rerank.candidate_limit', 50));
    }

    /**
     * 最终返回商户数量
     */
    public function resultLimit(): int
    {
        return $this->configRepository->int('llm_rerank_result_limit', (int)config('huimaidan.ai.rerank.result_limit', 3));
    }

    /**
     * LLM 失败是否回退规则排序
     */
    public function fallbackEnabled(): bool
    {
        return (bool)$this->configRepository->int('llm_rerank_fallback_enabled', (int)config('huimaidan.ai.rerank.fallback_enabled', 1));
    }

    /**
     * LLM 排序调用超时秒数
     */
    public function timeout(): int
    {
        $default = (int)config('huimaidan.ai.rerank.timeout', 0);
        if ($default <= 0) {
            $driver = (string)config('huimaidan.ai.llm_driver', 'bailian');
            $default = (int)config('huimaidan.ai.drivers.' . $driver . '.timeout', 15);
        }
        $configured = $this->configRepository->int('llm_rerank_timeout', $default);
        if ($configured <= 0) {
            $configured = $default;
        }
        return max(1, $configured);
    }

    /**
     * LLM 排序最大输出 token 数
     */
    public function maxTokens(): int
    {
        $default = (int)config('huimaidan.ai.rerank.max_tokens', 1024);
        return max(1, $this->configRepository->int('llm_rerank_max_tokens', $default));
    }

    protected function sanitizeUserText(string $text, int $limit): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?: '');
        if ($text === '') {
            return '';
        }
        $blocked = [
            'rule_score', 'score_factors', 'JSON', 'json', '候选商户', '排序依据', '排序核心',
            '意图标签', '规则解析', '规则排序', 'LLM', 'llm', '模型分析', '内部', 'rank',
            '用户明确', '核心诉求', '核心需求', '三家中', '商户均', '标注', '字段', '后端召回', '召回得分',
            'facility', 'facilities', 'category', 'scene', 'backend', 'score', '后台召回分',
            'has_private_room', 'has_parking', 'has_baby_chair', 'has_large_table', 'is_non_smoking',
            'business_hours', 'meal_is_default', 'late_night', 'breakfast', 'brunch', 'lunch',
            'dinner', 'tea', '标签权重', '完全匹配', '显著提升', '权重', '匹配度', '候选集', '候选', 'supper',
            '未明示', '适配性', '高度契合', '高度匹配', '标签明确', '标签突出', '标签', '候选中', '推测', '可能',
            '编号', 'ID', 'mer_id',
            '不推荐', '不适合', '不太适合', '不建议', '缺点', '但是不',
        ];
        foreach ($blocked as $word) {
            if (mb_stripos($text, $word) !== false) {
                return '';
            }
        }
        return mb_substr($text, 0, max(1, $limit));
    }
}
