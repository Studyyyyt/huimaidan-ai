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

class MerchantTagInitializerRepository
{
    protected $profileRepository;
    protected $tagRepository;
    protected $discountRepository;
    protected $aiTagRepository;

    public function __construct(
        MerchantProfileRepository $profileRepository,
        MerchantTagRepository $tagRepository,
        MerchantDiscountRepository $discountRepository,
        AiTagRepository $aiTagRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->tagRepository = $tagRepository;
        $this->discountRepository = $discountRepository;
        $this->aiTagRepository = $aiTagRepository;
    }

    public function initialize(int $merId = 0): array
    {
        $query = Merchant::getDB()
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('mer_state', 1)
            ->field('mer_id,mer_name,category_id,sales,is_best,product_score');
        if ($merId > 0) {
            $query->where('mer_id', $merId);
        }
        $merchants = $query->with(['categoryName'])->select()->toArray();
        $profiles = $this->profileRepository->displayProfiles(array_column($merchants, 'mer_id'));
        $discounts = $this->discountRepository->displayBaseDiscounts(array_column($merchants, 'mer_id'));
        $count = 0;
        $tagCount = 0;
        foreach ($merchants as $merchant) {
            $id = (int)$merchant['mer_id'];
            $tags = $this->buildTags($merchant, $profiles[$id] ?? [], $discounts[$id] ?? null);
            $this->tagRepository->replaceAutoTags($id, $tags);
            $count++;
            $tagCount += count($tags);
        }
        return [
            'merchant_count' => $count,
            'tag_count' => $tagCount,
        ];
    }

    public function suggest(int $merId): array
    {
        if ($merId <= 0) {
            return [];
        }
        $merchant = Merchant::getDB()
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('mer_id', $merId)
            ->field('mer_id,mer_name,category_id,sales,is_best,product_score')
            ->with(['categoryName'])
            ->find();
        if (!$merchant) {
            return [];
        }
        $merchant = $merchant->toArray();
        $profile = $this->profileRepository->displayProfileByMerId($merId);
        $discounts = $this->discountRepository->displayBaseDiscounts([$merId]);
        $tags = $this->buildTags($merchant, $profile, $discounts[$merId] ?? null);
        $result = [];
        foreach ($tags as $tag) {
            $key = ($tag['tag_type'] ?? '') . ':' . ($tag['tag_value'] ?? '');
            if ($key === ':' || isset($result[$key])) {
                continue;
            }
            $result[$key] = [
                'tag_type' => (string)$tag['tag_type'],
                'tag_value' => (string)$tag['tag_value'],
                'tag_weight' => (int)($tag['tag_weight'] ?? 10),
                'reason' => $this->suggestReason((string)$tag['tag_type'], (string)$tag['tag_value']),
            ];
        }
        return array_values($result);
    }

    public function buildTags(array $merchant, array $profile, ?array $discount = null): array
    {
        $tags = [];
        $categoryName = (string)($merchant['category_name'] ?? '');
        foreach (['火锅', '川菜', '烧烤', '奶茶', '快餐', '日料', '亲子餐厅'] as $word) {
            if ($categoryName !== '' && mb_strpos($categoryName, $word) !== false) {
                $tags[] = ['tag_type' => 'category', 'tag_value' => $word, 'tag_weight' => 80];
            }
        }
        $perCapita = (float)($profile['per_capita'] ?? 0);
        if ($perCapita > 0) {
            $tags[] = ['tag_type' => 'price', 'tag_value' => $this->priceRange($perCapita), 'tag_weight' => 65];
        }
        $facilities = (array)($profile['facilities'] ?? []);
        $facilityMap = $this->facilityTagMap();
        foreach ($facilityMap as $key => $value) {
            if (!empty($facilities[$key])) {
                $tags[] = ['tag_type' => 'facility', 'tag_value' => $value, 'tag_weight' => 70];
            }
        }
        if ((int)($merchant['sales'] ?? 0) >= 100) {
            $tags[] = ['tag_type' => 'feature', 'tag_value' => '高销量', 'tag_weight' => 60];
        }
        if ((float)($merchant['product_score'] ?? 0) >= 4.5 || !empty($merchant['is_best'])) {
            $tags[] = ['tag_type' => 'feature', 'tag_value' => '高评分', 'tag_weight' => 60];
        }
        $memberDiscount = $discount ? (float)($discount['member_discount'] ?? 0) : 0;
        if ($memberDiscount > 0 && $memberDiscount <= 1) {
            $label = $this->discountLabel($memberDiscount);
            if ($label !== '') {
                $tags[] = ['tag_type' => 'promotion', 'tag_value' => $label, 'tag_weight' => 85];
            }
            if ($memberDiscount <= 0.5) {
                $tags[] = ['tag_type' => 'promotion', 'tag_value' => '5折及以下', 'tag_weight' => 80];
            } elseif ($memberDiscount <= 0.7) {
                $tags[] = ['tag_type' => 'promotion', 'tag_value' => '低折扣', 'tag_weight' => 75];
            }
        }
        $businessHours = json_encode($profile['business_hours'] ?? [], JSON_UNESCAPED_UNICODE);
        if (preg_match('/22:|23:|00:|凌晨|夜宵/u', $businessHours . $categoryName)) {
            $tags[] = ['tag_type' => 'meal', 'tag_value' => 'supper', 'tag_weight' => 65];
            $tags[] = ['tag_type' => 'meal', 'tag_value' => 'late_night', 'tag_weight' => 60];
        }
        return $tags;
    }

    /**
     * 从 AI 标签字典读取启用的设施标签映射（profile key => merchant tag value）。
     * 字典读取失败时返回硬编码兜底。
     */
    protected function facilityTagMap(): array
    {
        try {
            $tags = $this->aiTagRepository->search(['tag_type' => 'facility', 'status' => 1])->select()->toArray();
            $map = [];
            foreach ($tags as $tag) {
                $key = (string)($tag['tag_value'] ?? '');
                $label = (string)($tag['tag_label'] ?? '');
                if ($key !== '' && $label !== '') {
                    $map[$key] = $label;
                }
            }
            if ($map) {
                return $map;
            }
        } catch (\Throwable $e) {
            // 兜底
        }
        return [
            'has_large_table' => '大桌',
            'has_baby_chair' => '宝宝椅',
            'has_private_room' => '包间',
            'is_non_smoking' => '无烟',
        ];
    }

    protected function priceRange(float $amount): string
    {
        if ($amount <= 30) {
            return '0-30';
        }
        if ($amount <= 60) {
            return '30-60';
        }
        if ($amount <= 100) {
            return '60-100';
        }
        if ($amount <= 150) {
            return '100-150';
        }
        return '150+';
    }

    protected function suggestReason(string $type, string $value): string
    {
        $typeLabel = [
            'category' => '来自商户分类',
            'price' => '来自人均消费',
            'facility' => '来自设施标签',
            'feature' => '来自销量/评分/推荐状态',
            'promotion' => '来自惠买单优惠规则',
            'meal' => '来自营业时间',
        ][$type] ?? '来自商户资料';
        return $typeLabel . '：' . $this->displayTagValue($type, $value);
    }

    protected function displayTagValue(string $type, string $value): string
    {
        $labels = [
            'meal' => [
                'breakfast' => '早餐',
                'brunch' => '早午餐',
                'lunch' => '午餐',
                'tea' => '下午茶',
                'dinner' => '晚餐',
                'supper' => '夜宵',
                'late_night' => '深夜食堂',
            ],
            'price' => [
                '0-30' => '人均30元以内',
                '30-60' => '人均30-60元',
                '60-100' => '人均60-100元',
                '100-150' => '人均100-150元',
                '150+' => '人均150元以上',
            ],
        ];
        return $labels[$type][$value] ?? $value;
    }

    /**
     * 把会员折扣率转为中文标签，如 0.80 → "8折"，0.85 → "85折"。
     */
    protected function discountLabel(float $rate): string
    {
        if ($rate <= 0 || $rate > 1) {
            return '';
        }
        $percent = round($rate * 10, 1);
        if ($percent == (int)$percent) {
            return (int)$percent . '折';
        }
        return $percent . '折';
    }
}
