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

namespace crmeb\services\easywechat\pay;

use EasyWeChat\Payment\API;
use EasyWeChat\Payment\Merchant;
use EasyWeChat\Support\XML;
use crmeb\services\HttpService;
use think\exception\ValidateException;
use think\facade\Cache;

class SandboxApi extends API
{
    protected const SANDBOX_SIGNKEY_URL = 'https://api.mch.weixin.qq.com/xdc/apiv2getsignkey/sign/getsignkey';
    protected const SANDBOX_CACHE_PREFIX = 'wechat_routine_sandbox_signkey_';

    protected function wrapApi($resource)
    {
        if (!$this->sandboxEnabled) {
            return parent::wrapApi($resource);
        }

        $baseUrl = rtrim((string)$this->merchant->get('sandbox_base_url', ''), '/');
        if ($baseUrl === '') {
            $baseUrl = 'https://api.mch.weixin.qq.com/xdc/apiv2sandbox';
        }

        return $baseUrl . $resource;
    }

    public function getSignkey($api)
    {
        if (!$this->sandboxEnabled) {
            return parent::getSignkey($api);
        }

        return self::resolveSandboxSignKey($this->merchant);
    }

    public static function resolveSandboxSignKey(Merchant $merchant): string
    {
        $manualKey = trim((string)$merchant->get('sandbox_sign_key', ''));
        if ($manualKey !== '') {
            return $manualKey;
        }

        $merchantId = trim((string)$merchant->merchant_id);
        if ($merchantId === '') {
            throw new ValidateException('缺少小程序支付商户号，无法获取沙箱验签密钥');
        }

        $cacheKey = self::SANDBOX_CACHE_PREFIX . $merchantId;
        $cached = trim((string)Cache::get($cacheKey, ''));
        if ($cached !== '') {
            return $cached;
        }

        $originKey = trim((string)$merchant->get('origin_key', ''));
        if ($originKey === '') {
            throw new ValidateException('缺少小程序支付正式密钥，无法自动获取沙箱验签密钥');
        }

        $nonceStr = md5($merchantId . microtime(true) . mt_rand());
        $params = [
            'mch_id' => $merchantId,
            'nonce_str' => $nonceStr,
        ];
        $params['sign'] = self::sign($params, $originKey);

        $response = HttpService::request(
            self::SANDBOX_SIGNKEY_URL,
            'post',
            XML::build($params),
            ['Content-Type: text/xml; charset=utf-8'],
            15
        );

        if ($response === false) {
            throw new ValidateException('自动获取微信支付沙箱验签密钥失败');
        }

        $result = XML::parse($response);
        if (!is_array($result)) {
            throw new ValidateException('微信支付沙箱验签密钥返回异常');
        }

        if (($result['return_code'] ?? '') !== 'SUCCESS') {
            throw new ValidateException('微信支付沙箱验签密钥获取失败：' . ($result['return_msg'] ?? '未知错误'));
        }

        $sandboxKey = trim((string)($result['sandbox_signkey'] ?? ''));
        if ($sandboxKey === '') {
            throw new ValidateException('微信支付沙箱验签密钥为空');
        }

        Cache::set($cacheKey, $sandboxKey, 23 * 3600);

        return $sandboxKey;
    }

    protected static function sign(array $params, string $key): string
    {
        ksort($params);
        $pairs = [];
        foreach ($params as $name => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            $pairs[] = $name . '=' . $value;
        }

        return strtoupper(md5(implode('&', $pairs) . '&key=' . $key));
    }
}
