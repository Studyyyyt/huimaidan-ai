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

namespace app\controller\admin\huimaidan;

use app\common\repositories\huimaidan\PoolRepository;
use app\validate\admin\HuimaidanPoolValidate;
use crmeb\basic\BaseController;
use think\App;

class Pool extends BaseController
{
    protected $repository;

    public function __construct(App $app, PoolRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params([
            'mer_id', 'keyword', 'status', 'alarm_enabled', 'alarm_status',
            'balance_gte', 'balance_lte', 'order_field', 'order_direction',
        ]);
        return app('json')->success($this->repository->getList($where, $page, $limit));
    }

    public function detail($id)
    {
        return app('json')->success($this->repository->detail((int)$id));
    }

    public function create(HuimaidanPoolValidate $validate)
    {
        $data = $this->request->params(['mer_id']);
        $validate->only(['mer_id'])->check($data);
        return app('json')->success($this->repository->getOrCreateByMerId((int)$data['mer_id']));
    }

    public function recharge($id, HuimaidanPoolValidate $validate)
    {
        $data = $this->request->params(['amount', 'remark']);
        $validate->only(['amount', 'remark'])->check($data);
        $adminId = $this->request->hasMacro('adminId') ? (int)$this->request->adminId() : 0;
        return app('json')->success($this->repository->recharge((int)$id, $data['amount'], (string)($data['remark'] ?? ''), $adminId));
    }

    public function adjust($id, HuimaidanPoolValidate $validate)
    {
        $data = $this->request->params(['amount', 'type', 'remark']);
        $validate->only(['amount', 'remark'])->check($data);
        $adminId = $this->request->hasMacro('adminId') ? (int)$this->request->adminId() : 0;
        return app('json')->success($this->repository->adjust((int)$id, $data['amount'], (int)$data['type'], (string)($data['remark'] ?? ''), $adminId));
    }

    public function alarm($id, HuimaidanPoolValidate $validate)
    {
        $data = $this->request->params(['alarm_balance', 'alarm_enabled']);
        $validate->only(['alarm_balance', 'alarm_enabled'])->check($data);
        return app('json')->success($this->repository->saveAlarm((int)$id, $data));
    }

    public function status($id, HuimaidanPoolValidate $validate)
    {
        $data = $this->request->params(['status']);
        $validate->only(['status'])->check($data);
        $this->repository->changeStatus((int)$id, (int)$data['status']);
        return app('json')->success('修改成功');
    }

    public function transactions($id)
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['type', 'date']);
        $where['pool_id'] = (int)$id;
        return app('json')->success($this->repository->transactions($where, $page, $limit));
    }

    public function alarms()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params([
            'mer_id', 'keyword', 'status', 'balance_gte', 'balance_lte',
            'order_field', 'order_direction',
        ]);
        return app('json')->success($this->repository->alarms($where, $page, $limit));
    }

    public function alarmRecords()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['pool_id', 'mer_id', 'notice_status', 'date']);
        return app('json')->success($this->repository->alarmRecords($where, $page, $limit));
    }

    public function batchAlarm(HuimaidanPoolValidate $validate)
    {
        $data = $this->request->params([['pool_ids', []], 'alarm_balance', 'alarm_enabled']);
        $validate->only(['pool_ids', 'alarm_balance', 'alarm_enabled'])->check($data);
        return app('json')->success([
            'updated_count' => $this->repository->batchSaveAlarm($data['pool_ids'], $data),
        ]);
    }
}
