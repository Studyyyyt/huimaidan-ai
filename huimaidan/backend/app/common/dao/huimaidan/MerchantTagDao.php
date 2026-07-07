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
use app\common\model\huimaidan\MerchantTag;

class MerchantTagDao extends BaseDao
{
    protected function getModel(): string
    {
        return MerchantTag::class;
    }

    public function search(array $where)
    {
        return MerchantTag::getDB()
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['mer_ids']) && $where['mer_ids'] !== '', function ($query) use ($where) {
                $query->whereIn('mer_id', array_map('intval', (array)$where['mer_ids']));
            })
            ->when(isset($where['tag_type']) && $where['tag_type'] !== '', function ($query) use ($where) {
                $query->where('tag_type', (string)$where['tag_type']);
            })
            ->when(isset($where['tag_values']) && $where['tag_values'] !== '', function ($query) use ($where) {
                $query->whereIn('tag_value', (array)$where['tag_values']);
            })
            ->order('tag_weight DESC,tag_id ASC');
    }

    public function deleteAutoByMerId(int $merId): int
    {
        return MerchantTag::getDB()->where('mer_id', $merId)->where('is_auto', 1)->delete();
    }
}
