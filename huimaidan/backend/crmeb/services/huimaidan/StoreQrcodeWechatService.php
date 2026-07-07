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

use crmeb\services\MiniProgramService;
use think\exception\ValidateException;

class StoreQrcodeWechatService
{
    const API_GET_UNLIMITED = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';

    public function getUnlimited(string $scene, string $page, array $options = []): string
    {
        $page = ltrim(trim($page), '/');
        if ($page === '' || strpos($page, '?') !== false) {
            throw new ValidateException('小程序页面路径错误');
        }
        $accessToken = MiniProgramService::create()->miniProgram()->access_token->getToken();
        if (!$accessToken) {
            throw new ValidateException('微信 access_token 获取失败');
        }

        $payload = [
            'scene' => $scene,
            'page' => $page,
            'check_path' => array_key_exists('check_path', $options) ? (bool)$options['check_path'] : true,
            'env_version' => (string)($options['env_version'] ?? 'release'),
            'width' => (int)($options['width'] ?? 430),
        ];

        $response = $this->postJson(
            self::API_GET_UNLIMITED . '?access_token=' . $accessToken,
            $payload,
            (int)($options['timeout'] ?? 15)
        );

        return $this->resolveBinaryResponse($response);
    }

    protected function postJson(string $url, array $payload, int $timeout)
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            throw new ValidateException('微信小程序码请求参数编码失败');
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curl);
        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false || $httpCode !== 200) {
            if (is_string($response) && trim($response) !== '') {
                return $response;
            }
            throw new ValidateException($error ? '调用微信小程序码接口失败：' . $error : '调用微信小程序码接口失败');
        }

        return $response;
    }

    public function resolveBinaryResponse($response): string
    {
        if ($response === false || $response === null || $response === '') {
            throw new ValidateException('调用微信小程序码接口失败');
        }

        $trimmed = ltrim((string)$response);
        if (strpos($trimmed, '{') === 0) {
            $data = json_decode($trimmed, true);
            if (is_array($data)) {
                $errcode = (int)($data['errcode'] ?? -1);
                $errmsg = (string)($data['errmsg'] ?? '微信小程序码接口返回异常');
                throw new ValidateException('微信小程序码生成失败：' . $errmsg . '（' . $errcode . '）');
            }
        }

        if (@getimagesizefromstring($response) === false) {
            throw new ValidateException('微信小程序码接口返回异常');
        }

        return (string)$response;
    }
}
