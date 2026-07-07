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

namespace app\validate\merchant;

use think\Validate;

class HuimaidanDiscountValidate extends Validate
{
    protected $failException = true;

    protected $rule = [
        'title|规则名称' => 'require|max:32',
        'rule_type|规则类型' => 'require|in:1,2,3',
        'platform_discount|平台折扣' => 'float|>:0|<=:1',
        'merchant_cost|商家底价折扣' => 'float|>:0|<=:1',
        'coupon_amount|立减金额' => 'float|>=:0',
        'point_ratio|积分抵扣比例' => 'float|>=:0|<=:1',
        'min_amount|最低消费金额' => 'float|>=:0',
        'pool_id|垫资池ID' => 'integer|>=:0',
        'status|状态' => 'in:0,1',
        'sort|排序' => 'integer',
    ];

    public function sceneUpdate()
    {
        return $this;
    }
}
