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

use app\common\repositories\huimaidan\OrderRepository;
use app\validate\api\HuimaidanOrderValidate;
use crmeb\basic\BaseController;
use think\App;

class Order extends BaseController
{
    protected $repository;

    public function __construct(App $app, OrderRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function create(HuimaidanOrderValidate $validate)
    {
        if (!$this->request->isLogin()) {
            return app('json')->fail('请先登录');
        }
        $this->repository->assertNoUnsupportedMiniProgramOrderFields($this->request->param());
        $data = $this->request->params([
            'mer_id', 'amount', 'pay_type', 'mark', 'return_url',
            'couponId', 'coupon_id', 'usePoints', 'use_points',
            'useMemberDiscount', 'use_member_discount',
        ]);
        $validate->check($data);
        $order = $this->repository->create($this->request->userInfo(), $data);
        $pay = $this->repository->pay(
            $this->request->userInfo(),
            (int)$order['group_order_id'],
            $data['pay_type'],
            (string)($data['return_url'] ?? ''),
            $this->request->isApp()
        );

        return $pay;
    }

    public function createCombined(HuimaidanOrderValidate $validate)
    {
        if (!$this->request->isLogin()) {
            return app('json')->fail('请先登录');
        }
        $data = $this->request->params([
            'mer_id', 'discount_amount', 'no_discount_amount', 'pay_type', 'mark', 'return_url',
            'couponId', 'coupon_id', 'usePoints', 'use_points',
            'useMemberDiscount', 'use_member_discount',
        ]);
        $validate->scene('combined')->check($data);
        $order = $this->repository->createCombined($this->request->userInfo(), $data);
        $pay = $this->repository->pay(
            $this->request->userInfo(),
            (int)$order['group_order_id'],
            $data['pay_type'],
            (string)($data['return_url'] ?? ''),
            $this->request->isApp()
        );

        return $pay;
    }

    public function prepare(HuimaidanOrderValidate $validate)
    {
        if (!$this->request->isLogin()) {
            return app('json')->fail('请先登录');
        }
        $data = $this->request->params(['mer_id', 'amount', 'mark', 'useMemberDiscount', 'use_member_discount']);
        $validate->scene('prepare')->check($data);
        $order = $this->repository->prepare($this->request->userInfo(), $this->request->param());

        return app('json')->success($order);
    }

    public function pay($id)
    {
        $type = (string)$this->request->param('pay_type', $this->request->param('type', ''));
        return $this->repository->pay($this->request->userInfo(), (int)$id, $type, (string)$this->request->param('return_url', ''), $this->request->isApp());
    }

    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['paid', 'date']);
        return app('json')->success($this->repository->getList($this->request->uid(), $where, $page, $limit));
    }

    public function statistics()
    {
        return app('json')->success($this->repository->statistics($this->request->uid()));
    }

    public function payResult($id)
    {
        return app('json')->success($this->repository->payResult($this->request->uid(), (int)$id));
    }

    public function detail($id)
    {
        return app('json')->success($this->repository->detail($this->request->uid(), (int)$id));
    }
}
