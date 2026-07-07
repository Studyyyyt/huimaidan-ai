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

namespace app\common\dao\huimaidan;

use app\common\dao\BaseDao;
use app\common\model\huimaidan\MemberDiscount;

class MemberDiscountDao extends BaseDao
{
    protected function getModel(): string
    {
        return MemberDiscount::class;
    }

    public function byDiscountId(int $discountId)
    {
        return MemberDiscount::getDB()->where('discount_id', $discountId)->with(['level'])->order('member_level ASC');
    }

    public function deleteByDiscountId(int $discountId): int
    {
        return MemberDiscount::getDB()->where('discount_id', $discountId)->delete();
    }

    public function getByDiscountAndLevel(int $discountId, int $memberLevel)
    {
        return MemberDiscount::getDB()->where('discount_id', $discountId)
            ->where('member_level', $memberLevel)->where('status', 1)->with(['level'])->find();
    }
}
