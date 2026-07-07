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
use app\common\model\huimaidan\AiRecommendLog;

class AiRecommendLogDao extends BaseDao
{
    protected function getModel(): string
    {
        return AiRecommendLog::class;
    }

    public function search(array $where)
    {
        return AiRecommendLog::getDB()
            ->when(isset($where['uid']) && $where['uid'] !== '', function ($query) use ($where) {
                $query->where('uid', (int)$where['uid']);
            })
            ->when(isset($where['session_id']) && $where['session_id'] !== '', function ($query) use ($where) {
                $query->where('session_id', (string)$where['session_id']);
            })
            ->when(isset($where['keyword']) && $where['keyword'] !== '', function ($query) use ($where) {
                $query->whereLike('query_text', '%' . trim((string)$where['keyword']) . '%');
            })
            ->when(isset($where['degraded']) && $where['degraded'] !== '', function ($query) use ($where) {
                $query->where('degraded', (int)$where['degraded']);
            })
            ->when(isset($where['date']) && is_array($where['date']) && count($where['date']) === 2, function ($query) use ($where) {
                $query->whereBetweenTime('create_time', $where['date'][0], $where['date'][1]);
            })
            ->order('log_id DESC');
    }
}
