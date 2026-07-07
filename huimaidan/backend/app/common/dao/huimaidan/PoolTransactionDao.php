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
use app\common\model\huimaidan\PoolTransaction;

class PoolTransactionDao extends BaseDao
{
    protected function getModel(): string
    {
        return PoolTransaction::class;
    }

    public function search(array $where)
    {
        return PoolTransaction::getDB()
            ->when(isset($where['pool_id']) && $where['pool_id'] !== '', function ($query) use ($where) {
                $query->where('pool_id', (int)$where['pool_id']);
            })
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['type']) && $where['type'] !== '', function ($query) use ($where) {
                $query->where('type', (int)$where['type']);
            })
            ->when(isset($where['order_id']) && $where['order_id'] !== '', function ($query) use ($where) {
                $query->where('order_id', (int)$where['order_id']);
            })
            ->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
                getModelTime($query, $where['date'], 'create_time');
            })
            ->order('transaction_id DESC');
    }

    public function getByOrder(int $orderId, int $type)
    {
        return PoolTransaction::getDB()->where('order_id', $orderId)->where('type', $type)->find();
    }
}
