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

namespace crmeb\listens\pay;

use app\common\repositories\huimaidan\OrderRepository;
use crmeb\interfaces\ListenerInterface;
use think\exception\ValidateException;

class HuimaidanOrderPaySuccessListen implements ListenerInterface
{
    public function handle($data): void
    {
        $orderSn = $data['order_sn'] ?? '';
        if (!$orderSn) {
            throw new ValidateException('惠买单支付回调缺少订单号');
        }
        app()->make(OrderRepository::class)->paySuccessByOrderSn($orderSn, $data['data'] ?? []);
    }
}
