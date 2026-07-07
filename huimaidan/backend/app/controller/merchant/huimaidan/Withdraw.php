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

use app\common\repositories\huimaidan\WithdrawRepository;
use crmeb\basic\BaseController;
use think\App;

class Withdraw extends BaseController
{
    protected $repository;

    public function __construct(App $app, WithdrawRepository $repository)
    {
        parent::__construct($app);
        $this->repository = $repository;
    }

    public function overview()
    {
        return app('json')->success($this->repository->overview($this->request->merId()));
    }

    public function current()
    {
        return app('json')->success($this->repository->currentData($this->request->merId()));
    }

    public function records()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date', 'status', 'financial_status', 'account_type']);
        return app('json')->success($this->repository->records($this->request->merId(), $where, $page, $limit));
    }

    /**
     * 提现记录列表（格式化版本）
     * GET /mer/huimaidan/settlement/withdraw/list
     */
    public function list()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['date', 'status', 'account_type']);
        return app('json')->success($this->repository->list($this->request->merId(), $where, $page, $limit));
    }

    public function account()
    {
        $data = $this->request->params(['financial_type', 'name', 'wechat', 'wechat_code', 'alipay', 'alipay_code', 'code_image']);
        // 兼容前端字段名：code_image -> wechat_code / alipay_code
        if (!empty($data['code_image']) && empty($data['wechat_code']) && empty($data['alipay_code'])) {
            $financialType = (int)($data['financial_type'] ?? 0);
            if ($financialType === 2) {
                $data['wechat_code'] = $data['code_image'];
            } elseif ($financialType === 3) {
                $data['alipay_code'] = $data['code_image'];
            }
        }
        $this->repository->saveAccount($this->request->merId(), $data);
        return app('json')->success('保存成功');
    }

    public function apply()
    {
        $data = $this->request->params(['extract_money', 'financial_type', 'mark']);
        $data['mer_admin_id'] = $this->request->adminId();
        $this->repository->apply($this->request->merId(), $data);
        return app('json')->success('申请成功');
    }
}
