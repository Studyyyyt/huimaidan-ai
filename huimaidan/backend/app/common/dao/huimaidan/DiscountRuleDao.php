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
use app\common\model\huimaidan\DiscountRule;

class DiscountRuleDao extends BaseDao
{
    protected function getModel(): string
    {
        return DiscountRule::class;
    }

    public function search(array $where)
    {
        return DiscountRule::getDB()
            ->where('is_del', $where['is_del'] ?? 0)
            ->when(isset($where['rule_id']) && $where['rule_id'] !== '', function ($query) use ($where) {
                $query->where('rule_id', (int)$where['rule_id']);
            })
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
                $query->where('status', (int)$where['status']);
            })
            ->when(isset($where['rule_type']) && $where['rule_type'] !== '', function ($query) use ($where) {
                $query->where('rule_type', (int)$where['rule_type']);
            })
            ->when(isset($where['amount']) && $where['amount'] !== '', function ($query) use ($where) {
                $query->where('min_amount', '<=', $where['amount']);
            })
            ->when(isset($where['active_at']) && $where['active_at'] !== '', function ($query) use ($where) {
                $time = $where['active_at'];
                $query->where(function ($query) use ($time) {
                    $query->whereNull('start_time')->whereOr('start_time', '<=', $time);
                })->where(function ($query) use ($time) {
                    $query->whereNull('end_time')->whereOr('end_time', '>=', $time);
                });
            })
            ->with(['pool'])
            ->order('sort DESC,rule_id DESC');
    }

    public function merHas(int $merId, int $ruleId, ?int $isDel = 0)
    {
        return DiscountRule::getDB()->where('rule_id', $ruleId)->where('mer_id', $merId)
            ->when(!is_null($isDel), function ($query) use ($isDel) {
                $query->where('is_del', $isDel);
            })->count('rule_id') > 0;
    }
}
