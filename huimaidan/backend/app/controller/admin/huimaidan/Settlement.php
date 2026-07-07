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

use app\common\repositories\huimaidan\SettlementRepository;
use crmeb\services\ExcelService;
use crmeb\basic\BaseController;
use think\App;

class Settlement extends BaseController
{
    protected $repository;

    public function __construct(App $app, SettlementRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function stats()
    {
        $where = $this->request->params(['mer_id', 'date', 'order_sn']);
        return app('json')->success($this->repository->stats($where));
    }

    public function orders()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['mer_id', 'date', 'order_sn']);
        return app('json')->success($this->repository->orderList($where, $page, $limit));
    }

    public function merchants()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['mer_id', 'date']);
        return app('json')->success($this->repository->merchantStats($where, $page, $limit));
    }

    public function daily()
    {
        $where = $this->request->params(['mer_id']);
        return app('json')->success($this->repository->daily($where, (int)$this->request->param('days', 30)));
    }

    public function monthCompare()
    {
        $where = $this->request->params(['mer_id']);
        return app('json')->success($this->repository->monthCompare($where));
    }

    public function rank()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['mer_id', 'date']);
        return app('json')->success($this->repository->merchantStats($where, $page, $limit));
    }

    public function ordersExport()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['mer_id', 'date', 'order_sn', 'paid']);
        return app('json')->success(app()->make(ExcelService::class)->huimaidanOrders($where, $page, $limit));
    }

    public function merchantsExport()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['mer_id', 'date']);
        return app('json')->success(app()->make(ExcelService::class)->huimaidanMerchants($where, $page, $limit));
    }
}
