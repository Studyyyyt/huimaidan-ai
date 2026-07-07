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

use app\common\repositories\huimaidan\HuimaidanStoreQrcodeRepository;
use crmeb\basic\BaseController;
use think\App;

class StoreQrcode extends BaseController
{
    protected $repository;

    public function __construct(App $app, HuimaidanStoreQrcodeRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function detail()
    {
        return app('json')->success($this->repository->merchantDetail((int)$this->request->merId()));
    }

    public function refresh()
    {
        return app('json')->success($this->repository->refresh(
            (int)$this->request->merId(),
            false,
            HuimaidanStoreQrcodeRepository::SOURCE_MERCHANT,
            (int)$this->request->merAdminId(),
            'merchant_refresh'
        ));
    }

    public function download()
    {
        $file = $this->repository->download((int)$this->request->merId());
        return download($file['absolute_path'], $file['file_name']);
    }
}
