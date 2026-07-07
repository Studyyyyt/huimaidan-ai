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

use app\common\repositories\huimaidan\UserBenefitRepository;
use app\common\repositories\huimaidan\UserMerchantHistoryRepository;
use crmeb\basic\BaseController;
use think\App;

class User extends BaseController
{
    protected $repository;
    protected $merchantHistoryRepository;

    public function __construct(
        App $app,
        UserBenefitRepository $repository,
        UserMerchantHistoryRepository $merchantHistoryRepository
    )
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->merchantHistoryRepository = $merchantHistoryRepository;
    }

    public function benefit()
    {
        return app('json')->success($this->repository->summary($this->request->uid()));
    }

    public function assets()
    {
        return json($this->repository->miniProgramSuccessPayload(
            $this->repository->assets($this->request->uid())
        ));
    }

    public function merchantHistory()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['latitude', 'longitude', 'store_group_id']);
        return app('json')->success($this->merchantHistoryRepository->getList(
            (int)$this->request->uid(),
            $where,
            $page,
            $limit
        ));
    }

    public function deleteMerchantHistory($id)
    {
        $this->merchantHistoryRepository->deleteOne((int)$this->request->uid(), (int)$id);
        return app('json')->success('浏览记录已删除');
    }

    public function deleteMerchantHistoryBatch()
    {
        $params = $this->request->params(['history_ids', 'clear']);
        $this->merchantHistoryRepository->deleteBatch((int)$this->request->uid(), $params);
        return app('json')->success('浏览记录已删除');
    }
}
