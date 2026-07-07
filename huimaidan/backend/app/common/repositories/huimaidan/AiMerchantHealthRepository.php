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

use app\common\model\system\merchant\Merchant;
use think\facade\Db;

/**
 * 商户 AI 资料健康检查仓库
 *
 * 用于评估商户资料是否足够参与 AI 推荐，并给出缺失项提示。
 */
class AiMerchantHealthRepository
{
    protected $profileRepository;
    protected $tagRepository;
    protected $discountRepository;
    protected $configRepository;

    public function __construct(
        MerchantProfileRepository $profileRepository,
        MerchantTagRepository $tagRepository,
        MerchantDiscountRepository $discountRepository,
        AiConfigRepository $configRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->tagRepository = $tagRepository;
        $this->discountRepository = $discountRepository;
        $this->configRepository = $configRepository;
    }

    /**
     * 检查单个商户的 AI 完整度
     *
     * @param int $merId 商户ID
     * @return array [
     *   'mer_id' => int,
     *   'mer_name' => string,
     *   'score' => int,        // 0-100
     *   'status' => string,     // excellent|usable|needs_improvement
     *   'status_text' => string,
     *   'missing_items' => array,
     *   'suggestions' => array,
     * ]
     */
    public function checkMerchant(int $merId): array
    {
        $merchant = Merchant::getDB()
            ->where('mer_id', $merId)
            ->where('is_del', 0)
            ->find();
        if (!$merchant) {
            return $this->emptyHealth($merId, '商户不存在');
        }

        $merchant = $merchant->toArray();
        $profile = $this->profileRepository->displayProfileByMerId($merId);
        $tags = $this->tagRepository->byMerchantIds([$merId]);
        $merchantTags = $tags[$merId] ?? [];
        $discount = $this->discountRepository->eligibleMerchantIds();
        $hasDiscount = in_array($merId, $discount, true);

        return $this->evaluateMerchant($merchant, $profile, $merchantTags, $hasDiscount);
    }

    /**
     * 批量检查商户 AI 完整度
     */
    public function checkMerchants(array $merIds, int $limit = 200): array
    {
        $merIds = array_values(array_filter(array_unique(array_map('intval', $merIds))));
        if (!$merIds) {
            return ['count' => 0, 'list' => []];
        }

        $merchants = Merchant::getDB()
            ->whereIn('mer_id', array_slice($merIds, 0, max(1, $limit)))
            ->where('is_del', 0)
            ->field('mer_id,mer_name,real_name,mer_avatar,mer_address,long,lat,category_id,city_id,status,mer_state,sales')
            ->select()
            ->toArray();

        if (!$merchants) {
            return ['count' => 0, 'list' => []];
        }

        $profiles = $this->profileRepository->displayProfiles(array_column($merchants, 'mer_id'));
        $tags = $this->tagRepository->byMerchantIds(array_column($merchants, 'mer_id'));
        $discountIds = $this->discountRepository->eligibleMerchantIds();
        $discountIndex = array_flip($discountIds);

        $list = [];
        foreach ($merchants as $merchant) {
            $merId = (int)$merchant['mer_id'];
            $profile = $profiles[$merId] ?? [];
            $merchantTags = $tags[$merId] ?? [];
            $hasDiscount = isset($discountIndex[$merId]);
            $list[] = $this->evaluateMerchant($merchant, $profile, $merchantTags, $hasDiscount);
        }

        return [
            'count' => count($list),
            'list' => $list,
        ];
    }

    /**
     * 分页查询需要完善的商户
     */
    public function needImprovement(int $page = 1, int $limit = 20, ?int $minScore = null): array
    {
        if (is_null($minScore)) {
            $minScore = $this->configRepository->int('merchant_ai_health_min_score', 70);
        }

        $query = Merchant::getDB()
            ->where('is_del', 0)
            ->where('status', 1)
            ->order('mer_id', 'DESC');

        $count = $query->count();
        $merchants = $query->page($page, $limit)->select()->toArray();
        if (!$merchants) {
            return ['count' => 0, 'list' => []];
        }

        $merIds = array_column($merchants, 'mer_id');
        $profiles = $this->profileRepository->displayProfiles($merIds);
        $tags = $this->tagRepository->byMerchantIds($merIds);
        $discountIds = $this->discountRepository->eligibleMerchantIds();
        $discountIndex = array_flip($discountIds);

        $list = [];
        foreach ($merchants as $merchant) {
            $merId = (int)$merchant['mer_id'];
            $profile = $profiles[$merId] ?? [];
            $merchantTags = $tags[$merId] ?? [];
            $hasDiscount = isset($discountIndex[$merId]);
            $health = $this->evaluateMerchant($merchant, $profile, $merchantTags, $hasDiscount);
            if ($health['score'] < $minScore) {
                $list[] = $health;
            }
        }

        return [
            'count' => $count,
            'list' => $list,
        ];
    }

    /**
     * 分页查询商户 AI 健康状态，可按状态筛选。
     */
    public function listMerchants(int $page = 1, int $limit = 20, string $mode = 'all', ?int $minScore = null): array
    {
        if (is_null($minScore)) {
            $minScore = $this->configRepository->int('merchant_ai_health_min_score', 70);
        }
        $page = max(1, $page);
        $limit = max(1, min(500, $limit));
        $mode = in_array($mode, ['all', 'needs_improvement', 'usable', 'excellent'], true) ? $mode : 'all';

        $query = Merchant::getDB()
            ->where('is_del', 0)
            ->where('status', 1)
            ->order('mer_id', 'DESC');

        $count = $query->count();
        $merchants = $query->page($page, $limit)->select()->toArray();
        if (!$merchants) {
            return ['count' => $count, 'list' => []];
        }

        $merIds = array_column($merchants, 'mer_id');
        $profiles = $this->profileRepository->displayProfiles($merIds);
        $tags = $this->tagRepository->byMerchantIds($merIds);
        $discountIds = $this->discountRepository->eligibleMerchantIds();
        $discountIndex = array_flip($discountIds);

        $list = [];
        foreach ($merchants as $merchant) {
            $merId = (int)$merchant['mer_id'];
            $health = $this->evaluateMerchant(
                $merchant,
                $profiles[$merId] ?? [],
                $tags[$merId] ?? [],
                isset($discountIndex[$merId])
            );
            if ($mode === 'all') {
                $list[] = $health;
                continue;
            }
            if ($mode === 'needs_improvement' && $health['score'] < $minScore) {
                $list[] = $health;
                continue;
            }
            if ($mode !== 'needs_improvement' && $health['status'] === $mode) {
                $list[] = $health;
            }
        }

        return [
            'count' => $count,
            'list' => $list,
        ];
    }

    /**
     * 评估商户 AI 完整度
     */
    protected function evaluateMerchant(array $merchant, array $profile, array $merchantTags, bool $hasDiscount): array
    {
        $merId = (int)$merchant['mer_id'];
        $merName = (string)($merchant['mer_name'] ?: $merchant['real_name']);
        $missing = [];
        $suggestions = [];
        $score = 100;

        // 1. 头像
        if ((int)($merchant['status'] ?? 0) !== 1) {
            $missing[] = '店铺未启用';
            $suggestions[] = '店铺未启用，不会进入 AI 推荐候选池。';
            $score -= 30;
        }

        if ((int)($merchant['mer_state'] ?? 0) !== 1) {
            $missing[] = '未营业';
            $suggestions[] = '店铺营业状态未开启，不会进入 AI 推荐候选池。';
            $score -= 20;
        }

        if (trim((string)($merchant['mer_avatar'] ?? '')) === '') {
            $missing[] = '头像';
            $suggestions[] = '缺少头像，推荐卡片展示效果会受影响。';
            $score -= 10;
        }

        // 2. 地址
        if (trim((string)($merchant['mer_address'] ?? '')) === '') {
            $missing[] = '地址';
            $suggestions[] = '缺少地址，用户无法导航和了解位置。';
            $score -= 15;
        }

        // 3. 经纬度
        if (trim((string)($merchant['long'] ?? '')) === '' || trim((string)($merchant['lat'] ?? '')) === '') {
            $missing[] = '经纬度';
            $suggestions[] = '缺少经纬度，会影响“附近/最远/离我近”类推荐。';
            $score -= 15;
        }

        if (empty($merchant['city_id'])) {
            $missing[] = '城市';
            $suggestions[] = '缺少城市，会影响同城召回，用户定位到城市后可能查不到这家店。';
            $score -= 10;
        }

        // 4. 人均消费
        $perCapita = (float)($profile['per_capita'] ?? 0);
        if ($perCapita <= 0) {
            $missing[] = '人均消费';
            $suggestions[] = '缺少人均消费，会影响“便宜/最贵/预算多少”类推荐。';
            $score -= 10;
        }

        // 5. 营业时间
        $businessHours = $profile['business_hours'] ?? [];
        if (empty($businessHours)) {
            $missing[] = '营业时间';
            $suggestions[] = '缺少营业时间，会影响“现在营业/夜宵/早餐”类推荐。';
            $score -= 10;
        }

        // 6. 设施
        $facilities = $profile['facilities'] ?? [];
        $hasFacility = false;
        foreach ($facilities as $value) {
            if (!empty($value)) {
                $hasFacility = true;
                break;
            }
        }
        if (!$hasFacility) {
            $missing[] = '设施';
            $suggestions[] = '缺少设施，会影响“包间/亲子/停车/聚餐”类推荐。';
            $score -= 5;
        }

        // 7. 分类
        if (empty($merchant['category_id'])) {
            $missing[] = '分类';
            $suggestions[] = '缺少店铺分类，会影响基础召回。';
            $score -= 10;
        }

        // 8. AI 标签
        if (!$merchantTags) {
            $missing[] = 'AI标签';
            $suggestions[] = '缺少 AI 标签，会影响品类、口味、场景召回。';
            $score -= 15;
        }

        // 9. 优惠规则
        if (!$hasDiscount) {
            $missing[] = '优惠规则';
            $suggestions[] = '未配置会员消费折扣，推荐卡片无法展示优惠。';
            $score -= 10;
        }

        $score = max(0, min(100, $score));
        $status = $this->scoreStatus($score);

        return [
            'mer_id' => $merId,
            'mer_name' => $merName,
            'score' => $score,
            'status' => $status,
            'status_text' => $this->statusText($status),
            'missing_items' => $missing,
            'suggestions' => $suggestions,
        ];
    }

    protected function scoreStatus(int $score): string
    {
        if ($score >= 90) {
            return 'excellent';
        }
        if ($score >= 70) {
            return 'usable';
        }
        return 'needs_improvement';
    }

    protected function statusText(string $status): string
    {
        $map = [
            'excellent' => '优秀',
            'usable' => '可用',
            'needs_improvement' => '待完善',
        ];
        return $map[$status] ?? '待完善';
    }

    protected function emptyHealth(int $merId, string $reason): array
    {
        return [
            'mer_id' => $merId,
            'mer_name' => '',
            'score' => 0,
            'status' => 'needs_improvement',
            'status_text' => '待完善',
            'missing_items' => [$reason],
            'suggestions' => [$reason],
        ];
    }
}
