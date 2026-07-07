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

namespace crmeb\services\huimaidan;

use think\facade\Config;
use think\facade\Log;

/**
 * 三木森语音播报HTTP通信服务
 *
 * 通过三木森公有服务器HTTP接口推送消息，无需维护TCP长连接
 * 接口地址：http://cs.mqlinks.com/txmsgpush/
 *
 * 请求格式:
 * {
 *     "sbx_id": "设备SN",
 *     "agent_id": "{\"cmd\":\"voice\",\"money\":\"金额(分)\",\"msg\":\"播报文本\"}"
 * }
 */
class SanmusenVoiceService
{
    /**
     * 三木森API地址
     * @var string
     */
    protected $apiUrl = 'http://cs.mqlinks.com/txmsgpush/';

    /**
     * 请求超时时间（秒）
     * @var int
     */
    protected $timeout = 10;

    public function __construct()
    {
        $config = Config::get('huimaidan.voice', []);
        if (!empty($config['api_url'])) {
            $this->apiUrl = $config['api_url'];
        }
        if (!empty($config['timeout'])) {
            $this->timeout = (int)$config['timeout'];
        }
    }

    /**
     * 推送消息到设备
     *
     * @param array  $device  设备信息（只需 device_sn）
     * @param string $message 播报文本内容
     * @param float  $amount  播报金额（元），用于生成 money 字段
     * @return array ['success' => bool, 'error' => string, 'data' => array]
     */
    public function pushMessage(array $device, string $message, float $amount = 0): array
    {
        try {
            // money 字段单位为元
            $moneyStr = number_format($amount, 2, '.', '');

            // agent_id 是一个 JSON 字符串
            $agentId = json_encode([
                'cmd'   => 'voice',
                'money' => $moneyStr,
                'msg'   => $message,
            ], JSON_UNESCAPED_UNICODE);

            $payload = [
                'sbx_id'   => $device['device_sn'],
                'agent_id' => $agentId,
            ];

            Log::info('三木森推送请求: sn=' . $device['device_sn'] . ', msg=' . $message . ', money=' . $moneyStr);

            $response = $this->httpPost($payload);

            if ($response === false) {
                Log::error('三木森API请求失败: sn=' . ($device['device_sn'] ?? ''));
                return ['success' => false, 'error' => '请求三木森服务器失败'];
            }

            Log::info('三木森API响应: ' . $response);

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // 响应不是JSON，但HTTP 200，可能服务器只返回纯文本确认
                return ['success' => true, 'data' => ['raw' => $response]];
            }

            // 根据实际响应判断成功/失败
            if (isset($data['code']) && $data['code'] != 0) {
                return ['success' => false, 'error' => '推送失败: ' . ($data['msg'] ?? $response)];
            }

            return ['success' => true, 'data' => $data];
        } catch (\Throwable $e) {
            Log::error('三木森语音播报推送异常: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 发送HTTP POST请求
     *
     * @param array $payload 请求数据
     * @return string|false 响应内容，失败返回false
     */
    protected function httpPost(array $payload)
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            Log::error('三木森请求数据编码失败');
            return false;
        }

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            Log::error("三木森API请求失败: [{$errno}] {$error}");
            return false;
        }

        if ($httpCode !== 200) {
            Log::error("三木森API返回异常HTTP状态码: {$httpCode}");
            return false;
        }

        return $response;
    }

    /**
     * 测试设备连通性
     *
     * @param array $device 设备信息
     * @return array
     */
    public function testConnection(array $device): array
    {
        return $this->pushMessage($device, '测试播报，设备连接正常', 0.01);
    }
}
