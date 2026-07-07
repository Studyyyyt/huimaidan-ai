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

use app\common\repositories\huimaidan\MerchantDiscountRepository;
use app\validate\admin\HuimaidanDiscountValidate;
use crmeb\basic\BaseController;
use think\App;

class Discount extends BaseController
{
    protected $repository;

    public function __construct(App $app, MerchantDiscountRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['mer_id', 'status']);
        return app('json')->success($this->repository->getList($where, $page, $limit));
    }

    public function config()
    {
        return app('json')->success([
            'huimaidan_discount_stack_enabled' => $this->repository->discountStackEnabled(),
        ]);
    }

    public function detail($id)
    {
        return app('json')->success($this->repository->detail((int)$id));
    }

    public function create(HuimaidanDiscountValidate $validate)
    {
        $data = $this->params();
        $validate->check($data);
        return app('json')->success($this->repository->createDiscount($data));
    }

    public function update($id, HuimaidanDiscountValidate $validate)
    {
        $data = $this->params();
        $validate->scene('update')->check($data);
        return app('json')->success($this->repository->updateDiscount((int)$id, $data));
    }

    public function status($id)
    {
        $this->repository->changeStatus((int)$id, (int)$this->request->param('status', 0));
        return app('json')->success('修改成功');
    }

    public function delete($id)
    {
        $this->repository->deleteDiscount((int)$id);
        return app('json')->success('删除成功');
    }

    public function memberLevels()
    {
        return app('json')->success($this->repository->memberLevels());
    }

    protected function params(): array
    {
        return $this->request->params([
            'mer_id', 'pool_id', 'merchant_discount', 'status', 'start_time', 'end_time',
            'sort', 'remark', 'huimaidan_discount_stack_enabled', ['member_discounts', []],
        ]);
    }
}
