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

class HuimaidanOrderValidate extends Validate
{
    protected $failException = true;

    protected $rule = [
        'mer_id|商户ID' => 'require|integer|>:0',
        'amount|消费金额' => 'require|float|>:0|regex:/^\d+(\.\d{1,2})?$/',
        'pay_type|支付方式' => 'require|in:balance,weixin,routine,h5,alipay,alipayQr,weixinQr',
        'mark|备注' => 'max:128',
        'couponId|优惠券' => 'integer|>:0',
        'coupon_id|优惠券' => 'integer|>:0',
        'discount_amount|优惠金额' => 'require|float|>=:0|regex:/^\d+(\.\d{1,2})?$/',
        'no_discount_amount|不参与优惠金额' => 'require|float|>=:0|regex:/^\d+(\.\d{1,2})?$/',
    ];

    protected $message = [
        'amount.regex' => '消费金额最多支持两位小数',
        'discount_amount.regex' => '优惠金额最多支持两位小数',
        'no_discount_amount.regex' => '不参与优惠金额最多支持两位小数',
    ];

    public function sceneCalculate()
    {
        return $this->only(['mer_id', 'amount']);
    }

    public function scenePrepare()
    {
        return $this->only(['mer_id', 'amount', 'mark']);
    }

    public function sceneCombined()
    {
        return $this->only(['mer_id', 'discount_amount', 'no_discount_amount', 'pay_type', 'mark']);
    }
}
