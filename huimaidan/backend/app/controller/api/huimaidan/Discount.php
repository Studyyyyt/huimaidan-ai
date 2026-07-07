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

use app\common\repositories\huimaidan\DiscountEngineRepository;
use app\validate\api\HuimaidanOrderValidate;
use crmeb\basic\BaseController;
use think\App;

class Discount extends BaseController
{
    protected $repository;

    public function __construct(App $app, DiscountEngineRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function calculate(HuimaidanOrderValidate $validate)
    {
        if (!$this->request->isLogin()) {
            return app('json')->fail('请先登录');
        }
        $data = $this->request->params(['mer_id', 'amount', 'useMemberDiscount', 'use_member_discount']);
        $validate->scene('calculate')->check($data);
        $useMemberDiscount = $this->useMemberDiscount($data);
        return app('json')->success($this->repository->publicCalculate((int)$data['mer_id'], $data['amount'], $this->request->uid(), $useMemberDiscount));
    }

    protected function useMemberDiscount(array $data): bool
    {
        $value = true;
        if (isset($data['useMemberDiscount']) && $data['useMemberDiscount'] !== '') {
            $value = $data['useMemberDiscount'];
        } elseif (isset($data['use_member_discount']) && $data['use_member_discount'] !== '') {
            $value = $data['use_member_discount'];
        }
        return $this->truthy($value);
    }

    protected function truthy($value): bool
    {
        return in_array($value, [1, '1', true, 'true', 'on'], true);
    }
}
