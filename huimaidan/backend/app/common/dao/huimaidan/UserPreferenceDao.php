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
use app\common\model\huimaidan\UserPreference;

class UserPreferenceDao extends BaseDao
{
    protected function getModel(): string
    {
        return UserPreference::class;
    }

    public function search(array $where)
    {
        return UserPreference::getDB()
            ->when(isset($where['uid']) && $where['uid'] !== '', function ($query) use ($where) {
                $query->where('uid', (int)$where['uid']);
            })
            ->when(isset($where['pref_type']) && $where['pref_type'] !== '', function ($query) use ($where) {
                $query->where('pref_type', (string)$where['pref_type']);
            })
            ->order('pref_score DESC,pref_id ASC');
    }
}
