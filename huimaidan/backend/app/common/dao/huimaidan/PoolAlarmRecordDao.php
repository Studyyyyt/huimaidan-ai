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
use app\common\model\huimaidan\PoolAlarmRecord;

class PoolAlarmRecordDao extends BaseDao
{
    protected function getModel(): string
    {
        return PoolAlarmRecord::class;
    }

    public function search(array $where)
    {
        return PoolAlarmRecord::getDB()
            ->when(isset($where['pool_id']) && $where['pool_id'] !== '', function ($query) use ($where) {
                $query->where('pool_id', (int)$where['pool_id']);
            })
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['notice_status']) && $where['notice_status'] !== '', function ($query) use ($where) {
                $query->where('notice_status', (int)$where['notice_status']);
            })
            ->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
                getModelTime($query, $where['date'], 'create_time');
            })
            ->with(['merchant'])
            ->order('alarm_record_id DESC');
    }
}
