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
use think\exception\ValidateException;
use app\common\dao\circle\CircleAgentDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\admin\AdminRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\system\auth\RoleRepository;

class CircleAgentRepository extends BaseRepository
{
    const IS_AGENT = [
        CircleAgentDao::TYPE['REGION'] => 1, // 区域
        CircleAgentDao::TYPE['MERCHANT'] => 2, // 商户
    ];

    public function __construct(CircleAgentDao $dao)
    {
        $this->dao = $dao;
    }
    /**
     * 获取代理列表
     *
     * @param int $page
     * @param int $limit
     * @param array $where
     * @param array $with
     * @return void
     */
    public function getList(int $page = 1, int $limit = 10, array $where, array $with = [])
    {
        $query = $this->dao->search($where)
            ->when(!empty($with), function ($query) use ($with) {
                $query->with($with);
            });

        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        // 取各个状态的总数
        if (isset($where['status'])) {
            unset($where['status']);
        }
        $typeTotal = $this->dao->search($where, true)->field("count(c.circle_agent_id)AS`all`,ifnull(sum(if(c.status=" . CircleAgentDao::STATUS['REVIEW'] . ",1,0)), 0)AS review,ifnull(sum(if(c.status=" . CircleAgentDao::STATUS['APPROVER'] . ",1,0)), 0)AS approver,ifnull(sum(if(c.status=" . CircleAgentDao::STATUS['REJECTED'] . ",1,0)), 0)AS rejected,ifnull(sum(if(c.status=" . CircleAgentDao::STATUS['REVOKED'] . ",1,0)), 0)AS revoked")->select()->toArray();
        $typeTotal = array_shift($typeTotal);

        return compact('list', 'count', 'typeTotal');
    }
    /**
     * 创建代理
     *
     * @param int $uid
     * @param array $data
     * @return void
     */
    public function create(int $uid, array $data)
    {
        $oldInfo = $this->dao->getWhere(['type' => $data['type'], 'uid' => $uid, 'status' => [CircleAgentDao::STATUS['REVIEW'], CircleAgentDao::STATUS['APPROVER']]]);
        if ($oldInfo) {
            throw new ValidateException('您已有一条入驻申请正在审核中或已通过，无需重复提交');
        }
        $data['uid'] = $uid;

        return $this->dao->create($data);
    }
    /**
     * 更新代理
     *
     * @param int $id
     * @param int $uid
     * @param array $data
     * @return void
     */
    public function update(int $id, int $uid, array $data)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }
        if ($info['uid'] != $uid) {
            throw new ValidateException('无权限操作');
        }
        if (!in_array($info['status'], [CircleAgentDao::STATUS['REJECTED'], CircleAgentDao::STATUS['REVOKED']])) {
            throw new ValidateException('状态异常，无法修改');
        }

        $data['audit_time'] = null;
        $data['audit_reason'] = '';
        $data['audit_admin_id'] = 0;
        $data['status'] = CircleAgentDao::STATUS['REVIEW'];
        $data['extend'] = $data['extend'] ? json_encode($data['extend']) : [];
        $data['qualification'] = $data['qualification'] ? json_encode($data['qualification']) : [];
        $data['update_time'] = date('Y-m-d H:i:s');

        return $this->dao->update($id, $data);
    }
    /**
     * 撤销代理
     *
     * @param int $id
     * @param int $uid
     * @return void
     */
    public function revoke($id, $uid)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }
        if ($info['uid'] != $uid) {
            throw new ValidateException('无权限操作');
        }
        if ($info['status'] != CircleAgentDao::STATUS['REVIEW']) {
            throw new ValidateException('状态异常，无法撤销');
        }

        $data['status'] = CircleAgentDao::STATUS['REVOKED'];
        $data['update_time'] = date('Y-m-d H:i:s');

        return $this->dao->update($id, $data);
    }
    /**
     * 后台创建代理
     *
     * @param int $adminId
     * @param array $data
     * @return void
     */
    public function adminCreate(int $adminId, array $data)
    {
        if ($data['uid']) {
            $oldInfo = $this->dao->getWhere([
                'type' => $data['type'],
                'uid' => $data['uid'],
                'status' => [CircleAgentDao::STATUS['REVIEW'], CircleAgentDao::STATUS['APPROVER']]
            ]);
            if ($oldInfo) {
                throw new ValidateException('该用户已有一条入驻申请正在审核中或已通过，无需重复提交');
            }
        }
        $data['status'] = CircleAgentDao::STATUS['APPROVER'];
        $data['audit_admin_id'] = $adminId;
        $data['audit_time'] = date('Y-m-d H:i:s');
        $data['status'] = CircleAgentDao::STATUS['APPROVER'];

        return Db::transaction(function () use ($data) {
            $res = $this->dao->create($data);
            // 后台添加默认审核通过,生成商圈管理员账号
            $this->circleUserAdmin($res->circle_agent_id, $data);

            return true;
        });
    }
    /**
     * 后台更新代理
     *
     * @param int $id
     * @param int $adminId
     * @param array $data
     * @return void
     */
    public function adminUpdate(int $id, int $adminId, array $data)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }
        if (!$info['uid'] && $data['uid']) {
            $oldInfo = $this->dao->getWhere([
                'type' => $data['type'],
                'uid' => $data['uid'],
                'status' => [CircleAgentDao::STATUS['REVIEW'], CircleAgentDao::STATUS['APPROVER']]
            ]);
            if ($oldInfo) {
                throw new ValidateException('该用户已有一条入驻申请正在审核中或已通过，无需重复提交');
            }
        }
        if ($info['status'] !== CircleAgentDao::STATUS['APPROVER']) {
            throw new ValidateException('状态异常，无法修改');
        }
        $data['audit_admin_id'] = $adminId;
        $data['audit_time'] = date('Y-m-d H:i:s');
        $data['extend'] = $data['extend'] ? json_encode($data['extend']) : [];
        $data['qualification'] = $data['qualification'] ? json_encode($data['qualification']) : [];
        $data['update_time'] = date('Y-m-d H:i:s');

        return Db::transaction(function () use ($id, $data) {
            $this->dao->update($id, $data);
            // 后台添加默认审核通过,生成商圈管理员账号
            $this->circleUserAdmin($id, $data, true);

            return true;
        });
    }
    /**
     * 后台审核代理
     *
     * @param int $id
     * @param int $adminId
     * @param array $data
     * @return void
     */
    public function audit($id, int $adminId, array $data)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }
        if ($info['status'] != CircleAgentDao::STATUS['REVIEW']) {
            throw new ValidateException('状态异常，无法审核');
        }

        $data['audit_admin_id'] = $adminId;
        $data['audit_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');

        return Db::transaction(function () use ($id, $data, $info) {
            $this->dao->update($id, $data);
            // 审核通过后生成商圈管理员账号
            if ($data['status'] == CircleAgentDao::STATUS['APPROVER']) {
                $this->circleUserAdmin($id, $info->toArray());
                // type为商户，则自动创建组织商户
                if ($info['type'] == CircleAgentDao::TYPE['MERCHANT']) {
                    // 创建组织商户
                    $circleRepository = app()->make(CircleRepository::class);
                    $circleRepository->create([
                        "pid" => 0,
                        "type" => 1,
                        "role_id" => app()->make(RoleRepository::class)->getWhere(['is_default' => 1, 'is_agent' => 2, 'status' => 1])->role_id ?? 0,
                        "name" => $info['business_name'],
                        "circle_agent_id" => $id,
                        "status" => 1,
                        "is_show" => 1,
                        "positioning_status" => 0,
                        "business_store_category" => $info['business_store_category'],
                        "business_store_type" => $info['business_store_type'],
                        "merchant_ids" => []
                    ]);
                }
            }

            return true;
        });
    }
    /**
     * 后台删除代理
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }
        if ($info['status'] !== CircleAgentDao::STATUS['APPROVER']) {
            throw new ValidateException('状态异常，无法删除');
        }

        return Db::transaction(function () use ($id) {
            $this->dao->delete($id);
            // 删除成功后清除该商户下的商圈管理员账号
            app()->make(AdminRepository::class)->getSearch(['circle_agent_id' => $id, 'is_agent' => 1])->delete();
            // 清除关联的商圈的关联关系
            app()->make(CircleRepository::class)->search(['circle_agent_id' => $id])->update(['circle_agent_id' => 0]);

            return true;
        });
    }
    /**
     * 后台创建/更新商圈管理员账号
     *
     * @param int $circleAgentId
     * @param array $data
     * @param bool $isEdit
     * @return void
     */
    protected function circleUserAdmin(int $circleAgentId, array $data, bool $isEdit = false)
    {
        $adminRepository = app()->make(AdminRepository::class);

        if (!$isEdit) {
            $param['account'] = isset($data['account']) && !empty($data['account']) ? $data['account'] : $data['phone'];
            $param['pwd'] = isset($data['password']) && !empty($data['password']) ? $data['password'] : '000000'; // 默认密码 000000
            $param['pwd'] = $adminRepository->passwordEncode($param['pwd']);
        }
        $param['phone'] = $data['phone'];
        $param['real_name'] = $data['name'];
        $param['status'] = 1;
        $param['roles'] = '';
        $param['is_agent'] = self::IS_AGENT[$data['type']];
        $param['circle_agent_id'] = $circleAgentId;

        if ($isEdit) {
            return $adminRepository->search(['circle_agent_id' => $circleAgentId])->update($param);
        }

        return $adminRepository->create($param);
    }
    /**
     * 后台获取关联的商户列表
     *
     * @param int $id
     * @param int $page
     * @param int $limit
     * @param array $where
     * @return void
     */
    public function associatedMerchantList(int $id, int $page, int $limit, array $where)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('代理记录不存在!');
        }
        if (!$where['circle_id']) {
            $circleIds = app()->make(CircleRepository::class)->getSearch(['circle_agent_id' => $id])->column('circle_id');
        } else {
            $circleIds = [$where['circle_id']];
            unset($where['circle_id']);
        }

        if (empty($circleIds)) {
            return ['list' => [], 'count' => 0];
        }
        if ($info['type'] == CircleAgentDao::TYPE['REGION']) {
            $where['region_id'] = $circleIds;
        }
        if ($info['type'] == CircleAgentDao::TYPE['MERCHANT']) {
            $where['business_id'] = $circleIds;
        }
        $query = app()->make(MerchantRepository::class)->search($where);

        $count = $query->count();
        $list = $query->page($page, $limit)->field('mer_id, mer_name,real_name, mer_phone, region_id,status,mer_state')
            ->with(['merchantRegion'])->select();

        return compact('list', 'count');
    }
    /**
     * 获取代理的结算方式
     *
     * @param int $id
     * @return void
     */
    public function getSettlementMethod(int $id)
    {
        $where['circle_agent_id'] = $id;
        $field = 'circle_agent_id,payment_method, payment_name, payment_account, payment_bank, payment_qr_img';

        $info = $this->dao->getWhere($where, $field);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }

        return $info;
    }
    /**
     * 设置代理的结算方式
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function setSettlementMethod(int $id, array $data)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }

        $data['update_time'] = date('Y-m-d H:i:s');

        return $this->dao->update($id, $data);
    }
    /**
     * 获取代理下拉列表
     *
     * @param string $type
     * @return void
     */
    public function options(string $type)
    {
        $where['type'] = $type;
        $where['status'] = CircleAgentDao::STATUS['APPROVER'];

        return $this->dao->search($where)->column('circle_agent_id, name');
    }
    /**
     * 重置密码
     *
     * @param int $id
     * @return void
     */
    public function resetPassword(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new ValidateException('记录不存在!');
        }

        $adminRepository = app()->make(AdminRepository::class);
        $adminInfo = $adminRepository->getWhere(['circle_agent_id' => $id, 'is_agent' => self::IS_AGENT[$info['type']]]);
        if (!$adminInfo) {
            throw new ValidateException('管理员账号不存在!');
        }

        $data['pwd'] = '000000'; // 默认密码 000000
        $data['pwd'] = $adminRepository->passwordEncode($data['pwd']);
        $data['update_time'] = date('Y-m-d H:i:s');

        return $adminRepository->update($adminInfo['admin_id'], $data);
    }
}
