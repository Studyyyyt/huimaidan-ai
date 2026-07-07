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
use app\common\model\huimaidan\StoreQrcode;
use app\common\model\system\merchant\Merchant;

class StoreQrcodeDao extends BaseDao
{
    protected function getModel(): string
    {
        return StoreQrcode::class;
    }

    public function search(array $where)
    {
        return StoreQrcode::getDB()
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['scene_type']) && $where['scene_type'] !== '', function ($query) use ($where) {
                $query->where('scene_type', (string)$where['scene_type']);
            })
            ->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
                $query->where('status', (int)$where['status']);
            })
            ->when(isset($where['last_generate_status']) && $where['last_generate_status'] !== '', function ($query) use ($where) {
                $query->where('last_generate_status', (int)$where['last_generate_status']);
            })
            ->when(isset($where['keyword']) && trim((string)$where['keyword']) !== '', function ($query) use ($where) {
                $keyword = trim((string)$where['keyword']);
                $merchantIds = Merchant::getDB()
                    ->where('is_del', 0)
                    ->whereLike('mer_name|mer_keyword|real_name|mer_phone', '%' . $keyword . '%')
                    ->column('mer_id');
                $query->where(function ($query) use ($keyword, $merchantIds) {
                    $query->whereLike('entry_code|scene_value', '%' . $keyword . '%');
                    if (is_numeric($keyword)) {
                        $query->whereOr('mer_id', (int)$keyword);
                    }
                    if ($merchantIds) {
                        $query->whereOr('mer_id', 'in', array_map('intval', $merchantIds));
                    }
                });
            })
            ->order('id DESC');
    }

    public function getByMerIdAndType(int $merId, string $sceneType)
    {
        return StoreQrcode::getDB()
            ->where('mer_id', $merId)
            ->where('scene_type', $sceneType)
            ->find();
    }

    public function getBySceneValue(string $sceneValue)
    {
        return StoreQrcode::getDB()->where('scene_value', $sceneValue)->find();
    }

    public function entryCodeExists(string $entryCode): bool
    {
        return StoreQrcode::getDB()->where('entry_code', $entryCode)->count() > 0;
    }

    public function sceneValueExists(string $sceneValue): bool
    {
        return StoreQrcode::getDB()->where('scene_value', $sceneValue)->count() > 0;
    }
}
