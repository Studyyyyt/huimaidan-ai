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

namespace app\controller\api\huimaidan;

use app\common\repositories\huimaidan\MerchantDiscoveryRepository;
use app\common\repositories\huimaidan\UserMerchantHistoryRepository;
use crmeb\basic\BaseController;
use think\App;

class Store extends BaseController
{
    protected $repository;
    protected $historyRepository;

    public function __construct(
        App $app,
        MerchantDiscoveryRepository $repository,
        UserMerchantHistoryRepository $historyRepository
    )
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->historyRepository = $historyRepository;
    }

    public function detail($id)
    {
        $uid = $this->uid();
        $location = $this->request->params(['latitude', 'longitude']);
        $detail = $this->repository->detail((int)$id, $uid, $location);
        if ($uid > 0) {
            $this->historyRepository->record($uid, (int)$id);
        }
        return app('json')->success($detail);
    }

    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['keyword', 'category_id', 'store_group_id', 'city_id', 'order', 'latitude', 'longitude', 'distance']);
        return app('json')->success($this->repository->getList($where, $page, $limit, $this->uid()));
    }

    public function nearby()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['category_id', 'city_id', 'latitude', 'longitude', 'distance']);
        return app('json')->success($this->repository->nearby($where, $page, $limit, $this->uid()));
    }

    public function categories()
    {
        return app('json')->success($this->repository->categories());
    }

    public function cities()
    {
        return app('json')->success($this->repository->cities());
    }

    public function filters()
    {
        return app('json')->success($this->repository->filters());
    }

    public function branches($id)
    {
        return app('json')->success($this->repository->branchStores((int)$id));
    }

    protected function uid(): int
    {
        return $this->request->hasMacro('isLogin') && $this->request->isLogin() ? (int)$this->request->uid() : 0;
    }
}
