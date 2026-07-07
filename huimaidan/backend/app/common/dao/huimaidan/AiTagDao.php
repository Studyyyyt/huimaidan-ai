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
use app\common\model\huimaidan\AiTag;

class AiTagDao extends BaseDao
{
    protected function getModel(): string
    {
        return AiTag::class;
    }

    public function search(array $where)
    {
        return AiTag::getDB()
            ->when(isset($where['tag_type']) && $where['tag_type'] !== '', function ($query) use ($where) {
                $query->where('tag_type', (string)$where['tag_type']);
            })
            ->when(isset($where['tag_value']) && $where['tag_value'] !== '', function ($query) use ($where) {
                $query->where('tag_value', (string)$where['tag_value']);
            })
            ->when(isset($where['keyword']) && trim((string)$where['keyword']) !== '', function ($query) use ($where) {
                $keyword = trim((string)$where['keyword']);
                $query->whereLike('tag_value|tag_label', '%' . $keyword . '%');
            })
            ->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
                $query->where('status', (int)$where['status']);
            })
            ->order('sort DESC,tag_id ASC');
    }
}
