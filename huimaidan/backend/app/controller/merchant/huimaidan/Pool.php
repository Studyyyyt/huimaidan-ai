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

namespace app\controller\merchant\huimaidan;

use app\common\repositories\huimaidan\PoolRepository;
use app\validate\admin\HuimaidanPoolValidate;
use crmeb\services\ExcelService;
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

    public function info()
    {
        return app('json')->success($this->repository->getOrCreateByMerId($this->request->merId()));
    }

    public function alarm($id, HuimaidanPoolValidate $validate)
    {
        $data = $this->request->params(['alarm_balance', 'alarm_enabled']);
        $validate->only(['alarm_balance', 'alarm_enabled'])->check($data);
        return app('json')->success($this->repository->saveAlarm((int)$id, $data, $this->request->merId()));
    }

    public function transactions($id)
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['type', 'date']);
        $where['pool_id'] = (int)$id;
        $where['mer_id'] = $this->request->merId();
        return app('json')->success($this->repository->transactions($where, $page, $limit));
    }

    public function transactionsExport($id)
    {
        $this->repository->detail((int)$id, $this->request->merId());
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['type', 'date']);
        $where['pool_id'] = (int)$id;
        $where['mer_id'] = $this->request->merId();
        return app('json')->success(app()->make(ExcelService::class)->huimaidanPoolTransactions($where, $page, $limit));
    }
}
