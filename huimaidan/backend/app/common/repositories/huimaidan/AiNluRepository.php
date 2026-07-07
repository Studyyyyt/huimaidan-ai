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

class AiNluRepository
{
    protected $tagRepository;
    protected $parser;
    protected $llmClient;
    protected $promptRepository;

    public function __construct(
        AiTagRepository $tagRepository,
        AiResponseParser $parser,
        LlmClientService $llmClient,
        AiPromptRepository $promptRepository
    ) {
        $this->tagRepository = $tagRepository;
        $this->parser = $parser;
        $this->llmClient = $llmClient;
        $this->promptRepository = $promptRepository;
    }

    public function parse(string $message, array $history = []): array
    {
        $message = trim($message);
        $fallback = $this->ruleParse($message, $history);
        if ($this->shouldUseRuleOnly($message, $history, $fallback)) {
            return ['intent_tags' => $fallback, 'degraded' => false, 'error_message' => ''];
        }
        if (empty(config('huimaidan.ai.enabled', true))) {
            return ['intent_tags' => $fallback, 'degraded' => true, 'error_message' => 'AI服务未启用，已使用规则解析'];
        }

        try {
            $prompt = $this->promptRepository->nluPrompt($message, $history, $fallback);
            $response = $this->llmClient->completion($prompt['user'], '', [], $prompt['system'], null, null, ['response_format' => ['type' => 'json_object']]);
            $json = $this->parser->parseJson((string)($response['text'] ?? ''));
            if (is_array($json)) {
                $intent = $this->normalizeIntent(array_merge($fallback, $json));
                return [
                    'intent_tags' => $this->correctDiscountPrice($intent, $message),
                    'degraded' => false,
                    'error_message' => '',
                ];
            }
            return ['intent_tags' => $this->correctDiscountPrice($fallback, $message), 'degraded' => true, 'error_message' => 'AI未返回结构化意图，已使用规则解析'];
        } catch (\Throwable $e) {
            return ['intent_tags' => $this->correctDiscountPrice($fallback, $message), 'degraded' => true, 'error_message' => $e->getMessage()];
        }
    }

    public function ruleParse(string $message, array $history = []): array
    {
        $intent = [
            'category' => [],
            'scene' => [],
            'taste' => [],
            'facility' => [],
            'price' => '',
            'meal' => [],
            'distance' => '',
            'action' => '',
            'exclude_mer_ids' => [],
            'meal_is_default' => false,
            'requires_open_now' => false,
        ];
        foreach ($this->tagRepository->keywordMap() as $word => $tag) {
            if ($word !== '' && mb_strpos($message, $word) !== false) {
                $type = $tag['type'];
                $value = $tag['value'];
                if (in_array($type, ['category', 'scene', 'taste', 'facility', 'feature', 'meal', 'promotion'], true)) {
                    $intent[$type][] = $value;
                } elseif ($type === 'price') {
                    $intent['price'] = $value;
                }
            }
        }

        if (preg_match('/([0-9]{2,4})\s*(元|块)?(以内|以下|内|左右)?/u', $message, $match)) {
            $intent['price'] = $this->priceRangeByBudget((int)$match[1]);
        }
        if (preg_match('/便宜|不贵|实惠|划算|性价比/u', $message)) {
            $intent['price'] = $intent['price'] ?: '30-60';
        }
        if (preg_match('/最贵|贵一点|贵点|高端|档次高|请客|商务宴请/u', $message)) {
            $intent['action'] = $intent['action'] ?: 'expensive';
            $intent['price'] = $intent['price'] ?: '150+';
        }
        if (preg_match('/附近|离我近|近一点|最近/u', $message)) {
            $intent['distance'] = 'near';
            if ($history) {
                $intent['action'] = $intent['action'] ?: 'nearer';
            }
        }
        if (preg_match('/最远|远一点|远点|远的|远一些|不要太近/u', $message)) {
            $intent['distance'] = 'far';
            if ($history) {
                $intent['action'] = $intent['action'] ?: 'farther';
            }
        }
        if (preg_match('/现在|马上|立刻|当前|营业中|还开门|开着/u', $message)) {
            $intent['requires_open_now'] = true;
        }
        $discountRate = $this->parseDiscountRate($message);
        if ($discountRate > 0 && $discountRate <= 1) {
            $intent['discount_rate'] = $discountRate;
            $intent['promotion'][] = $this->discountRateLabel($discountRate);
        }
        if (preg_match('/换一家|换个|不要这家|下一家/u', $message)) {
            $intent['action'] = 'replace';
            $intent['exclude_mer_ids'] = $this->lastMerchantIds($history);
        }
        if (preg_match('/便宜点|更便宜/u', $message)) {
            $intent['action'] = 'cheaper';
            $intent['price'] = $this->lowerPrice($this->lastPrice($history) ?: $intent['price']);
        }
        if (empty($intent['meal'])) {
            $intent['meal'] = [$this->currentMealType()];
            $intent['meal_is_default'] = true;
        }
        return $this->normalizeIntent($this->mergeHistory($intent, $history));
    }

    protected function shouldUseRuleOnly(string $message, array $history, array $intent): bool
    {
        // 只有明确的多轮指令才纯走规则，其他情况都让 LLM 参与语义理解
        if (!empty($intent['action']) && in_array($intent['action'], ['replace', 'cheaper', 'nearer', 'farther'], true)) {
            return true;
        }
        return preg_match('/^(便宜点|更便宜|换一家|换个|下一家|离我近点|近一点|最近的?|离我远点|远一点|最远的?)$/u', $message) === 1;
    }

    protected function hasRuleIntent(array $intent): bool
    {
        foreach (['category', 'scene', 'taste', 'facility', 'feature', 'promotion'] as $key) {
            if (!empty($intent[$key])) {
                return true;
            }
        }
        return !empty($intent['price']) || !empty($intent['distance']) || !empty($intent['action']);
    }

    protected function currentMealType(?int $hour = null): string
    {
        $hour = is_null($hour) ? (int)date('G') : $hour;
        if ($hour < 6) {
            return 'late_night';
        }
        if ($hour < 9) {
            return 'breakfast';
        }
        if ($hour < 11) {
            return 'brunch';
        }
        if ($hour < 14) {
            return 'lunch';
        }
        if ($hour < 17) {
            return 'tea';
        }
        if ($hour < 21) {
            return 'dinner';
        }
        if ($hour < 24) {
            return 'supper';
        }
        return 'late_night';
    }

    protected function normalizeIntent(array $intent): array
    {
        $normalized = [];
        if (empty($intent['price']) && !empty($intent['price_range'])) {
            $intent['price'] = $intent['price_range'];
        }
        if (empty($intent['meal']) && !empty($intent['time'])) {
            $intent['meal'] = $intent['time'];
        }
        foreach (['category', 'scene', 'taste', 'facility', 'feature', 'meal', 'promotion'] as $key) {
            if (isset($intent[$key]) && is_string($intent[$key])) {
                $intent[$key] = [$intent[$key]];
            }
            $normalized[$key] = array_values(array_filter(array_unique((array)($intent[$key] ?? [])), function ($item) {
                return trim((string)$item) !== '';
            }));
        }
        $normalized['price'] = $this->stringOrFirst($intent['price'] ?? '');
        $normalized['price_range'] = $normalized['price'];
        $normalized['time'] = $normalized['meal'];
        $normalized['people'] = $this->stringOrFirst($intent['people'] ?? '');
        $normalized['distance'] = $this->stringOrFirst($intent['distance'] ?? '');
        $normalized['action'] = $this->stringOrFirst($intent['action'] ?? '');
        $normalized['discount_rate'] = $this->parseDiscountRateFromIntent($intent);
        $normalized['exclude_mer_ids'] = array_values(array_filter(array_unique(array_map('intval', (array)($intent['exclude_mer_ids'] ?? [])))));
        $normalized['meal_is_default'] = !empty($intent['meal_is_default']);
        $normalized['requires_open_now'] = !empty($intent['requires_open_now']);
        return $normalized;
    }

    /**
     * 将值安全转换为字符串。如果是数组，取第一个非空元素。
     */
    protected function stringOrFirst($value): string
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $str = trim((string)$item);
                if ($str !== '') {
                    return $str;
                }
            }
            return '';
        }
        return trim((string)$value);
    }

    protected function mergeHistory(array $intent, array $history): array
    {
        $lastIntent = [];
        for ($i = count($history) - 1; $i >= 0; $i--) {
            if (!empty($history[$i]['intent_tags']) && is_array($history[$i]['intent_tags'])) {
                $lastIntent = $history[$i]['intent_tags'];
                break;
            }
        }
        if (!$lastIntent) {
            return $intent;
        }
        foreach (['category', 'scene', 'taste', 'facility'] as $key) {
            if (empty($intent[$key]) && !empty($lastIntent[$key])) {
                $intent[$key] = (array)$lastIntent[$key];
            }
        }
        if ($intent['price'] === '' && !empty($lastIntent['price'])) {
            $intent['price'] = (string)$lastIntent['price'];
        }
        return $intent;
    }

    protected function priceRangeByBudget(int $budget): string
    {
        if ($budget <= 30) {
            return '0-30';
        }
        if ($budget <= 60) {
            return '30-60';
        }
        if ($budget <= 100) {
            return '60-100';
        }
        if ($budget <= 150) {
            return '100-150';
        }
        return '150+';
    }

    protected function lowerPrice(string $price): string
    {
        $order = ['150+' => '100-150', '100-150' => '60-100', '60-100' => '30-60', '30-60' => '0-30'];
        return $order[$price] ?? '30-60';
    }

    /**
     * 从用户输入中解析折扣率，统一返回 0~1 之间的小数。
     * 支持：8折、8.5折、85折、7.5折、5折等。
     */
    protected function parseDiscountRate(string $message): float
    {
        if (preg_match('/([0-9]+(?:\.[0-9]+)?)\s*折/u', $message, $match)) {
            $value = (float)$match[1];
            if ($value >= 1 && $value <= 10) {
                return round($value / 10, 2);
            }
        }
        if (preg_match('/([0-9]{1,2})\s*折/u', $message, $match)) {
            $value = (int)$match[1];
            if ($value >= 1 && $value <= 10) {
                return round($value / 10, 2);
            }
        }
        return 0.0;
    }

    /**
     * 把折扣率转成标签文本，用于 promotion 标签匹配。
     */
    protected function discountRateLabel(float $rate): string
    {
        if ($rate <= 0) {
            return '';
        }
        $percent = round($rate * 10, 1);
        if ($percent == (int)$percent) {
            return (int)$percent . '折';
        }
        return $percent . '折';
    }

    /**
     * 从意图数组中读取折扣率，优先使用已解析的 discount_rate。
     */
    protected function parseDiscountRateFromIntent(array $intent): float
    {
        if (isset($intent['discount_rate']) && is_numeric($intent['discount_rate'])) {
            $rate = (float)$intent['discount_rate'];
            if ($rate > 0 && $rate <= 1) {
                return $rate;
            }
        }
        return 0.0;
    }

    /**
     * 校正折扣与价格的冲突：用户说“8折”时，LLM 容易把折扣数字误填成 price。
     * 当检测到明确折扣表述且 price 看起来像是误标时，清空 price 避免错误硬过滤。
     */
    protected function correctDiscountPrice(array $intent, string $message): array
    {
        $discountRate = (float)($intent['discount_rate'] ?? 0);
        if ($discountRate <= 0 || $discountRate > 1) {
            return $intent;
        }
        if (mb_strpos($message, '折') === false) {
            return $intent;
        }
        $price = (string)($intent['price'] ?? '');
        if ($price === '') {
            return $intent;
        }
        // 常见误标：把 8折/7.5折 理解为 80/75 元，落入 0-100 或 60-100
        if (in_array($price, ['0-100', '60-100', '100-150'], true)) {
            $intent['price'] = '';
            $intent['price_range'] = '';
        }
        return $intent;
    }

    protected function lastPrice(array $history): string
    {
        for ($i = count($history) - 1; $i >= 0; $i--) {
            if (!empty($history[$i]['intent_tags']['price'])) {
                return (string)$history[$i]['intent_tags']['price'];
            }
        }
        return '';
    }

    protected function lastMerchantIds(array $history): array
    {
        for ($i = count($history) - 1; $i >= 0; $i--) {
            if (!empty($history[$i]['mer_ids'])) {
                return array_values(array_map('intval', (array)$history[$i]['mer_ids']));
            }
        }
        return [];
    }
}
