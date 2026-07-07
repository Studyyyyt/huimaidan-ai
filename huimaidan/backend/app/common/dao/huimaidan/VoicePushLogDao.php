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
use app\common\model\huimaidan\VoicePushLog;

class VoicePushLogDao extends BaseDao
{
    protected function getModel(): string
    {
        return VoicePushLog::class;
    }

    /**
     * 搜索条件构建
     */
    public function search(array $where)
    {
        return VoicePushLog::getDB()
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['device_id']) && $where['device_id'] !== '', function ($query) use ($where) {
                $query->where('device_id', (int)$where['device_id']);
            })
            ->when(isset($where['order_sn']) && $where['order_sn'] !== '', function ($query) use ($where) {
                $query->where('order_sn', $where['order_sn']);
            })
            ->when(isset($where['push_status']) && $where['push_status'] !== '', function ($query) use ($where) {
                $query->where('push_status', (int)$where['push_status']);
            })
            ->when(isset($where['push_type']) && $where['push_type'] !== '', function ($query) use ($where) {
                $query->where('push_type', (int)$where['push_type']);
            })
            ->when(isset($where['start_time']) && $where['start_time'] !== '', function ($query) use ($where) {
                $query->where('create_time', '>=', (int)$where['start_time']);
            })
            ->when(isset($where['end_time']) && $where['end_time'] !== '', function ($query) use ($where) {
                $query->where('create_time', '<=', (int)$where['end_time']);
            })
            ->order('id DESC');
    }

    /**
     * 获取待推送的日志
     */
    public function getPendingLogs(int $limit = 10): array
    {
        return VoicePushLog::getDB()
            ->where('push_status', VoicePushLog::PUSH_STATUS_PENDING)
            ->order('id ASC')
            ->limit($limit)
            ->select()
            ->toArray();
    }
}
