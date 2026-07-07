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

namespace app\common\model\huimaidan;

use app\common\model\BaseModel;
use app\common\model\user\UserBrokerage;

class MemberDiscount extends BaseModel
{
    public static function tablePk(): ?string
    {
        return 'member_discount_id';
    }

    public static function tableName(): string
    {
        return 'huimaidan_member_discount';
    }

    public function level()
    {
        return $this->hasOne(UserBrokerage::class, 'brokerage_level', 'member_level')->where('type', 1);
    }
}
