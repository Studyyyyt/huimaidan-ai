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
use app\common\repositories\BaseRepository;
use app\common\dao\circle\CircleBrokerageCheckoutDao;

class CircleBrokerageCheckoutRepository extends BaseRepository
{
    public function __construct(CircleBrokerageCheckoutDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(int $page = 1, int $limit = 10, array $where, array $with = [])
    {
        $query = $this->dao->search($where)
            ->when(!empty($with), function ($query) use ($with) {
                $query->with($with);
            });

        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        return compact('list', 'count');
    }

    public function create(array $data)
    {
        $agentInfo = app()->make(CircleAgentRepository::class)->get($data['agent_id']);
        if(!$agentInfo) {
            throw new ValidateException('代理商不存在');
        }
        if(!$agentInfo['payment_name'] && !$agentInfo['payment_account']) {
            throw new ValidateException('未设置结算方式，无法申请结算');
        }

        if((float)$agentInfo->balance < (float)$data['withdrawal_amount']) {
            throw new ValidateException('余额不足');
        }

        $data['agent_name'] = $agentInfo->name;
        $data['agent_phone'] = $agentInfo->phone;
        $data['withdrawal_sn'] = $this->getSn();
        $data['withdrawal_type'] = $agentInfo->payment_method;
        $data['withdrawal_name'] = $agentInfo->payment_name;
        $data['withdrawal_account'] = $agentInfo->payment_account;
        $data['withdrawal_qr_img'] = $agentInfo->payment_qr_img;

        return Db::transaction(function () use ($data) {
            $this->dao->create($data);
            // 提交成功，代理商余额减少
            app()->make(CircleAgentRepository::class)->decField($data['agent_id'], 'balance', $data['withdrawal_amount']);

            return true;
        });
    }

    public function revoke(int $id)
    {
        $info = $this->dao->get($id);
        if(!$info) {
            throw new ValidateException('提现记录不存在');
        }

        if ($info['audit_status'] != CircleBrokerageCheckoutDao::AUDIT_STATUS['REVIEW']) {
            throw new ValidateException('审核状态异常，无法撤销');
        }

        $data['audit_status'] = CircleBrokerageCheckoutDao::AUDIT_STATUS['REVOKED'];
        $data['update_time'] = date('Y-m-d H:i:s');

        return Db::transaction(function () use ($id, $data, $info) {
            $this->dao->update($id, $data);
            // 撤销后，返还代理商余额
            app()->make(CircleAgentRepository::class)->incField($info['agent_id'], 'balance', $info['withdrawal_amount']);

            return true;
        });
    }

    public function remark(int $id, array $data)
    {
        $info = $this->dao->get($id);
        if(!$info) {
            throw new ValidateException('提现记录不存在');
        }

        $data['update_time'] = date('Y-m-d H:i:s');
        return $this->dao->update($id, $data);
    }

    public function audit(int $id, int $adminId, array $data)
    {
        $info = $this->dao->get($id);
        if(!$info) {
            throw new ValidateException('提现记录不存在');
        }

        if ($info['audit_status'] != CircleBrokerageCheckoutDao::AUDIT_STATUS['REVIEW']) {
            throw new ValidateException('审核状态异常，无法审核');
        }

        $data['audit_admin_id'] = $adminId;
        $data['audit_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');

        return Db::transaction(function () use ($id, $data, $info) {
            $this->dao->update($id, $data);
            // 审核驳回后，返还代理商余额
            if($data['audit_status'] == CircleBrokerageCheckoutDao::AUDIT_STATUS['REJECTED']) {
                app()->make(CircleAgentRepository::class)->incField($info['agent_id'], 'balance', $info['withdrawal_amount']);
            }

            return true;
        });
    }

    public function transfer(int $id, array $data)
    {
        $info = $this->dao->get($id);
        if(!$info) {
            throw new ValidateException('提现记录不存在');
        }

        if ($info['audit_status'] != CircleBrokerageCheckoutDao::AUDIT_STATUS['APPROVER']) {
            throw new ValidateException('审核状态异常，无法转账');
        }
        if ($info['status'] != CircleBrokerageCheckoutDao::STATUS['NO_CREDITED']) {
            throw new ValidateException('已经转账成功，无需重复操作');
        }

        $data['update_time'] = date('Y-m-d H:i:s');
        $data['transfer_voucher'] = json_encode($data['transfer_voucher']);
        $data['status'] = CircleBrokerageCheckoutDao::STATUS['CREDITED'];

        return $this->dao->update($id, $data);
    }

    public function platformRemark(int $id, array $data)
    {
        $info = $this->dao->get($id);
        if(!$info) {
            throw new ValidateException('提现记录不存在');
        }

        $data['update_time'] = date('Y-m-d H:i:s');

        return $this->dao->update($id, $data);
    }

    protected function getSn()
    {
        // 获取当前时间的微秒和秒部分
        list($msec, $sec) = explode(' ', microtime());

        // 将微秒和秒转换为毫秒，并格式化为整数
        $msectime = number_format((floatval($msec) + floatval($sec)) * 1000, 0, '', '');

        // 生成订单编号：前缀 + 毫秒时间戳 + 随机数
        // 保证随机数在特定范围内，避免生成重复的订单编号
        $orderId = 'cbc' . $msectime . mt_rand(10000, max(intval($msec * 10000) + 10000, 98369));

        return $orderId;
    }
}
