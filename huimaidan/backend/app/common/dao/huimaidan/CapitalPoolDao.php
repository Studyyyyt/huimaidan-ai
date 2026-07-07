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
use app\common\model\huimaidan\CapitalPool;

class CapitalPoolDao extends BaseDao
{
    protected function getModel(): string
    {
        return CapitalPool::class;
    }

    public function search(array $where)
    {
        return CapitalPool::getDB()
            ->when(isset($where['pool_id']) && $where['pool_id'] !== '', function ($query) use ($where) {
                $query->where('pool_id', (int)$where['pool_id']);
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
            ->when(isset($where['alarm_enabled']) && $where['alarm_enabled'] !== '', function ($query) use ($where) {
                $query->where('alarm_enabled', (int)$where['alarm_enabled']);
            })
            ->when(isset($where['balance_lte']) && $where['balance_lte'] !== '', function ($query) use ($where) {
                $query->where('balance', '<=', $where['balance_lte']);
            })
            ->when(isset($where['balance_gte']) && $where['balance_gte'] !== '', function ($query) use ($where) {
                $query->where('balance', '>=', $where['balance_gte']);
            })
            ->when(isset($where['alarm_status']) && $where['alarm_status'] !== '', function ($query) use ($where) {
                if ((int)$where['alarm_status'] === 1) {
                    $query->where('alarm_enabled', 1)->whereColumn('balance', '<=', 'alarm_balance');
                } else {
                    $query->whereRaw('(alarm_enabled = 0 OR balance > alarm_balance)');
                }
            })
            ->with(['merchant'])
            ->order($this->orderBy($where));
    }

    public function getByMerId(int $merId)
    {
        return CapitalPool::getDB()->where('mer_id', $merId)->find();
    }

    public function lockById(int $poolId)
    {
        return CapitalPool::getDB()->where('pool_id', $poolId)->lock(true)->find();
    }

    public function existingIds(array $poolIds): array
    {
        return CapitalPool::getDB()->whereIn('pool_id', $poolIds)->column('pool_id');
    }

    protected function orderBy(array $where): string
    {
        $field = in_array($where['order_field'] ?? '', ['balance', 'total_consume', 'update_time'], true)
            ? $where['order_field']
            : 'pool_id';
        $direction = strtolower((string)($where['order_direction'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        return $field . ' ' . $direction;
    }
}
