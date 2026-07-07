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

class Dashboard extends BaseController
{
    protected $repository;

    public function __construct(App $app, SettlementRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function overview()
    {
        return app('json')->success($this->repository->overview($this->request->merId()));
    }
}
