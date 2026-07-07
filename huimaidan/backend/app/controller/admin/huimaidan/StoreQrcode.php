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

    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['mer_id', 'keyword', 'status', 'last_generate_status']);
        return app('json')->success($this->repository->getList($where, (int)$page, (int)$limit));
    }

    public function detail($merId)
    {
        return app('json')->success($this->repository->adminDetail((int)$merId));
    }

    public function refresh($merId)
    {
        $adminId = $this->request->hasMacro('adminId') ? (int)$this->request->adminId() : 0;
        $reason = (string)$this->request->post('reason', 'admin_force_refresh');
        return app('json')->success($this->repository->refresh(
            (int)$merId,
            true,
            HuimaidanStoreQrcodeRepository::SOURCE_ADMIN,
            $adminId,
            $reason
        ));
    }

    public function download($merId)
    {
        $file = $this->repository->download((int)$merId);
        return download($file['absolute_path'], $file['file_name']);
    }
}
