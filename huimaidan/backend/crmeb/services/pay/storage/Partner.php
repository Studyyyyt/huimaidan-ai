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
namespace  crmeb\services\pay\storage;

use think\facade\Route;
use think\facade\Cache;
use EasyWeChat\Payment\Order;
use crmeb\services\pay\BasePay;
use crmeb\services\WechatService;
use crmeb\services\pay\PayInterface;
use EasyWeChat\Foundation\Application;
use think\exception\ValidateException;
use app\common\repositories\wechat\WechatUserRepository;

class Partner extends BasePay implements PayInterface
{
    const DIVER_TYPE_WEIXIN = '1';
    //routine
    const DIVER_TYPE_ROUTINE = '0';

    protected $application;
    protected $diver = '1';

    public function initialize($config)
    {
        $this->create();
    }

    protected function create()
    {
        $config = $this->getConfig();
        $this->application = new Application($config);
        $this->application->register(new \crmeb\services\easywechat\partner\ServiceProvider());
        $this->application->register(new \crmeb\services\easywechat\certficates\ServiceProvider());
    }

    public function setDiver($diver)
    {
        $this->diver = $diver;
    }

    public function getApplication()
    {
        return $this->application->partner;
    }

    public function getConfig()
    {
        $payment = systemConfig([
            'routine_appId',
            'wechat_appid',
            'site_url',
            //服务商支付配置
            'wechat_service_merid',
            'wechat_service_key',
            'wechat_service_v3key',
            'wechat_service_client_cert',
            'wechat_service_client_key',
            'wechat_service_serial_no',
            'wechat_service_public_id',
            'wechat_service_public_key',
            'wechat_service_name',
            'wechat_service_tool',  //0 微信平台收付通  1 微信服务商
        ]);

        $appid = $this->diver == self::DIVER_TYPE_WEIXIN ?
            $payment['wechat_appid'] :
            $payment['routine_appId'];

        $this->config['app_id'] = $appid;
        $this->config['service_payment'] = [
            'key' => trim($payment['wechat_service_key']),
            'apiv3_key' => trim($payment['wechat_service_v3key']),
            'serial_no' => trim($payment['wechat_service_serial_no']),
            'cert_path' => (app()->getRootPath() . 'public' . $payment['wechat_service_client_cert']),
            'key_path' => (app()->getRootPath() . 'public' . $payment['wechat_service_client_key']),
            'merchant_id' => trim($payment['wechat_service_merid']),
            'pay_weixin_client_cert' => $payment['wechat_service_client_cert'],
            'pay_weixin_client_key' => $payment['wechat_service_client_key'],
            'wechat_service_tool' => trim($payment['wechat_service_tool']),
            'pay_weixin_public_id' => trim($payment['wechat_service_public_id']),
            'pay_weixin_public_key' => trim($payment['wechat_service_public_key']),
            'wechat_service_name' => trim($payment['wechat_service_name']),
            'notify_url'  => $payment['site_url'] . Route::buildUrl('wechatCombinePayNotify')->build(),
        ];
        return $this->config;
    }

    /**
     *  添加分账方到特约商户的分账名单
     * @param string $subMchid
     * @param $name
     * @return array
     * @author Qinii、
     */
    public function receiversAdd(string $subMchid)
    {
        $config =  $this->getApplication()->receiversAdd($subMchid);
        return compact('config');
    }

    public function getRate($subMchid)
    {
        $config =  $this->getApplication()->getRate($subMchid);
        return compact('config');
    }

    public function pay($user, $type, $order)
    {
        switch ($type){
            case 'h5':
                $config =  $this->getApplication()->payH5($order, 'Wap');
                break;
            case 'weixin':
                $wechatUserRepository = app()->make(WechatUserRepository::class);
                $openId = $wechatUserRepository->idByOpenId($user['wechat_user_id']);
                if (!$openId)
                    throw new ValidateException('请关联微信公众号!');
                $order['openid'] = $openId;
                $config =  $this->getApplication()->payJsapi($order);
                break;
            case 'weixinQr':
                $res =  $this->getApplication()->payNative($order);
                $config = ['config' => $res['code_url'], 'time_expire' => time() + (15 * 60)];
                break;
            case 'weixinApp':
                $config =  $this->getApplication()->payApp($order);
                break;
            case 'weixinBarCode':
                $config = WechatService::create(null, true)->payWeixinBarCode(
                    $order['order_sn'],
                    $order['pay_price'],
                    $order['attach'],
                    $order['body'],
                    $order['authCode'],
                    $type,
                    $order['sub_mchid']
                );
                break;
            case 'routine':
                $wechatUserRepository = app()->make(WechatUserRepository::class);
                $openId = $wechatUserRepository->idByRoutineId($user['wechat_user_id']);
                if (!$openId)
                    throw new ValidateException('请关联微信小程序!');
                $order['openid'] = $openId;

                $this->setDiver(self::DIVER_TYPE_ROUTINE);
                $this->create();
                $config = $this->getApplication()->payJsapi($order);
                break;
            default:
                throw new ValidateException('不存在的支付方式');
                break;
        }
        return compact('config');
    }

    public function profitsharingStatus($options)
    {
        return $this->getApplication()->profitsharingStatus($options);
    }

    public function profitsharing($order)
    {
        $config = $this->getApplication()->profitsharing($order);
        return compact('config');
    }

    public function payOrderRefund(string $outTradeNo, array $options = [])
    {
        $config = $this->getApplication()->refund($outTradeNo, $options);
        return compact('config');
    }

    public function profitsharingOrder($order, $finish)
    {
        $config = $this->getApplication()->profitsharingOrder($order,$finish);
        return compact('config');
    }

    public function profitsharingFinishOrder($order)
    {
        $config = $this->getApplication()->profitsharingFinishOrder($order);
        return compact('config');
    }

    public function refund(string $outTradeNo, array $options = [])
    {
        $config = $this->getApplication()->refund($outTradeNo,$options);
    }

    public function handleNotify()
    {

    }

}
