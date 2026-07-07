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


namespace crmeb\services\easywechat\certficates;


use crmeb\exceptions\WechatException;
use crmeb\services\easywechat\BaseClient;
use EasyWeChat\Core\AbstractAPI;
use think\exception\InvalidArgumentException;
use think\facade\Cache;

class Client extends BaseClient
{
    public function get()
    {
        $is = $this->isService ? 'service_payment':'payment';
        //如果是自动分账 或者 普通支付但是用的是v3支付 获取公钥
        if ($this->app['config']['type'] == 'wechat') {
            $public_key = $this->app['config'][$is]['pay_weixin_public_key'];
        } else {
            $public_key = $this->app['config'][$is]['pay_routine_public_key'];
        }
        if ($public_key && ($this->isService || $this->app['config']['is_v3'])) {
            $certficates = [
                'serial_no' => $this->app['config'][$is]['pay_routine_public_key'],
                'certificates' => $this->app['config'][$is]['pay_routine_public_id']
            ];
            //如果获取公钥成功则返回
            if ($certficates['serial_no'] && $certficates['certificates']) {
                return  $certficates;
            }
        }
        // v2支付，或者是未获取到公钥，则平台证书获取操作
        $driver = Cache::store('file');
        $cacheKey = '_wx_v3' . $this->app['config'][$is]['serial_no'];
        if ($driver->has($cacheKey)) {
            return $driver->get($cacheKey);
        }
        $certficates = $this->getCertficates();
        $driver->set($cacheKey, $certficates, 3600 * 24 * 30);

        return $certficates;
    }

    /**
     * get certficates.
     *
     * @return array
     */
    public function getCertficates()
    {
        $response = $this->request('/v3/certificates', 'GET', [], false);
        if (isset($response['code']))  throw new WechatException($response['message']);
        $certificates = $response['data'][0];
        $certificates['certificates'] = $this->decrypt($certificates['encrypt_certificate']);
        unset($certificates['encrypt_certificate']);
        return $certificates;
    }
}
