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

use app\common\repositories\huimaidan\MerchantDiscountRepository;
use app\validate\merchant\HuimaidanDiscountValidate;
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
        $where = $this->request->params(['status']);
        $where['mer_id'] = $this->request->merId();
        return app('json')->success($this->repository->getList($where, $page, $limit));
    }

    public function create(HuimaidanDiscountValidate $validate)
    {
        return app('json')->fail('惠买单折扣由平台统一配置');
    }

    public function update($id, HuimaidanDiscountValidate $validate)
    {
        return app('json')->fail('惠买单折扣由平台统一配置');
    }

    public function delete($id)
    {
        return app('json')->fail('惠买单折扣由平台统一配置');
    }

    public function status($id)
    {
        return app('json')->fail('惠买单折扣由平台统一配置');
    }

    protected function params(): array
    {
        return $this->request->params([
            'title', 'pool_id', 'rule_type', 'platform_discount', 'merchant_cost',
            'coupon_amount', 'point_ratio', 'min_amount', 'status', 'sort',
            'start_time', 'end_time',
        ]);
    }
}
