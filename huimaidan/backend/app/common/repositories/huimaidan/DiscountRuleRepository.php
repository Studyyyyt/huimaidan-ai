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

namespace app\common\repositories\huimaidan;

use app\common\dao\huimaidan\DiscountRuleDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use think\exception\ValidateException;

/**
 * @mixin DiscountRuleDao
 */
class DiscountRuleRepository extends BaseRepository
{
    protected $poolRepository;
    protected $poolRulePolicy;

    public function __construct(DiscountRuleDao $dao, PoolRepository $poolRepository, PoolRulePolicy $poolRulePolicy)
    {
        $this->dao = $dao;
        $this->poolRepository = $poolRepository;
        $this->poolRulePolicy = $poolRulePolicy;
    }

    public function getList(?int $merId, array $where, $page, $limit)
    {
        if (!is_null($merId)) {
            $where['mer_id'] = $merId;
        }
        $query = $this->dao->search($where);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

    public function createRule(int $merId, array $data)
    {
        $data = $this->filter($data, false, $merId);
        $this->assertPoolBinding($merId, $data);
        $data['mer_id'] = $merId;
        return $this->dao->create($data);
    }

    public function updateRule(int $merId, int $ruleId, array $data)
    {
        if (!$this->dao->merHas($merId, $ruleId)) {
            throw new ValidateException('优惠规则不存在');
        }
        $current = $this->dao->get($ruleId);
        $data = $this->filter($data, true, $merId);
        $this->assertPoolBinding($merId, array_merge($current->toArray(), $data));
        $this->dao->update($ruleId, $data);
        return $this->dao->get($ruleId);
    }

    public function deleteRule(int $merId, int $ruleId)
    {
        if (!$this->dao->merHas($merId, $ruleId)) {
            throw new ValidateException('优惠规则不存在');
        }
        $this->dao->update($ruleId, ['is_del' => 1]);
    }

    public function changeStatus(int $merId, int $ruleId, int $status)
    {
        if (!in_array($status, [0, 1], true)) {
            throw new ValidateException('优惠规则状态有误');
        }
        if (!$this->dao->merHas($merId, $ruleId)) {
            throw new ValidateException('优惠规则不存在');
        }
        if ($status === 1) {
            $this->assertPoolBinding($merId, $this->dao->get($ruleId)->toArray());
        }
        $this->dao->update($ruleId, ['status' => $status]);
    }

    protected function filter(array $data, bool $partial = false, ?int $merId = null): array
    {
        $fields = [
            'title', 'pool_id', 'rule_type', 'platform_discount', 'merchant_cost', 'coupon_amount',
            'point_ratio', 'min_amount', 'status', 'sort', 'start_time', 'end_time'
        ];
        $res = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $res[$field] = $data[$field];
            }
        }
        if (!$partial) {
            $res += [
                'platform_discount' => '1.00',
                'merchant_cost' => '1.00',
                'coupon_amount' => '0.00',
                'point_ratio' => '0.00',
                'min_amount' => '0.00',
                'status' => 1,
                'sort' => 0,
            ];
        }

        if (isset($res['pool_id']) && ($res['pool_id'] === '' || (int)$res['pool_id'] <= 0)) {
            $res['pool_id'] = null;
        }
        if (isset($res['pool_id']) && $res['pool_id'] && !is_null($merId)) {
            $this->poolRepository->detail((int)$res['pool_id'], $merId);
        }
        foreach (['platform_discount', 'merchant_cost', 'coupon_amount', 'point_ratio', 'min_amount'] as $money) {
            if (isset($res[$money])) {
                $res[$money] = $this->money($res[$money]);
            }
        }
        if (isset($res['rule_type'])) {
            $res['rule_type'] = (int)$res['rule_type'];
            if (!in_array($res['rule_type'], [DiscountCalculator::TYPE_DISCOUNT, DiscountCalculator::TYPE_COUPON, DiscountCalculator::TYPE_POINTS], true)) {
                throw new ValidateException('优惠规则类型有误');
            }
        }
        if (isset($res['platform_discount']) && bccomp($res['platform_discount'], '0.00', 2) <= 0) {
            throw new ValidateException('平台折扣必须大于0');
        }
        if (isset($res['merchant_cost']) && bccomp($res['merchant_cost'], '0.00', 2) <= 0) {
            throw new ValidateException('商家底价折扣必须大于0');
        }
        if (isset($res['platform_discount'], $res['merchant_cost']) && bccomp($res['merchant_cost'], $res['platform_discount'], 2) > 0) {
            throw new ValidateException('商家底价折扣不能高于平台折扣');
        }
        if (isset($res['status'])) {
            $res['status'] = (int)$res['status'] ? 1 : 0;
        }
        if (isset($res['sort'])) {
            $res['sort'] = (int)$res['sort'];
        }
        foreach (['start_time', 'end_time'] as $field) {
            if (isset($res[$field]) && $res[$field] === '') {
                $res[$field] = null;
            }
        }
        return $res;
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }

    protected function assertPoolBinding(int $merId, array $data): void
    {
        $mode = (int)app()->make(MerchantRepository::class)->search(['mer_id' => $merId])->value('huimaidan_settlement_mode');
        $mode = $mode ?: MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL;
        $poolId = (int)($data['pool_id'] ?? 0);
        if ($mode === MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL) {
            $poolId = $this->poolRulePolicy->poolId($poolId ?: null);
        } elseif ($poolId <= 0) {
            return;
        }
        $pool = $this->poolRepository->detail($poolId, $merId);
        if ((int)$pool->status !== PoolRepository::STATUS_ENABLED) {
            throw new ValidateException('垫资池已停用');
        }
    }
}
