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

namespace app\common\repositories\huimaidan;

use app\common\dao\huimaidan\VoiceDeviceDao;
use app\common\model\huimaidan\VoiceDevice;
use app\common\repositories\BaseRepository;
use app\exception\ValidateException;

/**
 * @mixin VoiceDeviceDao
 */
class VoiceDeviceRepository extends BaseRepository
{
    protected $dao;

    public function __construct(VoiceDeviceDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 设备列表（分页）
     */
    public function getList(array $where, int $page, int $limit): array
    {
        $query = $this->dao->search($where);
        $count = $query->count();
        $list  = $this->dao->search($where)->page($page, $limit)->select();

        return compact('count', 'list');
    }

    /**
     * 新增设备
     */
    public function create(int $merId, array $data): int
    {
        // 校验
        if (empty($data['device_sn']) || mb_strlen($data['device_sn']) > 64) {
            throw new ValidateException('设备SN必填且不能超过64个字符');
        }
        if (empty($data['device_name']) || mb_strlen($data['device_name']) > 64) {
            throw new ValidateException('设备名称必填且不能超过64个字符');
        }

        // 检查SN是否已被该商户绑定
        $existing = VoiceDevice::getDB()
            ->where('mer_id', $merId)
            ->where('device_sn', $data['device_sn'])
            ->where('is_del', 0)
            ->find();

        if ($existing) {
            throw new ValidateException('该设备SN已绑定');
        }

        // 检查设备数量限制
        $count = $this->dao->countByMerId($merId);
        if ($count >= 10) {
            throw new ValidateException('最多绑定10台设备');
        }

        $id = $this->dao->create([
            'mer_id'      => $merId,
            'device_sn'   => $data['device_sn'],
            'device_name' => $data['device_name'],
            'status'      => VoiceDevice::STATUS_ENABLE,
            'create_time' => time(),
            'update_time' => time(),
        ]);

        return $id;
    }

    /**
     * 编辑设备
     */
    public function update(int $id, int $merId, array $data): bool
    {
        $device = $this->dao->get([
            'id'     => $id,
            'mer_id' => $merId,
            'is_del' => 0,
        ]);

        if (empty($device)) {
            throw new ValidateException('设备不存在');
        }

        $updateData = [];
        if (isset($data['device_name'])) {
            $updateData['device_name'] = $data['device_name'];
        }
        if (isset($data['remark'])) {
            $updateData['remark'] = $data['remark'];
        }
        $updateData['update_time'] = time();

        return $this->dao->update($id, $updateData);
    }

    /**
     * 删除设备（软删除）
     */
    public function delete(int $id, int $merId): bool
    {
        $device = $this->dao->get([
            'id'     => $id,
            'mer_id' => $merId,
            'is_del' => 0,
        ]);

        if (empty($device)) {
            throw new ValidateException('设备不存在');
        }

        return $this->dao->update($id, [
            'is_del'      => 1,
            'update_time' => time(),
        ]);
    }

    /**
     * 切换设备状态
     */
    public function changeStatus(int $id, int $merId): bool
    {
        $device = $this->dao->get([
            'id'     => $id,
            'mer_id' => $merId,
            'is_del' => 0,
        ]);

        if (empty($device)) {
            throw new ValidateException('设备不存在');
        }

        $newStatus = $device->status == VoiceDevice::STATUS_ENABLE
            ? VoiceDevice::STATUS_DISABLE
            : VoiceDevice::STATUS_ENABLE;

        return $this->dao->update($id, [
            'status'      => $newStatus,
            'update_time' => time(),
        ]);
    }

    /**
     * 获取商户下所有启用的设备
     */
    public function getEnabledDevices(int $merId): array
    {
        return $this->dao->getEnabledDevices($merId);
    }

    /**
     * 设备详情
     */
    public function detail(int $id, int $merId): array
    {
        $device = $this->dao->get([
            'id'     => $id,
            'mer_id' => $merId,
            'is_del' => 0,
        ]);

        if (empty($device)) {
            throw new ValidateException('设备不存在');
        }

        return $device->toArray();
    }
}
