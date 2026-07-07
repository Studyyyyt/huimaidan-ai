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


use crmeb\services\easywechat\pay\SandboxApi;
use EasyWeChat\Payment\Notify;
use EasyWeChat\Payment\Payment;
use Symfony\Component\HttpFoundation\Request;

class PaymentService extends Payment
{
    public function getNotify()
    {
        $request = \request();
        $request = new Request($request->get(), $request->post(), [], [], [], $request->server(), $request->getContent());
        return new Notify($this->merchant, $request);
    }

    public function getAPI()
    {
        if ($this->api) {
            return $this->api;
        }

        if ($this->merchant->get('sandbox_mode')) {
            $this->api = new SandboxApi($this->merchant, $this->cache);
            $this->api->sandboxMode(true);
            return $this->api;
        }

        return parent::getAPI();
    }
}
