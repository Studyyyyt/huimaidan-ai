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
namespace crmeb\services;

use think\exception\ValidateException;

class PayStatusService
{
    protected $type;
    protected $options;

    public function __construct(string $type, array $options)
    {
        $this->type = $type;
        $this->options = $options;
    }

    public function query()
    {
        $method = 'query' . ucfirst($this->type);
        if (!method_exists($this, $method)) {
            throw new ValidateException('不支持该支付方式');
        }
        return $this->{$method}();
    }
    /**
     * 微信扫码支付查询订单状态
     *
     * @return void
     */
    protected function queryWeixinBarCode() : array
    {
        return $this->queryWechatResult(WechatService::create()->query($this->options['order_sn']));
    }
    /**
     * 支付宝扫码支付查询订单状态
     *
     * @return void
     */
    protected function queryAlipayBarCode()
    {
        $res = AlipayService::create()->query($this->options['order_sn']);
        if($res['code'] == '10000' && $res['msg'] == 'Success' && in_array($res['trade_status'],['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return ['transaction_id' => $res['trade_no']];
        }

        return [];
    }

    protected function queryRoutine() : array
    {
        return $this->queryWechatResult(MiniProgramService::create()->query($this->options['order_sn']));
    }

    protected function queryWechat() : array
    {
        return $this->queryWechatResult(WechatService::create()->query($this->options['order_sn']));
    }

    protected function queryH5() : array
    {
        return $this->queryWechat();
    }

    protected function queryWechatQr() : array
    {
        return $this->queryWechat();
    }

    protected function queryWechatResult($res) : array
    {
        if($res->return_code == 'SUCCESS' && $res->result_code == 'SUCCESS' && $res->trade_state == 'SUCCESS') {
            return ['transaction_id' => $res->transaction_id];
        }

        return [];
    }
}
