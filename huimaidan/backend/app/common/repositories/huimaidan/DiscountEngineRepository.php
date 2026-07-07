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
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\user\UserRepository;
use think\exception\ValidateException;

class DiscountEngineRepository
{
    protected $ruleDao;
    protected $calculator;
    protected $poolRepository;
    protected $merchantRepository;
    protected $poolRulePolicy;
    protected $merchantDiscountRepository;
    protected $memberDiscountCalculator;

    public function __construct(
        DiscountRuleDao $ruleDao,
        DiscountCalculator $calculator,
        PoolRepository $poolRepository,
        MerchantRepository $merchantRepository,
        PoolRulePolicy $poolRulePolicy,
        MerchantDiscountRepository $merchantDiscountRepository,
        MemberDiscountCalculator $memberDiscountCalculator
    ) {
        $this->ruleDao = $ruleDao;
        $this->calculator = $calculator;
        $this->poolRepository = $poolRepository;
        $this->merchantRepository = $merchantRepository;
        $this->poolRulePolicy = $poolRulePolicy;
        $this->merchantDiscountRepository = $merchantDiscountRepository;
        $this->memberDiscountCalculator = $memberDiscountCalculator;
    }

    public function calculate(int $merId, $amount, ?int $uid = null, bool $useMemberDiscount = true): array
    {
        $amount = $this->money($amount);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new ValidateException('消费金额必须大于0');
        }
        $this->assertMerchant($merId);
        if (!$uid) {
            throw new ValidateException('请登录后查看会员优惠');
        }
        $user = app()->make(UserRepository::class)->get($uid);
        if (!$user) {
            throw new ValidateException('用户不存在');
        }
        if ($useMemberDiscount) {
            $discount = $this->merchantDiscountRepository->activeForMember($merId, (int)$user->member_level);
            $result = $this->memberDiscountCalculator->calculate(
                $amount,
                $discount['merchant_discount'],
                $discount['member_discount']
            );
        } else {
            $discount = $this->merchantDiscountRepository->activeForMerchant($merId);
            $result = $this->memberDiscountCalculator->calculateWithoutMemberDiscount(
                $amount,
                $discount['merchant_discount']
            );
        }
        $mode = (int)$this->merchantRepository->search(['mer_id' => $merId])->value('huimaidan_settlement_mode');
        $mode = $mode ?: MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL;
        if ($mode === MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL) {
            $this->poolRepository->ensureUsable($result['pool_id'], $result['merchant_cost_amount'], $merId);
        } elseif (!empty($result['pool_id'])) {
            $this->poolRepository->detail((int)$result['pool_id'], $merId);
        }

        $result['mer_id'] = $merId;
        return $result;
    }

    public function publicCalculate(int $merId, $amount, ?int $uid = null, bool $useMemberDiscount = true): array
    {
        return $this->publicResult($this->calculate($merId, $amount, $uid, $useMemberDiscount));
    }

    public function publicResult(array $result): array
    {
        return array_intersect_key($result, array_flip([
            'mer_id', 'original_amount', 'rule_id', 'discount_type', 'discount_type_label',
            'discount_rule', 'discount_rate', 'member_level', 'member_level_name',
            'member_discount_enabled', 'pay_amount', 'saved_amount',
        ]));
    }

    public function merchantDetail(int $merId, int $uid = 0)
    {
        $merchant = $this->assertMerchant($merId);
        $rules = [];
        $displayRules = $this->merchantDiscountRepository->displayDiscounts([$merId], $uid);
        if (isset($displayRules[$merId]) && empty($displayRules[$merId]['login_required'])) {
            $rules[] = $displayRules[$merId];
        }

        return [
            'merchant' => $merchant,
            'rules' => $rules,
        ];
    }

    protected function assertMerchant(int $merId)
    {
        $merchant = $this->merchantRepository->apiGetOne($merId);
        if (!$merchant) {
            throw new ValidateException('商家不存在或未营业');
        }
        return $merchant;
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
