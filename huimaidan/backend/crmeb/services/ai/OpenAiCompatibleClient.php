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
use crmeb\services\HttpService;
use think\exception\ValidateException;

class OpenAiCompatibleClient
{
    public function chat(string $driver, string $prompt, string $systemPrompt = '', ?int $maxTokens = null, ?int $timeout = null, array $overrides = []): array
    {
        $config = config('huimaidan.ai.drivers.' . $driver, []);
        $apiKey = trim($this->configText($driver . '_api_key', (string)($config['api_key'] ?? ''), $overrides));
        $apiUrl = trim($this->configText($driver . '_api_url', (string)($config['api_url'] ?? ''), $overrides));
        $model = trim($this->configText($driver . '_model', (string)($config['model'] ?? ''), $overrides));
        if ($apiKey === '' || $apiUrl === '' || $model === '') {
            throw new ValidateException($driver . '模型未配置');
        }

        if ($driver === 'deepseek') {
            $apiUrl = $this->normalizeDeepSeekApiUrl($apiUrl);
        }

        if ($systemPrompt === '') {
            $systemPrompt = '你是惠买单本地生活推荐系统的结构化意图理解模块。';
        }

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'stream' => false,
        ];

        $temperature = $this->configText($driver . '_temperature', '', $overrides);
        if ($temperature !== '' && is_numeric($temperature)) {
            $payload['temperature'] = (float)$temperature;
        }
        if ($maxTokens !== null) {
            $payload['max_tokens'] = max(1, $maxTokens);
        } else {
            $configuredMaxTokens = $this->configText($driver . '_max_tokens', '', $overrides);
            if ($configuredMaxTokens !== '' && is_numeric($configuredMaxTokens)) {
                $payload['max_tokens'] = max(1, (int)$configuredMaxTokens);
            }
        }
        if ($driver === 'deepseek') {
            $thinkingType = $this->configText('deepseek_thinking_type', 'disabled', $overrides);
            if ($thinkingType === 'enabled') {
                $payload['thinking'] = ['type' => 'enabled'];
                $reasoningEffort = $this->configText('deepseek_reasoning_effort', 'high', $overrides);
                if (in_array($reasoningEffort, ['low', 'medium', 'high'], true)) {
                    $payload['reasoning_effort'] = $reasoningEffort;
                }
            }
        }

        // 允许调用方通过 overrides 强制指定 response_format（如 json_object），提升结构化输出稳定性
        if (!empty($overrides['response_format']) && is_array($overrides['response_format'])) {
            $payload['response_format'] = $overrides['response_format'];
        }

        $payload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $response = HttpService::request($apiUrl, 'post', $payload, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ], $timeout === null ? $this->configInt($driver . '_timeout', (int)($config['timeout'] ?? 15), $overrides) : max(1, $timeout), true);
        if ($response === false) {
            $status = HttpService::getStatus();
            $httpCode = is_array($status) ? (int)($status['http_code'] ?? 0) : 0;
            $curlError = HttpService::getCurlError();
            $parts = [];
            if ($httpCode > 0) {
                $parts[] = 'HTTP ' . $httpCode;
            }
            if ($curlError !== '') {
                $parts[] = $curlError;
            }
            throw new ValidateException($driver . '调用失败' . ($parts ? '，' . implode('，', $parts) : ''));
        }
        $decoded = json_decode((string)$response, true);
        if (!is_array($decoded)) {
            throw new ValidateException($driver . '响应格式错误：' . mb_substr((string)$response, 0, 500));
        }
        if (!empty($decoded['error'])) {
            $message = is_array($decoded['error']) ? (string)($decoded['error']['message'] ?? '') : (string)$decoded['error'];
            $code = is_array($decoded['error']) ? (string)($decoded['error']['code'] ?? '') : '';
            throw new ValidateException($message ?: ($code ? $driver . '调用失败：' . $code : $driver . '调用失败'));
        }
        $choices = $decoded['choices'] ?? [];
        if (!is_array($choices) || !isset($choices[0])) {
            throw new ValidateException($driver . '响应缺少 choices');
        }
        $content = (string)($choices[0]['message']['content'] ?? '');
        if ($content === '') {
            throw new ValidateException($driver . '响应内容为空');
        }
        return $decoded;
    }

    protected function configText(string $key, string $default, array $overrides = []): string
    {
        $value = trim((string)($overrides[$key] ?? ''));
        if ($value !== '' && $value !== '******') {
            return $value;
        }
        try {
            return app()->make(AiConfigRepository::class)->text($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * 兼容用户只填写 DeepSeek base URL（https://api.deepseek.com）的情况，自动补全 chat completions 路径。
     */
    protected function normalizeDeepSeekApiUrl(string $apiUrl): string
    {
        $apiUrl = rtrim($apiUrl, '/');
        if ($apiUrl === '') {
            return $apiUrl;
        }
        $suffix = '/chat/completions';
        if (substr($apiUrl, -strlen($suffix)) === $suffix) {
            return $apiUrl;
        }
        return $apiUrl . $suffix;
    }

    protected function configInt(string $key, int $default, array $overrides = []): int
    {
        $value = trim((string)($overrides[$key] ?? ''));
        if ($value !== '' && is_numeric($value)) {
            return max(1, (int)$value);
        }
        try {
            return max(1, app()->make(AiConfigRepository::class)->int($key, $default));
        } catch (\Throwable $e) {
            return max(1, $default);
        }
    }

    protected function configNumber(string $key, float $default, array $overrides = []): float
    {
        $value = trim((string)($overrides[$key] ?? ''));
        if ($value !== '' && is_numeric($value)) {
            return (float)$value;
        }
        try {
            return app()->make(AiConfigRepository::class)->number($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
