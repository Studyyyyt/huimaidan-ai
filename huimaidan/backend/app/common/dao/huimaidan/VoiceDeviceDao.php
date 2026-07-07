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
use app\common\model\huimaidan\VoiceDevice;

class VoiceDeviceDao extends BaseDao
{
    protected function getModel(): string
    {
        return VoiceDevice::class;
    }

    /**
     * 搜索条件构建
     */
    public function search(array $where)
    {
        return VoiceDevice::getDB()
            ->where('is_del', 0)
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
                $query->where('status', (int)$where['status']);
            })
            ->when(isset($where['device_sn']) && $where['device_sn'] !== '', function ($query) use ($where) {
                $query->whereLike('device_sn', '%' . $where['device_sn'] . '%');
            })
            ->when(isset($where['keyword']) && $where['keyword'] !== '', function ($query) use ($where) {
                $query->where(function ($query) use ($where) {
                    $query->whereLike('device_name', '%' . $where['keyword'] . '%')
                        ->whereOrLike('device_sn', '%' . $where['keyword'] . '%');
                });
            })
            ->order('id DESC');
    }

    /**
     * 获取商户下所有启用的设备
     */
    public function getEnabledDevices(int $merId): array
    {
        return VoiceDevice::getDB()
            ->where('mer_id', $merId)
            ->where('status', VoiceDevice::STATUS_ENABLE)
            ->where('is_del', 0)
            ->select()
            ->toArray();
    }

    /**
     * 根据SN查找设备
     */
    public function findBySn(string $sn): ?array
    {
        $result = VoiceDevice::getDB()
            ->where('device_sn', $sn)
            ->where('is_del', 0)
            ->find();
        return $result ? $result->toArray() : null;
    }

    /**
     * 统计商户设备数
     */
    public function countByMerId(int $merId): int
    {
        return VoiceDevice::getDB()
            ->where('mer_id', $merId)
            ->where('is_del', 0)
            ->count();
    }
}
