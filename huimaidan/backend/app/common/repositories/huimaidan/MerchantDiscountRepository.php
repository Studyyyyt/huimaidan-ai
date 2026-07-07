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

use app\common\dao\huimaidan\MemberDiscountDao;
use app\common\dao\huimaidan\MerchantDiscountDao;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\config\ConfigValueRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\user\UserBrokerageRepository;
use app\common\repositories\user\UserRepository;
use think\exception\ValidateException;
use think\facade\Db;

/**
 * @mixin MerchantDiscountDao
 */
class MerchantDiscountRepository extends BaseRepository
{
    const CONFIG_DISCOUNT_STACK = 'huimaidan_discount_stack_enabled';

    protected $memberDiscountDao;
    protected $poolRepository;
    protected $memberLevelRepository;
    protected $merchantRepository;

    public function __construct(
        MerchantDiscountDao $dao,
        MemberDiscountDao $memberDiscountDao,
        PoolRepository $poolRepository,
        UserBrokerageRepository $memberLevelRepository,
        MerchantRepository $merchantRepository
    ) {
        $this->dao = $dao;
        $this->memberDiscountDao = $memberDiscountDao;
        $this->poolRepository = $poolRepository;
        $this->memberLevelRepository = $memberLevelRepository;
        $this->merchantRepository = $merchantRepository;
    }

    public function getList(array $where, $page, $limit): array
    {
        $query = $this->dao->search($where);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        $stackEnabled = $this->discountStackEnabled();
        foreach ($list as $item) {
            $this->attachDiscountStackConfig($item, $stackEnabled);
        }
        return compact('count', 'list');
    }

    public function detail(int $discountId, ?int $merId = null)
    {
        $where = ['discount_id' => $discountId];
        if (!is_null($merId)) {
            $where['mer_id'] = $merId;
        }
        $discount = $this->dao->search($where)->find();
        if (!$discount) {
            throw new ValidateException('惠买单折扣配置不存在');
        }
        return $this->attachDiscountStackConfig($discount);
    }

    public function discountStackEnabled(): int
    {
        return $this->normalizeDiscountStackEnabled(systemConfig(self::CONFIG_DISCOUNT_STACK));
    }

    public function saveDiscountStackEnabled($value): void
    {
        app()->make(ConfigValueRepository::class)->setFormData([
            self::CONFIG_DISCOUNT_STACK => $this->normalizeDiscountStackEnabled($value),
        ], 0);
    }

    public function memberLevels(): array
    {
        return $this->activeMemberLevels();
    }

    public function createDiscount(array $data)
    {
        $stackEnabled = array_key_exists(self::CONFIG_DISCOUNT_STACK, $data)
            ? $data[self::CONFIG_DISCOUNT_STACK]
            : null;
        $data = $this->filter($data, false);
        $memberDiscounts = $data['member_discounts'];
        unset($data['member_discounts']);
        return Db::transaction(function () use ($data, $memberDiscounts, $stackEnabled) {
            if ($stackEnabled !== null) {
                $this->saveDiscountStackEnabled($stackEnabled);
            }
            if ((int)($data['status'] ?? 1) === 1) {
                $this->assertNoEnabledConflict((int)$data['mer_id']);
            }
            $discount = $this->dao->create($data);
            $this->replaceMemberDiscounts((int)$discount->discount_id, (int)$discount->mer_id, $memberDiscounts);
            return $this->detail((int)$discount->discount_id);
        });
    }

    public function updateDiscount(int $discountId, array $data)
    {
        $current = $this->detail($discountId);
        $stackEnabled = array_key_exists(self::CONFIG_DISCOUNT_STACK, $data)
            ? $data[self::CONFIG_DISCOUNT_STACK]
            : null;
        $data = $this->filter($data, true, $current->toArray());
        $memberDiscounts = $data['member_discounts'] ?? null;
        unset($data['member_discounts']);
        return Db::transaction(function () use ($discountId, $data, $memberDiscounts, $current, $stackEnabled) {
            if ($stackEnabled !== null) {
                $this->saveDiscountStackEnabled($stackEnabled);
            }
            if ((int)($data['status'] ?? $current->status) === 1) {
                $this->assertNoEnabledConflict((int)($data['mer_id'] ?? $current->mer_id), $discountId);
            }
            if ($data) {
                $this->dao->update($discountId, $data);
            }
            if (is_array($memberDiscounts)) {
                $this->replaceMemberDiscounts($discountId, (int)($data['mer_id'] ?? $current->mer_id), $memberDiscounts);
            }
            return $this->detail($discountId);
        });
    }

    public function changeStatus(int $discountId, int $status): void
    {
        if (!in_array($status, [0, 1], true)) {
            throw new ValidateException('状态有误');
        }
        $discount = $this->detail($discountId);
        if ($status === 1) {
            $this->assertNoEnabledConflict((int)$discount->mer_id, $discountId);
            $this->assertPoolBySettlement((int)$discount->mer_id, (int)$discount->pool_id);
            $this->assertMemberDiscountCoverage($this->memberDiscountDao->byDiscountId($discountId)->select()->toArray());
        }
        $this->dao->update($discountId, ['status' => $status]);
    }

    public function deleteDiscount(int $discountId): void
    {
        $this->detail($discountId);
        Db::transaction(function () use ($discountId) {
            $this->memberDiscountDao->deleteByDiscountId($discountId);
            $this->dao->delete($discountId);
        });
    }

    public function activeForMember(int $merId, int $memberLevel): array
    {
        if ($memberLevel <= 0) {
            throw new ValidateException('当前用户暂无会员等级');
        }
        $discountData = $this->activeMerchantDiscount($merId);
        $memberDiscount = $this->memberDiscountDao->getByDiscountAndLevel((int)$discountData['discount_id'], $memberLevel);
        if (!$memberDiscount) {
            throw new ValidateException('当前会员等级暂无该商家优惠');
        }

        return [
            'merchant_discount' => $discountData,
            'member_discount' => $this->formatMemberDiscount($memberDiscount->toArray()),
        ];
    }

    public function activeForMerchant(int $merId): array
    {
        return [
            'merchant_discount' => $this->activeMerchantDiscount($merId),
        ];
    }

    public function eligibleMerchantIds(): array
    {
        $discounts = $this->dao->search([
            'status' => 1,
            'active_at' => date('Y-m-d H:i:s'),
        ])->select()->toArray();
        // 返回所有有活跃优惠配置的商户，结算模式校验移到下单时
        $ids = [];
        foreach ($discounts as $discount) {
            $ids[] = (int)$discount['mer_id'];
        }
        return array_values(array_unique($ids));
    }

    public function displayDiscounts(array $merIds, int $uid = 0): array
    {
        if (!$merIds) {
            return [];
        }
        if ($uid <= 0) {
            return $this->displayBaseDiscounts($merIds);
        }

        $user = app()->make(UserRepository::class)->get($uid);
        $memberLevel = (int)($user->member_level ?? 0);
        if ($memberLevel <= 0) {
            return [];
        }

        $discounts = $this->dao->search([
            'mer_ids' => $merIds,
            'status' => 1,
            'active_at' => date('Y-m-d H:i:s'),
        ])->select()->toArray();
        $result = [];
        foreach ($discounts as $discount) {
            $mode = $this->settlementMode((int)$discount['mer_id']);
            if (isset($result[$discount['mer_id']]) || ($mode === MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL && (empty($discount['pool']) || (int)$discount['pool']['status'] !== PoolRepository::STATUS_ENABLED))) {
                continue;
            }
            if (!empty($discount['pool']) && (int)$discount['pool']['status'] !== PoolRepository::STATUS_ENABLED) {
                continue;
            }
            $memberDiscountRows = $this->memberDiscountRows($discount);
            if (!$memberDiscountRows) {
                $memberDiscountRows = $this->memberDiscountDao->byDiscountId((int)$discount['discount_id'])->select()->toArray();
            }
            foreach ($memberDiscountRows as $memberDiscount) {
                if ((int)$memberDiscount['member_level'] === $memberLevel && (int)$memberDiscount['status'] === 1) {
                    $memberDiscount = $this->formatMemberDiscount($memberDiscount);
                    $result[(int)$discount['mer_id']] = [
                        'rule_type' => DiscountCalculator::TYPE_DISCOUNT,
                        'member_discount' => $memberDiscount['member_discount'],
                        'member_level' => $memberLevel,
                        'member_level_name' => $memberDiscount['member_level_name'],
                    ];
                    break;
                }
            }
        }
        return $result;
    }

    protected function normalizeDiscountStackEnabled($value): int
    {
        if ($value === '' || $value === null) {
            return 1;
        }
        return in_array($value, [1, '1', true, 'true', 'on'], true) ? 1 : 0;
    }

    protected function attachDiscountStackConfig($discount, ?int $value = null)
    {
        $value = $value ?? $this->discountStackEnabled();
        if (is_object($discount) && method_exists($discount, 'setAttr')) {
            $discount->setAttr(self::CONFIG_DISCOUNT_STACK, $value);
            return $discount;
        }
        if (is_array($discount)) {
            $discount[self::CONFIG_DISCOUNT_STACK] = $value;
        }
        return $discount;
    }

    protected function filter(array $data, bool $partial = false, array $current = []): array
    {
        $fields = [
            'mer_id', 'pool_id', 'merchant_discount', 'status', 'start_time', 'end_time',
            'sort', 'remark', 'member_discounts',
        ];
        $result = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $result[$field] = $data[$field];
            }
        }

        if (!$partial) {
            foreach (['mer_id', 'merchant_discount', 'member_discounts'] as $field) {
                if (!array_key_exists($field, $result)) {
                    throw new ValidateException('参数缺失：' . $field);
                }
            }
            $result += [
                'status' => 1,
                'sort' => 0,
                'remark' => '',
            ];
        }

        $merId = (int)($result['mer_id'] ?? ($current['mer_id'] ?? 0));
        $poolId = (int)($result['pool_id'] ?? ($current['pool_id'] ?? 0));
        if (isset($result['mer_id'])) {
            if (!app()->make(MerchantRepository::class)->get($merId)) {
                throw new ValidateException('商户不存在');
            }
            $result['mer_id'] = $merId;
        }
        if (isset($result['pool_id'])) {
            $this->assertPoolBySettlement($merId, $poolId);
            $result['pool_id'] = $poolId > 0 ? $poolId : null;
        } elseif (!$partial) {
            $this->assertPoolBySettlement($merId, 0);
            $result['pool_id'] = null;
        }
        if (isset($result['merchant_discount'])) {
            $result['merchant_discount'] = $this->rate($result['merchant_discount'], '商家结算折扣有误');
        }
        if (isset($result['status'])) {
            $result['status'] = (int)$result['status'] ? 1 : 0;
        }
        if (isset($result['sort'])) {
            $result['sort'] = (int)$result['sort'];
        }
        foreach (['start_time', 'end_time'] as $field) {
            if (isset($result[$field]) && $result[$field] === '') {
                $result[$field] = null;
            }
        }
        if (isset($result['member_discounts'])) {
            if (!is_array($result['member_discounts'])) {
                throw new ValidateException('会员折扣配置格式错误');
            }
            $result['member_discounts'] = $this->filterMemberDiscounts($result['member_discounts']);
            $this->assertMemberDiscountCoverage($result['member_discounts']);
        }
        return $result;
    }

    protected function replaceMemberDiscounts(int $discountId, int $merId, array $memberDiscounts): void
    {
        $this->memberDiscountDao->deleteByDiscountId($discountId);
        $rows = [];
        foreach ($memberDiscounts as $discount) {
            $rows[] = [
                'discount_id' => $discountId,
                'mer_id' => $merId,
                'member_level' => (int)$discount['member_level'],
                'member_discount' => $this->rate($discount['member_discount'], '会员消费折扣有误'),
                'status' => isset($discount['status']) ? ((int)$discount['status'] ? 1 : 0) : 1,
            ];
        }
        if ($rows) {
            $this->memberDiscountDao->insertAll($rows);
        }
    }

    protected function filterMemberDiscounts(array $memberDiscounts): array
    {
        $rows = [];
        foreach ($memberDiscounts as $discount) {
            $level = (int)($discount['member_level'] ?? 0);
            if ($level <= 0) {
                throw new ValidateException('会员等级有误');
            }
            $rows[$level] = [
                'member_level' => $level,
                'member_discount' => $this->rate($discount['member_discount'] ?? null, '会员消费折扣有误'),
                'status' => isset($discount['status']) ? ((int)$discount['status'] ? 1 : 0) : 1,
            ];
        }
        return array_values($rows);
    }

    protected function assertPool(int $merId, int $poolId): void
    {
        $pool = $this->poolRepository->detail($poolId, $merId);
        if ((int)$pool->status !== PoolRepository::STATUS_ENABLED) {
            throw new ValidateException('垫资池已停用');
        }
    }

    protected function assertPoolBySettlement(int $merId, int $poolId): void
    {
        $mode = (int)app()->make(MerchantRepository::class)->search(['mer_id' => $merId])->value('huimaidan_settlement_mode');
        $mode = $mode ?: MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL;
        if ($mode === MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL && $poolId <= 0) {
            throw new ValidateException('垫资池模式必须绑定已启用垫资池');
        }
        if ($poolId > 0) {
            $this->assertPool($merId, $poolId);
        }
    }

    protected function assertNoEnabledConflict(int $merId, int $exceptId = 0): void
    {
        $query = $this->dao->search(['mer_id' => $merId, 'status' => 1]);
        if ($exceptId > 0) {
            $query->where('discount_id', '<>', $exceptId);
        }
        if ($query->count()) {
            throw new ValidateException('该商户已存在启用中的惠买单折扣配置');
        }
    }

    protected function assertMemberDiscountCoverage(array $memberDiscounts): void
    {
        $missing = $this->missingMemberLevels($this->activeMemberLevels(), $memberDiscounts);
        if ($missing) {
            throw new ValidateException('请配置所有启用用户等级的会员消费折扣：' . implode('、', $missing));
        }
    }

    protected function missingMemberLevels(array $levels, array $memberDiscounts): array
    {
        $configured = [];
        foreach ($memberDiscounts as $discount) {
            if (!isset($discount['status']) || !empty($discount['status'])) {
                $configured[(int)$discount['member_level']] = true;
            }
        }
        $missing = [];
        foreach ($levels as $level) {
            $value = (int)($level['brokerage_level'] ?? 0);
            if ($value > 0 && empty($configured[$value])) {
                $missing[$value] = (string)($level['brokerage_name'] ?? $value);
            }
        }
        return $missing;
    }

    protected function activeMemberLevels(): array
    {
        return $this->memberLevelRepository->all(1)->toArray();
    }

    public function displayBaseDiscounts(array $merIds): array
    {
        $discounts = $this->dao->search([
            'mer_ids' => $merIds,
            'status' => 1,
            'active_at' => date('Y-m-d H:i:s'),
        ])->select()->toArray();
        $result = [];
        foreach ($discounts as $discount) {
            $merId = (int)$discount['mer_id'];
            if (isset($result[$merId])) {
                continue;
            }
            $mode = $this->settlementMode($merId);
            if ($mode === MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL && (empty($discount['pool']) || (int)$discount['pool']['status'] !== PoolRepository::STATUS_ENABLED)) {
                continue;
            }
            if (!empty($discount['pool']) && (int)$discount['pool']['status'] !== PoolRepository::STATUS_ENABLED) {
                continue;
            }
            $memberDiscountRows = $this->memberDiscountDao->byDiscountId((int)$discount['discount_id'])->select()->toArray();
            foreach ($memberDiscountRows as $memberDiscount) {
                if ((int)$memberDiscount['member_level'] === 1 && (int)$memberDiscount['status'] === 1) {
                    $result[$merId] = [
                        'rule_type' => DiscountCalculator::TYPE_DISCOUNT,
                        'member_discount' => $memberDiscount['member_discount'],
                    ];
                    break;
                }
            }
        }
        return $result;
    }

    protected function activeMerchantDiscount(int $merId): array
    {
        $discount = $this->dao->search([
            'mer_id' => $merId,
            'status' => 1,
            'active_at' => date('Y-m-d H:i:s'),
        ])->find();
        $discountData = $discount ? $discount->toArray() : [];
        $settlementMode = $this->settlementMode($merId);
        if (!$discountData) {
            throw new ValidateException('该商家暂无可用惠买单优惠');
        }
        if ($settlementMode === MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL && (empty($discountData['pool']) || (int)$discountData['pool']['status'] !== PoolRepository::STATUS_ENABLED)) {
            throw new ValidateException('该商家暂无可用惠买单优惠');
        }
        if (!empty($discountData['pool']) && (int)$discountData['pool']['status'] !== PoolRepository::STATUS_ENABLED) {
            throw new ValidateException('垫资池已停用');
        }
        return $discountData;
    }

    protected function settlementMode(int $merId): int
    {
        $mode = (int)app()->make(MerchantRepository::class)->search(['mer_id' => $merId])->value('huimaidan_settlement_mode');
        return $mode ?: MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL;
    }

    protected function formatMemberDiscount(array $memberDiscount): array
    {
        $level = $memberDiscount['level'] ?? [];
        $memberDiscount['member_level_name'] = (string)($level['brokerage_name'] ?? '');
        return $memberDiscount;
    }

    protected function memberDiscountRows(array $discount): array
    {
        return $discount['memberDiscounts'] ?? ($discount['member_discounts'] ?? []);
    }

    protected function rate($rate, string $message): string
    {
        $rate = number_format(round((float)$rate, 2), 2, '.', '');
        if (bccomp($rate, '0.00', 2) <= 0 || bccomp($rate, '1.00', 2) > 0) {
            throw new ValidateException($message);
        }
        return $rate;
    }
}
