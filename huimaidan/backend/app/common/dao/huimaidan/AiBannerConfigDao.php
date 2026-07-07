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
use app\common\model\huimaidan\AiBannerConfig;

class AiBannerConfigDao extends BaseDao
{
    protected function getModel(): string
    {
        return AiBannerConfig::class;
    }

    public function search(array $where)
    {
        return AiBannerConfig::getDB()
            ->when(isset($where['meal_type']) && $where['meal_type'] !== '', function ($query) use ($where) {
                $query->where('meal_type', (string)$where['meal_type']);
            })
            ->when(isset($where['is_enabled']) && $where['is_enabled'] !== '', function ($query) use ($where) {
                $query->where('is_enabled', (int)$where['is_enabled']);
            })
            ->order('sort DESC,config_id ASC');
    }
}
