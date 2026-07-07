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

namespace crmeb\services\ai;

use app\common\repositories\huimaidan\AiConfigRepository;
use think\exception\ValidateException;
use think\facade\Cache;

class LlmClientService
{
    protected $bailianClient;
    protected $openAiClient;
    protected $claudeClient;

    public function __construct(BailianAppClient $bailianClient, OpenAiCompatibleClient $openAiClient, ClaudeClient $claudeClient)
    {
        $this->bailianClient = $bailianClient;
        $this->openAiClient = $openAiClient;
        $this->claudeClient = $claudeClient;
    }

    public function completion(string $prompt, string $sessionId = '', array $bizParams = [], string $systemPrompt = '', ?int $maxTokens = null, ?int $timeout = null, array $overrides = []): array
    {
        $this->assertCircuitClosed();
        $driver = $this->overrideConfig($overrides, 'llm_driver', $this->configString('llm_driver', (string)config('huimaidan.ai.llm_driver', 'bailian')));
        if (!in_array($driver, ['bailian', 'deepseek', 'claude'], true)) {
            $driver = 'bailian';
        }
        $lastError = null;
        // 默认不重试：LLM 排序/NLU 耗时较长，重试会让用户等待时间翻倍，体验更差
        $retryTimes = max(0, $this->configInt('llm_retry_times', (int)config('huimaidan.ai.llm.retry_times', 0)));
        $sleepMs = max(0, $this->configInt('llm_retry_sleep_ms', (int)config('huimaidan.ai.llm.retry_sleep_ms', 200)));

        for ($attempt = 0; $attempt <= $retryTimes; $attempt++) {
            try {
                $result = $this->callDriver($driver, $prompt, $sessionId, $bizParams, $systemPrompt, $maxTokens, $timeout, $overrides);
                $this->resetCircuit();
                return [
                    'driver' => $driver,
                    'raw' => $result['raw'],
                    'text' => $result['text'],
                    'session_id' => $result['session_id'] ?? '',
                ];
            } catch (\Throwable $e) {
                $lastError = $e;
                // 超时类错误不再重试，避免让总等待时间翻倍
                if ($this->isTimeoutError($e)) {
                    break;
                }
                if ($attempt < $retryTimes && $sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            }
        }

        $this->recordCircuitFailure();
        throw new ValidateException($lastError ? $lastError->getMessage() : 'AI服务调用失败');
    }

    protected function callDriver(string $driver, string $prompt, string $sessionId, array $bizParams, string $systemPrompt, ?int $maxTokens = null, ?int $timeout = null, array $overrides = []): array
    {
        if ($driver === 'bailian') {
            $options = [];
            if ($maxTokens !== null) {
                $options['max_tokens'] = $maxTokens;
            }
            if ($timeout !== null) {
                $options['timeout'] = $timeout;
            }
            $raw = $this->bailianClient->completion($prompt, $sessionId, $bizParams, $systemPrompt, $options, $overrides);
            return [
                'raw' => $raw,
                'text' => $this->bailianText($raw),
                'session_id' => (string)($raw['output']['session_id'] ?? $raw['session_id'] ?? ''),
            ];
        }
        if ($driver === 'deepseek') {
            $raw = $this->openAiClient->chat($driver, $prompt, $systemPrompt, $maxTokens, $timeout, $overrides);
            return [
                'raw' => $raw,
                'text' => $this->chatText($raw),
                'session_id' => '',
            ];
        }
        if ($driver === 'claude') {
            $raw = $this->claudeClient->messages($prompt, $systemPrompt, $maxTokens, $timeout, $overrides);
            return [
                'raw' => $raw,
                'text' => $this->claudeText($raw),
                'session_id' => '',
            ];
        }
        throw new ValidateException('不支持的AI驱动：' . $driver);
    }

    protected function bailianText(array $response): string
    {
        if (isset($response['output']['text'])) {
            return (string)$response['output']['text'];
        }
        if (isset($response['output']['finish_reason']) && isset($response['output']['choices'][0]['message']['content'])) {
            return (string)$response['output']['choices'][0]['message']['content'];
        }
        if (isset($response['text'])) {
            return (string)$response['text'];
        }
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    protected function chatText(array $response): string
    {
        if (isset($response['choices'][0]['message']['content'])) {
            return (string)$response['choices'][0]['message']['content'];
        }
        if (isset($response['output_text'])) {
            return (string)$response['output_text'];
        }
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    protected function claudeText(array $response): string
    {
        $parts = [];
        foreach ((array)($response['content'] ?? []) as $item) {
            if (is_array($item) && ($item['type'] ?? '') === 'text' && isset($item['text'])) {
                $parts[] = (string)$item['text'];
            }
        }
        if ($parts) {
            return implode("\n", $parts);
        }
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    protected function assertCircuitClosed(): void
    {
        $openKey = (string)config('huimaidan.ai.circuit_breaker.open_key', 'ai:llm:open_until');
        $openUntil = (int)Cache::get($openKey);
        if ($openUntil > time()) {
            throw new ValidateException('AI服务暂时熔断，请稍后再试');
        }
    }

    protected function resetCircuit(): void
    {
        Cache::delete((string)config('huimaidan.ai.circuit_breaker.fail_key', 'ai:llm:fails'));
        Cache::delete((string)config('huimaidan.ai.circuit_breaker.open_key', 'ai:llm:open_until'));
    }

    protected function recordCircuitFailure(): void
    {
        $failKey = (string)config('huimaidan.ai.circuit_breaker.fail_key', 'ai:llm:fails');
        $openKey = (string)config('huimaidan.ai.circuit_breaker.open_key', 'ai:llm:open_until');
        $threshold = max(1, $this->configInt('llm_fail_threshold', (int)config('huimaidan.ai.circuit_breaker.fail_threshold', 3)));
        $recoverySeconds = max(60, $this->configInt('llm_recovery_seconds', (int)config('huimaidan.ai.circuit_breaker.recovery_seconds', 900)));
        $fails = (int)Cache::get($failKey) + 1;
        Cache::set($failKey, $fails, $recoverySeconds);
        if ($fails >= $threshold) {
            Cache::set($openKey, time() + $recoverySeconds, $recoverySeconds);
        }
    }

    /**
     * 判断异常是否由超时引起，超时错误不再重试。
     */
    protected function isTimeoutError(\Throwable $e): bool
    {
        $message = mb_strtolower($e->getMessage());
        $timeoutKeywords = ['超时', 'timed out', 'timeout', 'curl error 28', 'operation timed out'];
        foreach ($timeoutKeywords as $keyword) {
            if (mb_strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function overrideConfig(array $overrides, string $key, string $default): string
    {
        $value = trim((string)($overrides[$key] ?? ''));
        if ($value === '' || $value === '******') {
            return $default;
        }
        return $value;
    }

    protected function configInt(string $key, int $default): int
    {
        try {
            return app()->make(AiConfigRepository::class)->int($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    protected function configString(string $key, string $default): string
    {
        try {
            return app()->make(AiConfigRepository::class)->text($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
