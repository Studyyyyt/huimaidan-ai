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

class HuimaidanPoolValidate extends Validate
{
    protected $failException = true;

    protected $rule = [
        'mer_id|商户ID' => 'require|integer|>:0',
        'amount|金额' => 'require|float|>:0',
        'remark|备注' => 'max:128',
        'alarm_balance|预警金额' => 'require|float|>=:0',
        'alarm_enabled|预警开关' => 'require|in:0,1',
        'status|状态' => 'in:0,1',
        'pool_ids|垫资池' => 'require|array',
    ];
}
