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

namespace app\common\repositories\circle;

use think\facade\Db;
use app\common\dao\circle\CircleDao;
use crmeb\traits\CategoresRepository;
use think\exception\ValidateException;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\admin\AdminRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\system\auth\RoleRepository;

class CircleRepository extends BaseRepository
{
    use CategoresRepository;

    const IS_AGENT = [
        CircleDao::TYPE['REGION'] => 1, // 区域
        CircleDao::TYPE['MERCHANT'] => 2, // 商户
    ];

    const LIST_FIELDS = 'circle_id,pid,name,circle_agent_id,commission_rate,sort,status,commission_type,level,type';

    const SYSTEMC_COMMISSION = [
        0 => 'one_agent_commission',
        1 => 'two_agent_commission',
        2 => 'three_agent_commission',
    ];

    public function __construct(CircleDao $dao)
    {
        $this->dao = $dao;
    }
    /**
     * 获取组织列表
     *
     * @param array $where
     * @param array $with
     * @return void
     */
    public function getList(array $where, array $with = [], int $page, int $limit)
    {
        $query = $this->dao->search($where)
            ->when(!empty($with), function ($query) use ($with) {
                $query->with($with);
            });

        $count = $query->count();
        if($where['type'] == CircleDao::TYPE['REGION']) {
            $list = formatCategory($query->field(self::LIST_FIELDS)->append(['merchant_count'])->select()->toArray(), $this->dao->getPk());
        }
        if($where['type'] == CircleDao::TYPE['MERCHANT']) {
            $list = $query->page($page, $limit)->append(['merchant_count'])->select();
        }

        return compact('list', 'count');
    }
    /**
     * 创建组织
     *
     * @param array $data
     * @return void
     */
    public function create(array $data)
    {
        if($data['pid'] && !$this->dao->exists($data['pid'])) {
            throw new ValidateException('上级商圈不存在!');
        }

        if ($data['pid'] && ($this->getLevelById($data['pid']) >= $this->dao->getMaxLevel())) {
            throw new ValidateException('最多' . $this->dao->getMaxLevel() . '级!');
        }
        $data[$this->dao->getPath()] = $this->getPathById($data[$this->dao->getParentId()]);
        $data[$this->dao->getLevel()] = $this->getLevelById($data[$this->dao->getParentId()]);
        $this->checkRate($data);
        // 检查身份权限数据
        $this->checkRole($data);
        return Db::transaction(function () use ($data) {
            $res = $this->dao->create($data);
            // 商圈创建后更新商圈管理员账号所属商圈ID
            $this->updateAdminRegionIds($data['circle_agent_id'], $res->circle_id, true);

            $merchantIds = $data['merchant_ids'];
            unset($data['merchant_ids']);
            if($merchantIds) {
                $merchantRepository = app()->make(MerchantRepository::class);
                // 更新店铺所属区域id
                if ($data['type'] == CircleDao::TYPE['REGION']) {
                    $merchantRepository->search(['region_id' => $res->circle_id])->update(['region_id' => 0]);
                    $merchantRepository->search(['mer_id' => $merchantIds])->update(['region_id' => $res->circle_id]);
                }
                // 更新店铺所属商户id
                if ($data['type'] == CircleDao::TYPE['MERCHANT']) {
                    $merchantRepository->search(['business_id' => $res->circle_id])->update(['business_id' => 0]);
                    $merchantRepository->search(['mer_id' => $merchantIds])->update(['business_id' => $res->circle_id]);
                }
            }

            return true;
        });
    }
    /**
     * 更新组织
     *
     * @param integer $id
     * @param array $data
     * @return void
     */
    public function update(int $id, array $data)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }
        if($data['pid'] && !$this->dao->exists($data['pid'])) {
            throw new ValidateException('上级商圈不存在!');
        }
        if ($data['pid'] && ($this->getLevelById($data['pid']) >= $this->dao->getMaxLevel())) {
            throw new ValidateException('最多' . $this->dao->getMaxLevel() . '级!');
        }
        if ($this->dao->checkChangeToChild($id, $data['pid'])) {
            throw new ValidateException('无法修改到当前分类到子集，请先修改子类');
        }
        if (!$this->checkChildLevel($id, $data['pid'])) {
            throw new ValidateException('子类超过最高等级限制，请先修改子类');
        }

        $data[$this->dao->getPath()] = $this->getPathById($data[$this->dao->getParentId()]);
        $data[$this->dao->getLevel()] = $this->getLevelById($data[$this->dao->getParentId()]);
        $this->checkRate($data, $id, $info);
        // 检查身份权限数据
        $this->checkRole($data);

        $data['update_time'] = date('Y-m-d H:i:s');
        $data['commission_rate'] = $data['commission_type'] == 0 ? 0 : $data['commission_rate'];
        unset($data['type']);

        return Db::transaction(function () use ($id, $data, $info) {
            if ($data['circle_agent_id'] !== $info->circle_agent_id) {
                if($info['circle_agent_id']) {
                    // 清除原商圈管理员所属商圈ID
                    $this->updateAdminRegionIds($info['circle_agent_id'], $id, false);
                }
                // 更新新商圈管理员账号所属商圈ID
                $this->updateAdminRegionIds($data['circle_agent_id'], $id, true);
            }

            $merchantIds = $data['merchant_ids'];
            unset($data['merchant_ids']);
            if($merchantIds) {
                $merchantRepository = app()->make(MerchantRepository::class);
                // 更新店铺所属区域id
                if ($info['type'] == CircleDao::TYPE['REGION']) {
                    $merchantRepository->search(['region_id' => $info['circle_id']])->update(['region_id' => 0]);
                    $merchantRepository->search(['mer_id' => $merchantIds])->update(['region_id' => $id]);
                }
                // 更新店铺所属商户id
                if ($info['type'] == CircleDao::TYPE['MERCHANT']) {
                    $merchantRepository->search(['business_id' => $info['circle_id']])->update(['business_id' => 0]);
                    $merchantRepository->search(['mer_id' => $merchantIds])->update(['business_id' => $id]);
                }
            }

            $this->dao->update($id, $data);

            return true;
        });
    }
    /**
     * 删除组织
     *
     * @param integer $id
     * @return void
     */
    public function delete(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }
        if ($this->dao->hasChild($id)) {
            throw new ValidateException('该商圈存在子集，请先处理子集');
        }

        return Db::transaction(function () use ($id, $info) {
            if($info['circle_agent_id']) {
                // 清除原商圈管理员所属商圈ID
                $this->updateAdminRegionIds($info['circle_agent_id'], $id, false);
            }
            // 商圈删除后释放商户所属商圈
            app()->make(MerchantRepository::class)->search(['region_id' => $info['circle_id']])->update(['region_id' => 0]);
            $this->dao->delete($id);

            return true;
        });
    }
    /**
     * 切换组织状态
     *
     * @param integer $id
     * @param array $data
     * @return void
     */
    public function switch(int $id, array $data)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }

        $info->status = $data['status'];

        return $info->save();
    }
    /**
     * 获取商圈下关联的店铺列表
     *
     * @param integer $id
     * @param integer $page
     * @param integer $limit
     * @param array $where
     * @return void
     */
    public function associatedMerchantList($id, $page, $limit, $where)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('商圈记录不存在!');
        }
        if($info['type'] == CircleDao::TYPE['REGION']) {
            $where['region_id'] = $id;
        }
        if($info['type'] == CircleDao::TYPE['MERCHANT']) {
            $where['business_id'] = $id;
        }
        $query = app()->make(MerchantRepository::class)->search($where);

        $count = $query->count();
        $list = $query->page($page, $limit)->field('mer_id, mer_name, real_name ,mer_phone, region_id,status,mer_state')->select();

        return compact('list', 'count');
    }
    /**
     * 更新商圈管理员所属商圈ID
     *
     * @param integer $circleAgentId
     * @param integer $regionIds
     * @param integer $roleId
     * @param bool $isAdd
     * @return void
     */
    protected function updateAdminRegionIds(int $circleAgentId, int $regionIds, bool $isAdd = true)
    {
        $adminRepository = app()->make(AdminRepository::class);
        $adminInfo = $adminRepository->getSearch(['circle_agent_id' => $circleAgentId])->find();

        $adminRegionIds = $adminInfo->region_ids ?: [];
        if (in_array($regionIds, $adminRegionIds) && !$isAdd) {
            $adminRegionIds = array_diff($adminRegionIds, [$regionIds]);
        } else {
            $adminRegionIds[] = $regionIds;
        }

        $adminInfo->region_ids = implode(',', $adminRegionIds);

        return $adminInfo->save();
    }
    /**
     * 检查身份权限数据
     *
     * @param array $data
     * @return void
     */
    private function checkRole(array $data)
    {
        // 检查身份权限数据
        $role = app()->make(RoleRepository::class)->get($data['role_id']);
        if (!$role) {
            throw new ValidateException('身份权限数据不存在!');
        }
        if (self::IS_AGENT[$data['type']] != $role['is_agent']) {
            throw new ValidateException('组织类型和身份权限类型不匹配!');
        }
    }
    /**
     * 检查提成比例
     *
     * @param array $data
     * @param integer $id
     * @param array $info
     * @return void
     */
    private function checkRate($data, $id = 0, $info = null)
    {
        if($data['type'] == CircleDao::TYPE['MERCHANT']) {
            return false;
        }
        if ($data['pid'] && $data['commission_type']) {
            $this->checkParentLevelRate($data['commission_rate'], $data['pid']);
        }
        // 存在子级时，检查当前层级提成比例是否低于子级层级提成比例
        if ($id && $info['level'] != 2) {
            $this->checkChildLevelRate($data['commission_rate'], $id);
        }
    }
    /**
     * 检查父级层级提成比例
     *
     * @param float $rate
     * @param int $pid
     * @return void
     */
    private function checkParentLevelRate(float $rate, int $pid)
    {
        $parentRateInfo = $this->dao->getWhere(['circle_id' => $pid], 'commission_type, commission_rate, level');
        if($rate > $parentRateInfo['commission_rate']) {
            throw new ValidateException("子级提成比例【{$rate}】不能大于父级提成比例【{$parentRateInfo['commission_rate']}】，请重新设置!");
        }

        return true;
    }
    /**
     * 检查子级层级提成比例
     *
     * @param float $rate
     * @param int $id
     * @return void
     */
    private function checkChildLevelRate(float $rate, int $id)
    {
        $childRateInfo = $this->dao->getSearch(['pid' => $id])->field('circle_id, name, commission_type, commission_rate, level')->select()->toArray();
        if(!$childRateInfo) {
            return true;
        }

        foreach ($childRateInfo as $child) {
            if($child['commission_rate'] > $rate) {
                throw new ValidateException("当前层级提成比例【{$rate}】不能小于子级【{$child['name']}】的提成比例【{$child['commission_rate']}】，请先修改子级设置!");
            }
        }

        return true;
    }
}
