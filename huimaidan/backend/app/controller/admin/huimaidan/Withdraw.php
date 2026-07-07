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

use app\common\repositories\huimaidan\WithdrawRepository;
use crmeb\basic\BaseController;
use think\App;

class Withdraw extends BaseController
{
    protected $repository;

    public function __construct(App $app, WithdrawRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date', 'status', 'financial_status', 'account_type', 'mer_id']);
        return app('json')->success($this->repository->adminList($where, $page, $limit));
    }

    public function detail($id)
    {
        return app('json')->success($this->repository->detail((int)$id));
    }

    public function audit($id)
    {
        $data = $this->request->params([['status', 0], 'refusal', 'audit_remark']);
        $data['admin_id'] = $this->request->adminId();
        $this->repository->audit((int)$id, (int)$data['status'], $data);
        return app('json')->success('审核完成');
    }

    public function transfer($id)
    {
        $image = $this->request->param('image', []);
        $this->repository->transfer((int)$id, (array)$image, $this->request->adminId());
        return app('json')->success('打款完成');
    }

    public function stats()
    {
        $where = $this->request->params(['date', 'mer_id', 'account_type']);
        return app('json')->success($this->repository->stats($where));
    }
}
