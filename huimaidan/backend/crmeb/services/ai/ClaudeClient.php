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

class ClaudeClient
{
    public function messages(string $prompt, string $systemPrompt = '', ?int $maxTokens = null, ?int $timeout = null, array $overrides = []): array
    {
        $config = config('huimaidan.ai.drivers.claude', []);
        $apiKey = trim($this->configText('claude_api_key', (string)($config['api_key'] ?? ''), $overrides));
        $apiUrl = trim($this->configText('claude_api_url', (string)($config['api_url'] ?? 'https://api.anthropic.com/v1/messages'), $overrides));
        $model = trim($this->configText('claude_model', (string)($config['model'] ?? 'claude-3-5-sonnet-latest'), $overrides));
        if ($apiKey === '' || $apiUrl === '' || $model === '') {
            throw new ValidateException('claude模型未配置');
        }

        if ($systemPrompt === '') {
            $systemPrompt = '你是惠买单本地生活推荐系统的结构化意图理解模块。';
        }

        $payload = json_encode([
            'model' => $model,
            'max_tokens' => $maxTokens === null ? $this->configInt('claude_max_tokens', 1024, $overrides) : max(1, $maxTokens),
            'temperature' => $this->configNumber('claude_temperature', 0.1, $overrides),
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $response = HttpService::request($apiUrl, 'post', $payload, [
            'x-api-key: ' . $apiKey,
            'anthropic-version: ' . $this->configText('claude_version', (string)($config['version'] ?? '2023-06-01'), $overrides),
            'Content-Type: application/json',
        ], $timeout === null ? $this->configInt('claude_timeout', (int)($config['timeout'] ?? 15), $overrides) : max(1, $timeout));
        if ($response === false) {
            $status = HttpService::getStatus();
            $httpCode = is_array($status) ? (int)($status['http_code'] ?? 0) : 0;
            throw new ValidateException('claude调用失败' . ($httpCode > 0 ? '，HTTP ' . $httpCode : ''));
        }
        $decoded = json_decode((string)$response, true);
        if (!is_array($decoded)) {
            throw new ValidateException('claude响应格式错误');
        }
        if (!empty($decoded['error'])) {
            $message = is_array($decoded['error']) ? (string)($decoded['error']['message'] ?? '') : (string)$decoded['error'];
            throw new ValidateException($message ?: 'claude调用失败');
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
