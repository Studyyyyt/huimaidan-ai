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

use app\common\repositories\huimaidan\SettlementRepository;
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
        $where = $this->request->params(['date', 'order_sn']);
        return app('json')->success($this->repository->merchantStatsData($this->request->merId(), $where));
    }

    public function orders()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['paid', 'date', 'order_sn']);
        return app('json')->success($this->repository->merchantOrderList($this->request->merId(), $where, $page, $limit));
    }

    public function detail($id)
    {
        return app('json')->success($this->repository->merchantOrderDetail($this->request->merId(), (int)$id));
    }

    /**
     * 小时级收款趋势
     * GET /mer/huimaidan/settlement/hourly
     * 返回指定日期内每小时的收款金额和笔数，用于前端折线图展示
     */
    public function hourly()
    {
        $merId = $this->request->merId();
        $date = $this->request->param('date', '');
        return app('json')->success($this->repository->hourly($merId, $date));
    }
}
