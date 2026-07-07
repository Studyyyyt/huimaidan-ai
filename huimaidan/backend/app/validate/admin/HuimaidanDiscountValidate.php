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

namespace app\validate\admin;

use think\Validate;

class HuimaidanDiscountValidate extends Validate
{
    protected $failException = true;

    protected $rule = [
        'mer_id|商户ID' => 'require|integer|>:0',
        'merchant_discount|商家结算折扣' => 'require|float|>:0|<=:1',
        'status|状态' => 'in:0,1',
        'sort|排序' => 'integer',
        'member_discounts|会员折扣配置' => 'require|array',
        'huimaidan_discount_stack_enabled|优惠叠加开关' => 'in:0,1',
    ];

    public function sceneUpdate()
    {
        return $this->remove('mer_id', 'require')
            ->remove('pool_id', 'require')
            ->remove('merchant_discount', 'require')
            ->remove('member_discounts', 'require');
    }
}
