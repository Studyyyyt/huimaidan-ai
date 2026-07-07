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
use app\common\model\huimaidan\AiConfig;

class AiConfigDao extends BaseDao
{
    protected function getModel(): string
    {
        return AiConfig::class;
    }

    public function search(array $where)
    {
        return AiConfig::getDB()
            ->when(isset($where['config_key']) && $where['config_key'] !== '', function ($query) use ($where) {
                $query->where('config_key', (string)$where['config_key']);
            })
            ->order('sort ASC,config_id ASC');
    }
}
