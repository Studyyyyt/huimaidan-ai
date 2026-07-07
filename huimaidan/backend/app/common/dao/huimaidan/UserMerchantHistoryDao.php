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
use app\common\model\huimaidan\UserMerchantHistory;

class UserMerchantHistoryDao extends BaseDao
{
    protected function getModel(): string
    {
        return UserMerchantHistory::class;
    }

    public function search(array $where)
    {
        return UserMerchantHistory::getDB()
            ->when(isset($where['uid']) && $where['uid'] !== '', function ($query) use ($where) {
                $query->where('uid', (int)$where['uid']);
            })
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['mer_ids']) && $where['mer_ids'] !== '', function ($query) use ($where) {
                $query->whereIn('mer_id', array_map('intval', (array)$where['mer_ids']));
            })
            ->when(isset($where['history_ids']) && $where['history_ids'] !== '', function ($query) use ($where) {
                $query->whereIn('user_merchant_history_id', array_map('intval', (array)$where['history_ids']));
            });
    }

    public function createOrUpdate(int $uid, int $merId)
    {
        $now = date('Y-m-d H:i:s');
        $history = $this->search(['uid' => $uid, 'mer_id' => $merId])->find();
        if ($history) {
            $history->visit_count = (int)$history->visit_count + 1;
            $history->last_visit_time = $now;
            $history->update_time = $now;
            $history->save();
            return $history;
        }

        return $this->create([
            'uid' => $uid,
            'mer_id' => $merId,
            'visit_count' => 1,
            'last_visit_time' => $now,
            'create_time' => $now,
            'update_time' => $now,
        ]);
    }

    public function pruneOldest(int $uid, int $max): void
    {
        $ids = $this->search(['uid' => $uid])
            ->order('last_visit_time DESC,user_merchant_history_id DESC')
            ->column('user_merchant_history_id');
        if (count($ids) <= $max) {
            return;
        }
        $deleteIds = array_slice(array_map('intval', $ids), $max);
        if ($deleteIds) {
            UserMerchantHistory::getDB()->whereIn('user_merchant_history_id', $deleteIds)->delete();
        }
    }

    public function countByUidAndIds(int $uid, array $ids): int
    {
        if (!$ids) {
            return 0;
        }
        return $this->search(['uid' => $uid, 'history_ids' => $ids])->count();
    }

    public function deleteByUid(int $uid): void
    {
        UserMerchantHistory::getDB()->where('uid', $uid)->delete();
    }

    public function deleteByIdsForUid(int $uid, array $ids): void
    {
        if (!$ids) {
            return;
        }
        UserMerchantHistory::getDB()->where('uid', $uid)
            ->whereIn('user_merchant_history_id', array_map('intval', $ids))
            ->delete();
    }
}
