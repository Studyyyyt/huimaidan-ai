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
use app\common\model\huimaidan\MerchantDiscount;

class MerchantDiscountDao extends BaseDao
{
    protected function getModel(): string
    {
        return MerchantDiscount::class;
    }

    public function search(array $where)
    {
        return MerchantDiscount::getDB()
            ->when(isset($where['discount_id']) && $where['discount_id'] !== '', function ($query) use ($where) {
                $query->where('discount_id', (int)$where['discount_id']);
            })
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['mer_ids']) && $where['mer_ids'] !== '', function ($query) use ($where) {
                $query->whereIn('mer_id', (array)$where['mer_ids']);
            })
            ->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
                $query->where('status', (int)$where['status']);
            })
            ->when(isset($where['active_at']) && $where['active_at'] !== '', function ($query) use ($where) {
                $time = $where['active_at'];
                $query->where(function ($query) use ($time) {
                    $query->whereNull('start_time')->whereOr('start_time', '<=', $time);
                })->where(function ($query) use ($time) {
                    $query->whereNull('end_time')->whereOr('end_time', '>=', $time);
                });
            })
            ->with(['merchant', 'pool', 'memberDiscounts.level'])
            ->order('sort DESC,discount_id DESC');
    }
}
