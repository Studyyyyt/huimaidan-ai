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

/**
 * 百炼 / 灵积 DashScope 客户端。
 *
 * 支持两种调用模式：
 * 1. 百炼应用 API：按 DashScope 应用文档使用 APP_ID、prompt、session_id 调用。
 * 2. 兼容模式 Chat Completions：用于已有 OpenAI 兼容链路。
 */
class BailianAppClient
{
    public function completion(string $prompt, string $sessionId = '', array $bizParams = [], string $systemPrompt = '', array $options = [], array $overrides = []): array
    {
        $config = config('huimaidan.ai.drivers.bailian', []);
        $mode = $this->configText('bailian_mode', (string)($config['mode'] ?? ''), $overrides);
        if ($mode === '') {
            $mode = 'app';
        }
        if ($mode === 'compatible') {
            return $this->chatCompletion($config, $prompt, $systemPrompt, $options, $overrides);
        }
        return $this->appCompletion($config, $prompt, $sessionId, $bizParams, $options, $overrides);
    }

    /**
     * 兼容模式 Chat Completions 调用。
     */
    protected function chatCompletion(array $config, string $prompt, string $systemPrompt, array $options = [], array $overrides = []): array
    {
        $apiKey = trim($this->configText('bailian_api_key', (string)($config['api_key'] ?? ''), $overrides));
        if ($apiKey === '') {
            throw new ValidateException('百炼 API Key 未配置');
        }

        $apiUrl = $this->configText('bailian_compatible_api_url', $this->configText('bailian_api_url', (string)($config['api_url'] ?? 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions'), $overrides), $overrides);
        $model = $this->configText('bailian_model', (string)($config['model'] ?? 'qwen-plus-latest'), $overrides);
        $timeout = isset($options['timeout']) && is_numeric($options['timeout'])
            ? max(1, (int)$options['timeout'])
            : $this->configInt('bailian_timeout', (int)($config['timeout'] ?? 15), $overrides);
        $maxTokens = isset($options['max_tokens']) && is_numeric($options['max_tokens'])
            ? max(1, (int)$options['max_tokens'])
            : $this->configInt('bailian_max_tokens', (int)($config['max_tokens'] ?? 512), $overrides);
        $temperature = $this->configNumber('bailian_temperature', (float)($config['temperature'] ?? 0.1), $overrides);
        if ($systemPrompt === '') {
            $systemPrompt = '你是惠买单本地生活推荐系统的结构化助手，只输出要求的 JSON，不要解释，不要编造商户、优惠或距离。';
        }

        $payload = json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ], JSON_UNESCAPED_UNICODE);

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ];

        $response = HttpService::request($apiUrl, 'post', $payload, $headers, $timeout);
        if ($response === false) {
            $status = HttpService::getStatus();
            $httpCode = is_array($status) ? (int)($status['http_code'] ?? 0) : 0;
            throw new ValidateException('百炼调用失败' . ($httpCode > 0 ? '，HTTP ' . $httpCode : ''));
        }
        $decoded = json_decode((string)$response, true);
        if (!is_array($decoded)) {
            throw new ValidateException('百炼响应格式错误');
        }
        if (!empty($decoded['error'])) {
            $message = is_array($decoded['error']) ? (string)($decoded['error']['message'] ?? '') : (string)$decoded['error'];
            throw new ValidateException($message ?: '百炼调用失败');
        }
        $text = (string)($decoded['choices'][0]['message']['content'] ?? '');
        return [
            'output' => ['text' => $text, 'choices' => [$decoded['choices'][0] ?? []]],
            'usage' => $decoded['usage'] ?? [],
        ];
    }

    /**
     * 百炼应用 API 调用（旧版）。
     */
    protected function appCompletion(array $config, string $prompt, string $sessionId, array $bizParams, array $options = [], array $overrides = []): array
    {
        $appId = trim($this->configText('bailian_app_id', (string)($config['app_id'] ?? ''), $overrides));
        $apiKey = trim($this->configText('bailian_api_key', (string)($config['api_key'] ?? ''), $overrides));
        if ($appId === '' || $apiKey === '') {
            throw new ValidateException('百炼应用未配置');
        }

        $apiUrl = $this->configText('bailian_app_api_url', (string)($config['api_url'] ?? 'https://dashscope.aliyuncs.com/api/v1/apps/{app_id}/completion'), $overrides);
        $url = str_replace('{app_id}', rawurlencode($appId), $apiUrl);
        $input = ['prompt' => $prompt];
        if ($sessionId !== '') {
            $input['session_id'] = $sessionId;
        }
        if ($bizParams) {
            $input['biz_params'] = $bizParams;
        }
        $payload = json_encode([
            'input' => $input,
            'parameters' => [
                'incremental_output' => false,
            ],
            'debug' => new \stdClass(),
        ], JSON_UNESCAPED_UNICODE);

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ];
        $workspace = trim($this->configText('bailian_workspace', (string)($config['workspace'] ?? ''), $overrides));
        if ($workspace !== '') {
            $headers[] = 'X-DashScope-WorkSpace: ' . $workspace;
        }

        $timeout = isset($options['timeout']) && is_numeric($options['timeout'])
            ? max(1, (int)$options['timeout'])
            : $this->configInt('bailian_timeout', (int)($config['timeout'] ?? 15), $overrides);
        $response = HttpService::request($url, 'post', $payload, $headers, $timeout);
        if ($response === false) {
            $status = HttpService::getStatus();
            $httpCode = is_array($status) ? (int)($status['http_code'] ?? 0) : 0;
            throw new ValidateException('百炼调用失败' . ($httpCode > 0 ? '，HTTP ' . $httpCode : ''));
        }
        $decoded = json_decode((string)$response, true);
        if (!is_array($decoded)) {
            throw new ValidateException('百炼响应格式错误');
        }
        $statusCode = (int)($decoded['status_code'] ?? 200);
        if ($statusCode !== 200 && $statusCode !== 0) {
            throw new ValidateException((string)($decoded['message'] ?? '百炼调用失败'));
        }
        return $decoded;
    }

    /**
     * 使用系统 curl 发起 HTTP 请求。
     * PHP 容器内的 curl 扩展可能未编译 HTTP/2 支持，因此退而使用系统 curl 二进制。
     */
    protected function request(string $url, string $payload, array $headers, int $timeout): string
    {
        $cmdParts = [
            'curl',
            '-s', // 静默
            '-S', // 显示错误
            '--max-time', (string)$timeout,
            '--connect-timeout', '5',
            '-X', 'POST',
            escapeshellarg($url),
        ];
        foreach ($headers as $header) {
            $cmdParts[] = '-H ' . escapeshellarg($header);
        }
        $cmdParts[] = '-d ' . escapeshellarg($payload);

        $command = implode(' ', $cmdParts) . ' 2>&1; echo "__BAILIAN_EXIT_CODE__:$?"';
        $output = (string)shell_exec($command);

        $pos = strrpos($output, '__BAILIAN_EXIT_CODE__:');
        if ($pos === false) {
            throw new ValidateException('百炼调用失败');
        }
        $exitCode = (int)trim(substr($output, $pos + 22));
        $body = trim(substr($output, 0, $pos));
        if ($exitCode !== 0 || $body === '') {
            throw new ValidateException('百炼调用失败' . ($body !== '' ? '：' . $body : ''));
        }
        return $body;
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
