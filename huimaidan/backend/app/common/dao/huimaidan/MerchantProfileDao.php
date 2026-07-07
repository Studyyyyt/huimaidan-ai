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
use app\common\model\huimaidan\MerchantProfile;

class MerchantProfileDao extends BaseDao
{
    protected function getModel(): string
    {
        return MerchantProfile::class;
    }

    public function search(array $where)
    {
        return MerchantProfile::getDB()
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['mer_ids']) && $where['mer_ids'] !== '', function ($query) use ($where) {
                $query->whereIn('mer_id', (array)$where['mer_ids']);
            })
            ->order('profile_id DESC');
    }

    public function getByMerId(int $merId)
    {
        return MerchantProfile::getDB()->where('mer_id', $merId)->find();
    }
}
