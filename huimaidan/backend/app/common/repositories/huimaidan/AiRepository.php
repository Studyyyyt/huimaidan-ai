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

use app\common\model\store\CityArea;
use crmeb\services\ai\LlmClientService;
use think\exception\ValidateException;
use think\facade\Cache;

class AiRepository
{
    protected $bannerConfigRepository;
    protected $recommendRepository;
    protected $rerankRepository;
    protected $nluRepository;
    protected $sessionRepository;
    protected $logRepository;
    protected $learningRepository;
    protected $promptRepository;
    protected $parser;
    protected $llmClient;
    protected $replyGenerationError = '';

    public function __construct(
        AiBannerConfigRepository $bannerConfigRepository,
        AiRecommendRepository $recommendRepository,
        AiRerankRepository $rerankRepository,
        AiNluRepository $nluRepository,
        AiSessionRepository $sessionRepository,
        AiRecommendLogRepository $logRepository,
        AiUserLearningRepository $learningRepository,
        AiPromptRepository $promptRepository,
        AiResponseParser $parser,
        LlmClientService $llmClient
    ) {
        $this->bannerConfigRepository = $bannerConfigRepository;
        $this->recommendRepository = $recommendRepository;
        $this->rerankRepository = $rerankRepository;
        $this->nluRepository = $nluRepository;
        $this->sessionRepository = $sessionRepository;
        $this->logRepository = $logRepository;
        $this->learningRepository = $learningRepository;
        $this->promptRepository = $promptRepository;
        $this->parser = $parser;
        $this->llmClient = $llmClient;
    }

    public function banner(array $params, int $uid = 0): array
    {
        $location = $this->location($params, false);
        $mealType = $this->bannerConfigRepository->currentMealType();
        $cityId = $this->resolveCityId($params);
        $cacheKey = 'ai:banner:' . $mealType . ':' . $cityId . ':' . md5(json_encode($location));
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }
        $config = $this->bannerConfigRepository->configByMealType($mealType);
        $intent = [
            'meal' => [$mealType],
            'distance' => $location ? 'near' : '',
        ];
        $recommend = $this->recommendRepository->recommend($intent, $location + ['city_id' => $cityId], $uid, 1);
        if (empty($recommend['list']) && $location) {
            $recommend = $this->recommendRepository->recommend(['meal' => [$mealType]], $location + ['city_id' => $cityId], $uid, 1);
        }
        if (empty($recommend['list'])) {
            $recommend = $this->recommendRepository->recommend([], [], $uid, 1);
        }
        $merchant = $recommend['list'][0] ?? null;
        $result = [
            'meal_type' => $mealType,
            'title' => (string)($config['title_template'] ?? ''),
            'subtitle' => $this->bannerSubtitle((string)($config['subtitle_template'] ?? ''), $merchant),
            'recommend_merchant' => $merchant ? [
                'mer_id' => (int)$merchant['mer_id'],
                'mer_name' => (string)$merchant['mer_name'],
                'mer_avatar' => (string)($merchant['mer_avatar'] ?? ''),
                'discount_label' => (string)($merchant['discount_label'] ?? ''),
                'distance' => (string)($merchant['distance'] ?? ''),
            ] : null,
            'background_color' => (string)($config['bg_color'] ?? '#FFF3E0'),
            'text_color' => (string)($config['text_color'] ?? '#E65100'),
            'degraded' => false,
        ];
        Cache::set($cacheKey, $result, (int)config('huimaidan.ai.banner.cache_ttl', 300));
        return $result;
    }

    public function chat(int $uid, array $params): array
    {
        if ($uid <= 0) {
            throw new ValidateException('请先登录后使用AI推荐');
        }
        $message = trim((string)($params['message'] ?? ''));
        if ($message === '') {
            throw new ValidateException('请输入您的需求');
        }
        $maxMessageLength = $this->maxMessageLength();
        if (mb_strlen($message) > $maxMessageLength) {
            throw new ValidateException('输入内容不能超过' . $maxMessageLength . '个字符');
        }
        if ($this->containsSensitiveWord($message)) {
            throw new ValidateException('输入内容包含暂不支持的敏感词，请调整后再试');
        }
        $this->checkRateLimit($uid);
        $startedAt = microtime(true);
        $location = $this->location($params, false);
        $cityId = $this->resolveCityId($params);
        $session = $this->sessionRepository->readOrCreate((string)($params['session_id'] ?? ''), $uid, $location + ['city_id' => $cityId]);
        $nlu = $this->isNonDiningQuestion($message, $this->emptyChatIntent())
            ? ['intent_tags' => $this->emptyChatIntent(), 'degraded' => false, 'error_message' => '']
            : $this->nluRepository->parse($message, (array)($session['history'] ?? []));
        $intent = $nlu['intent_tags'];
        $rerankInfo = [
            'candidate_mer_ids_before' => [],
            'candidate_mer_ids_after' => [],
            'rerank_source' => 'none',
            'fallback_reason' => '',
            'rerank_degraded' => false,
            'rerank_error' => '',
        ];
        if ($this->isNonDiningQuestion($message, $intent)) {
            $intent['off_topic'] = true;
            $recommend = ['count' => 0, 'list' => [], 'candidates' => []];
            $merchants = [];
            $text = $this->nonDiningReplyText($message);
        } else {
            $recommend = $this->recommendRepository->recommend($intent, $location + ['city_id' => $cityId], $uid);
            $ruleRanked = $recommend['candidates'] ?? $recommend['list'];
            $rerankInfo['candidate_mer_ids_before'] = array_column($ruleRanked, 'mer_id');
            $merchants = $this->applyLlmRerank($message, $intent, $ruleRanked, (array)($session['history'] ?? []), $rerankInfo);
            $summary = $this->safeUserFacingText((string)($rerankInfo['llm_summary'] ?? ''), 120);
            $text = $summary !== ''
                ? $summary
                : $this->fallbackReplyText($intent, $merchants, !empty($nlu['degraded']) || $rerankInfo['rerank_degraded']);
        }
        $text = $this->withFriendlyAddress($text);
        $degraded = !empty($nlu['degraded']) || $this->replyGenerationError !== '' || $rerankInfo['rerank_degraded'];
        $errorMessage = trim(
            (string)($nlu['error_message'] ?? '')
            . ($this->replyGenerationError !== '' ? ' ' . $this->replyGenerationError : '')
            . ($rerankInfo['rerank_error'] !== '' ? ' ' . $rerankInfo['rerank_error'] : '')
        );
        $session = $this->sessionRepository->append($session, 'user', $message, ['intent_tags' => $intent]);
        $this->sessionRepository->append($session, 'ai', $text, ['mer_ids' => array_column($merchants, 'mer_id'), 'intent_tags' => $intent]);
        $responseTime = (int)round((microtime(true) - $startedAt) * 1000);
        $logId = $this->logRepository->record([
            'uid' => $uid,
            'session_id' => (string)$session['session_id'],
            'query_text' => $message,
            'intent_tags' => $intent,
            'recall_count' => (int)$recommend['count'],
            'result_mer_ids' => array_column($merchants, 'mer_id'),
            'candidate_mer_ids_before' => $rerankInfo['candidate_mer_ids_before'],
            'candidate_mer_ids_after' => $rerankInfo['candidate_mer_ids_after'],
            'rerank_source' => $rerankInfo['rerank_source'],
            'fallback_reason' => $rerankInfo['fallback_reason'],
            'degraded' => $degraded,
            'error_message' => $errorMessage,
            'response_time_ms' => $responseTime,
        ]);
        return [
            'log_id' => $logId,
            'session_id' => (string)$session['session_id'],
            'type' => $merchants ? 'recommend' : 'text',
            'content' => [
                'text' => $text,
                'merchants' => $merchants,
                'intent_tags' => $intent,
            ],
            'degraded' => $degraded,
            'error_message' => $errorMessage,
        ];
    }

    public function event(int $uid, array $params): array
    {
        if ($uid <= 0) {
            throw new ValidateException('请先登录后使用AI推荐');
        }
        $event = (string)($params['event'] ?? '');
        if (!in_array($event, ['click', 'detail', 'navigate', 'order', 'feedback'], true)) {
            throw new ValidateException('事件类型错误');
        }
        if (in_array($event, ['click', 'detail', 'navigate', 'order'], true) && (int)($params['mer_id'] ?? 0) <= 0) {
            throw new ValidateException('请选择商户');
        }
        if ($event === 'feedback' && !in_array((int)($params['feedback'] ?? 0), [-1, 0, 1], true)) {
            throw new ValidateException('反馈值错误');
        }
        return $this->learningRepository->trackEvent($uid, $params);
    }

    protected function checkRateLimit(int $uid): void
    {
        $key = 'ai:rate:' . $uid . ':' . date('Ymd');
        $count = (int)Cache::get($key);
        $max = (int)config('huimaidan.ai.rate_limit.daily_max', 500);
        try {
            $max = app()->make(AiConfigRepository::class)->int('daily_chat_limit', $max);
        } catch (\Throwable $e) {
        }
        if ($count >= $max) {
            throw new ValidateException('今日AI推荐次数已用完，请明天再试');
        }
        Cache::set($key, $count + 1, strtotime('tomorrow') - time());
    }

    protected function maxMessageLength(): int
    {
        $default = (int)config('huimaidan.ai.input.max_message_length', 200);
        try {
            return max(1, app()->make(AiConfigRepository::class)->int('input_max_length', $default));
        } catch (\Throwable $e) {
            return max(1, $default);
        }
    }

    protected function containsSensitiveWord(string $message): bool
    {
        $words = (array)config('huimaidan.ai.input.sensitive_words', []);
        try {
            $configured = app()->make(AiConfigRepository::class)->text('sensitive_words', '');
            if ($configured !== '') {
                $words = array_merge($words, preg_split('/[,，\n\r]+/u', $configured) ?: []);
            }
        } catch (\Throwable $e) {
        }
        foreach ($words as $word) {
            $word = trim((string)$word);
            if ($word !== '' && mb_stripos($message, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function applyLlmRerank(string $message, array $intent, array $ruleRanked, array $history, array &$rerankInfo): array
    {
        $limit = $this->rerankRepository->resultLimit();
        if (!$ruleRanked) {
            return [];
        }

        $rerankResult = $this->rerankRepository->rerank($message, $intent, $ruleRanked, $history);
        $rerankInfo['candidate_mer_ids_after'] = $rerankResult['sorted_mer_ids'];
        $rerankInfo['rerank_degraded'] = !empty($rerankResult['degraded']);
        $rerankInfo['rerank_error'] = (string)($rerankResult['error_message'] ?? '');
        if (!empty($rerankResult['summary'])) {
            $rerankInfo['llm_summary'] = (string)$rerankResult['summary'];
        }

        if (!$rerankResult['degraded'] && !empty($rerankResult['sorted_mer_ids'])) {
            $rerankInfo['rerank_source'] = 'llm';
            return $this->rerankRepository->applyRerank($ruleRanked, $rerankResult, $limit);
        }

        // 降级：回退到规则排序结果
        if ($this->rerankRepository->fallbackEnabled()) {
            $rerankInfo['rerank_source'] = 'rule_fallback';
            $rerankInfo['fallback_reason'] = $rerankInfo['rerank_error'] ?: 'LLM动态排序未返回有效结果';
            return array_slice($ruleRanked, 0, max(1, $limit));
        }

        // 如果未启用 fallback 且 LLM 失败，返回空结果
        $rerankInfo['rerank_source'] = 'none';
        $rerankInfo['fallback_reason'] = $rerankInfo['rerank_error'] ?: 'LLM动态排序失败且未启用规则兜底';
        return [];
    }

    protected function replyText(array $intent, array $merchants, bool $degraded): string
    {
        $this->replyGenerationError = '';
        $fallbackText = $this->fallbackReplyText($intent, $merchants, $degraded);
        if ($degraded || !$merchants || $this->shouldUseLocalReply($intent) || empty(config('huimaidan.ai.enabled', true))) {
            return $fallbackText;
        }
        try {
            $prompt = $this->promptRepository->reasoningPrompt($intent, $merchants, $fallbackText);
            $response = $this->llmClient->completion($prompt['user'], '', [], $prompt['system'], null, null, ['response_format' => ['type' => 'json_object']]);
            $json = $this->parser->parseJson((string)($response['text'] ?? ''));
            $text = is_array($json) ? trim((string)($json['text'] ?? '')) : '';
            if ($text !== '') {
                $safeText = $this->safeUserFacingText($text, 180);
                if ($safeText !== '') {
                    return $safeText;
                }
            }
            $this->replyGenerationError = 'AI回复未返回结构化文案';
            return $fallbackText;
        } catch (\Throwable $e) {
            $this->replyGenerationError = 'AI回复生成失败：' . $e->getMessage();
            return $fallbackText;
        }
    }

    protected function shouldUseLocalReply(array $intent): bool
    {
        // 默认走 LLM 生成推荐理由，只有非餐饮问题或 LLM 失败时才回退到本地模板
        return !empty($intent['off_topic']);
    }

    protected function safeUserFacingText(string $text, int $limit): string
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

    protected function isNonDiningQuestion(string $message, array $intent): bool
    {
        return !$this->hasDiningSignal($message, $intent);
    }

    protected function hasDiningSignal(string $message, array $intent): bool
    {
        foreach (['category', 'scene', 'taste', 'facility', 'feature', 'promotion'] as $key) {
            if (!empty($intent[$key])) {
                return true;
            }
        }
        if (!empty($intent['price']) || !empty($intent['distance']) || !empty($intent['action'])) {
            return true;
        }
        if (!empty($intent['meal']) && empty($intent['meal_is_default'])) {
            return true;
        }
        return preg_match('/吃|喝|餐|饭|菜|美食|店|商家|优惠|买单|附近|周边|推荐|便宜|不贵|实惠|划算|预算|元|块|聚餐|约会|亲子|包间|大桌|火锅|烧烤|烤肉|串串|奶茶|咖啡|甜品|夜宵|早餐|午餐|晚餐|下午茶|快餐|日料|川菜|湘菜|粤菜|粥|面|粉|饺子|汉堡|披萨|折|折扣|我饿了|换一家|换个|不要这家|下一家|再来一家|近一点|离我近|最近|远一点|远点|最远/u', $message) === 1;
    }

    protected function emptyChatIntent(): array
    {
        $mealType = $this->bannerConfigRepository->currentMealType();
        return [
            'category' => [],
            'scene' => [],
            'taste' => [],
            'facility' => [],
            'feature' => [],
            'meal' => [$mealType],
            'promotion' => [],
            'price' => '',
            'price_range' => '',
            'time' => [$mealType],
            'people' => '',
            'distance' => '',
            'action' => '',
            'exclude_mer_ids' => [],
            'meal_is_default' => true,
            'requires_open_now' => false,
        ];
    }

    protected function nonDiningReplyText(string $message = ''): string
    {
        if (preg_match('/你是谁|你叫(什么|啥)|叫什么名字|名字/u', $message)) {
            return '我叫 AI 小惠，是惠买单里的优惠商家推荐助手。你可以告诉我想吃什么、预算多少、几个人、想离你多近，我来帮你挑附近合适的店。';
        }
        if (preg_match('/你好|您好/u', $message)) {
            return '你好，我是 AI 小惠。想找附近优惠好店时，可以直接告诉我口味、预算、人数或距离要求。';
        }
        if (preg_match('/谢谢|谢了/u', $message)) {
            return '不客气，我一直在这儿。想找吃的或附近优惠商家时，直接告诉我需求就行。';
        }
        return '我主要帮你找附近优惠商家。你可以告诉我想吃什么、预算、人数、距离要求，我来帮你推荐。';
    }

    protected function withFriendlyAddress(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '亲，我主要帮你找附近优惠商家。你可以告诉我想吃什么、预算、人数或距离要求。';
        }
        if (preg_match('/^(亲|您好|你好)[，,！!。]?/u', $text)) {
            return $text;
        }
        return '亲，' . $text;
    }

    protected function fallbackReplyText(array $intent, array $merchants, bool $degraded): string
    {
        if (!$merchants) {
            return ($degraded ? 'AI服务暂时繁忙，' : '') . '暂时没有找到完全匹配的优惠商家，可以换个预算、品类或扩大距离试试。';
        }
        $lead = $degraded ? 'AI服务暂时繁忙，我先按规则为你筛选。' : '';
        $merchant = $merchants[0];
        $parts = [];
        foreach (['category', 'scene', 'taste', 'facility'] as $key) {
            foreach ((array)($intent[$key] ?? []) as $value) {
                $label = $this->intentDisplayLabel((string)$value);
                if ($label !== '') {
                    $parts[] = $label;
                }
            }
        }
        $summary = $parts ? '根据你提到的' . implode('、', array_slice(array_unique($parts), 0, 3)) . '，' : '';
        return $lead . $summary . '为你优先推荐「' . $merchant['mer_name'] . '」。' . ($merchant['recommend_reason'] ?? '');
    }

    protected function intentDisplayLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $map = [
            'has_private_room' => '包间',
            'private_room' => '包间',
            'has_parking' => '停车方便',
            'parking' => '停车方便',
            'has_baby_chair' => '宝宝椅',
            'baby_chair' => '宝宝椅',
            'has_large_table' => '大桌',
            'large_table' => '大桌',
            'is_non_smoking' => '无烟环境',
            'non_smoking' => '无烟环境',
        ];
        return $map[$value] ?? $value;
    }

    protected function bannerSubtitle(string $template, ?array $merchant): string
    {
        if (!$merchant) {
            return $template;
        }
        $replace = [
            '{merchant}' => (string)($merchant['mer_name'] ?? ''),
            '{discount}' => (string)($merchant['discount_label'] ?? ''),
            '{distance}' => (string)($merchant['distance'] ?? ''),
        ];
        $text = strtr($template, $replace);
        if (strpos($template, '{') === false && !empty($merchant['discount_label'])) {
            $text .= '，' . $merchant['discount_label'] . '优惠';
        }
        return $text;
    }

    protected function location(array $params, bool $required): array
    {
        $hasLatitude = isset($params['latitude']) && $params['latitude'] !== '';
        $hasLongitude = isset($params['longitude']) && $params['longitude'] !== '';
        if (!$hasLatitude && !$hasLongitude) {
            if ($required) {
                throw new ValidateException('请提供经纬度');
            }
            return [];
        }
        if (!$hasLatitude || !$hasLongitude) {
            throw new ValidateException('请同时提供经纬度');
        }
        if (!is_numeric($params['latitude']) || !is_numeric($params['longitude'])) {
            throw new ValidateException('经纬度格式错误');
        }
        $latitude = (float)$params['latitude'];
        $longitude = (float)$params['longitude'];
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new ValidateException('经纬度超出合法范围');
        }
        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    protected function resolveCityId(array $params): int
    {
        $cityId = (int)($params['city_id'] ?? 0);
        if ($cityId > 0) {
            return $cityId;
        }

        $cityName = trim((string)($params['city_name'] ?? ''));
        if ($cityName === '') {
            return 0;
        }

        $normalized = preg_replace('/市$/u', '', $cityName);
        $query = CityArea::getDB()->where('level', 2);
        $query->where(function ($query) use ($cityName, $normalized) {
            $query->where('name', $cityName);
            if ($normalized !== $cityName && $normalized !== '') {
                $query->whereOr('name', $normalized);
                $query->whereOr('name', $normalized . '市');
            }
        });
        return (int)$query->value('id');
    }
}
