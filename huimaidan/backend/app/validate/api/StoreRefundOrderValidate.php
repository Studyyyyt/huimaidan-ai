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


namespace app\validate\api;


use think\Validate;

class StoreRefundOrderValidate extends Validate
{
    protected $failException = true;

    protected $rule = [
        'order_id|订单ID' => 'require|integer|>:0',
        'refund_message|退款原因' => 'require|max:128',
        'mark|备注' => 'max:128',
        'pics|凭证' => 'array|max:9',
    ];
}
