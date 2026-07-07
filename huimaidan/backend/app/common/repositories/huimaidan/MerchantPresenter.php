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

class MerchantPresenter
{
    /**
     * @var AiTagRepository
     */
    protected $tagRepository;

    public function __construct(?AiTagRepository $tagRepository = null)
    {
        $this->tagRepository = $tagRepository ?: (function () {
            try {
                return app()->make(AiTagRepository::class);
            } catch (\Throwable $e) {
                return null;
            }
        })();
    }

    public function present(array $merchant, ?array $rule, $distanceKm): array
    {
        $result = [
            'mer_id' => (int)($merchant['mer_id'] ?? 0),
            'mer_name' => (string)($merchant['mer_name'] ?? ''),
            'mer_avatar' => (string)($merchant['mer_avatar'] ?? ''),
            'mer_address' => (string)($merchant['mer_address'] ?? ''),
            'category_id' => (int)($merchant['category_id'] ?? 0),
            'category_name' => (string)($merchant['category_name'] ?? ''),
            'city_id' => (int)($merchant['city_id'] ?? 0),
            'city_name' => (string)($merchant['city_name'] ?? ''),
            'longitude' => (string)($merchant['long'] ?? ''),
            'latitude' => (string)($merchant['lat'] ?? ''),
            'business_status' => !empty($merchant['status']) && !empty($merchant['mer_state']) ? 1 : 0,
            'business_status_text' => !empty($merchant['status']) && !empty($merchant['mer_state']) ? '营业中' : '休息中',
            'has_discount' => !is_null($rule),
            'discount_label' => $this->discountLabel($rule),
        ];

        $result = array_merge($result, $this->profile($merchant));

        if (!empty($rule['login_required'])) {
            $result['has_discount'] = false;
            $result['login_required'] = 1;
        }

        if (!is_null($distanceKm)) {
            $distanceKm = $this->money($distanceKm);
            $result['distance_km'] = $distanceKm;
            $result['distance'] = $this->distanceText($distanceKm);
        }
        return $result;
    }

    protected function profile(array $merchant): array
    {
        $realSales = max(0, (int)($merchant['sales'] ?? 0));
        $configuredSales = max(0, (int)($merchant['configured_sales'] ?? 0));
        $sales = $realSales + $configuredSales;
        $perCapita = $this->money($merchant['per_capita'] ?? '0.00');
        $facilities = $this->facilities($merchant['facilities'] ?? []);
        return [
            'phone' => (string)(($merchant['service_phone'] ?? '') ?: ($merchant['mer_phone'] ?? '')),
            'rating' => $this->rating($merchant),
            'rating_detail' => [
                'product_score' => $this->score($merchant['product_score'] ?? 0),
                'service_score' => $this->score($merchant['service_score'] ?? 0),
                'postage_score' => $this->score($merchant['postage_score'] ?? 0),
            ],
            'real_sales' => $realSales,
            'configured_sales' => $configuredSales,
            'sales' => $sales,
            'sales_text' => $this->salesText($sales),
            'store' => (string)($merchant['branch_name'] ?? ''),
            'store_branch_name' => (string)($merchant['branch_name'] ?? ''),
            'per_capita' => $this->displayAmount($perCapita),
            'price_per_person' => $this->displayAmount($perCapita),
            'price_per_person_text' => bccomp($perCapita, '0.00', 2) > 0 ? '人均 ¥' . $this->trimMoney($perCapita) : null,
            'business_hours' => $this->arrayValue($merchant['business_hours'] ?? []),
            'facilities' => $facilities,
            'facility_tags' => $this->facilityTags($facilities),
            'promo_image' => (string)($merchant['promo_image'] ?? ''),
            'slogan' => (string)($merchant['slogan'] ?? ''),
            'settled_years' => $this->settledYears($merchant['create_time'] ?? null),
            'settled_years_text' => $this->settledYearsText($merchant['create_time'] ?? null),
        ];
    }

    protected function rating(array $merchant): float
    {
        $scores = [
            $this->score($merchant['product_score'] ?? 0),
            $this->score($merchant['service_score'] ?? 0),
            $this->score($merchant['postage_score'] ?? 0),
        ];
        return round(array_sum($scores) / 3, 1);
    }

    protected function score($score): float
    {
        return round((float)$score, 1);
    }

    protected function salesText(int $sales): ?string
    {
        if ($sales <= 0) {
            return null;
        }
        if ($sales >= 10000) {
            return '半年售' . floor($sales / 10000) . '万+';
        }
        return '半年售' . $sales;
    }

    protected function settledYears($createTime): int
    {
        $timestamp = $this->timestamp($createTime);
        if (!$timestamp) {
            return 0;
        }
        $years = (int)date('Y') - (int)date('Y', $timestamp);
        if (date('md') < date('md', $timestamp)) {
            $years--;
        }
        return max(0, $years);
    }

    protected function settledYearsText($createTime): ?string
    {
        $years = $this->settledYears($createTime);
        return $years > 0 ? '收录' . $years . '年' : null;
    }

    protected function timestamp($value): int
    {
        if (is_numeric($value)) {
            return max(0, (int)$value);
        }
        if (is_string($value) && trim($value) !== '') {
            $time = strtotime($value);
            return $time ? $time : 0;
        }
        return 0;
    }

    protected function facilities($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($value)) {
            $value = [];
        }
        $result = [];
        foreach ($this->facilityLabelMap() as $key => $label) {
            $result[$key] = !empty($value[$key]);
        }
        return $result;
    }

    protected function facilityTags(array $facilities): array
    {
        $labels = $this->facilityLabelMap();
        $tags = [];
        foreach ($labels as $key => $label) {
            if (!empty($facilities[$key])) {
                $tags[] = $label;
            }
        }
        return $tags;
    }

    /**
     * 从 AI 标签字典读取启用的设施标签映射（key => label）。
     * 字典读取失败时使用硬编码兜底，保证展示不中断。
     */
    protected function facilityLabelMap(): array
    {
        if ($this->tagRepository) {
            try {
                $tags = $this->tagRepository->search(['tag_type' => 'facility', 'status' => 1])->select()->toArray();
                $map = [];
                foreach ($tags as $tag) {
                    $value = (string)($tag['tag_value'] ?? '');
                    $label = (string)($tag['tag_label'] ?? '');
                    if ($value !== '' && $label !== '') {
                        $map[$value] = $label;
                    }
                }
                if ($map) {
                    return $map;
                }
            } catch (\Throwable $e) {
                // 兜底
            }
        }
        return [
            'has_large_table' => '大桌',
            'has_baby_chair' => '宝宝椅',
            'has_private_room' => '包间',
            'can_phone_reserve' => '电话预订',
            'is_non_smoking' => '无烟餐厅',
        ];
    }

    protected function arrayValue($value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }
        if (!is_string($value) || trim($value) === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    protected function displayAmount(string $amount)
    {
        if (bccomp($amount, '0.00', 2) <= 0) {
            return 0;
        }
        $trimmed = $this->trimMoney($amount);
        return strpos($trimmed, '.') === false ? (int)$trimmed : (float)$trimmed;
    }

    protected function trimMoney(string $amount): string
    {
        return rtrim(rtrim($amount, '0'), '.');
    }

    protected function discountLabel(?array $rule): ?string
    {
        if (is_null($rule)) {
            return null;
        }
        if (!empty($rule['discount_label'])) {
            return (string)$rule['discount_label'];
        }
        if (isset($rule['member_discount'])) {
            $rate = bcmul((string)$rule['member_discount'], '10', 2);
            return rtrim(rtrim($rate, '0'), '.') . '折';
        }
        switch ((int)($rule['rule_type'] ?? 0)) {
            case 1:
                $rate = bcmul((string)($rule['platform_discount'] ?? '1.00'), '10', 2);
                return rtrim(rtrim($rate, '0'), '.') . '折';
            case 2:
                return '立减¥' . $this->money($rule['coupon_amount'] ?? '0.00');
            case 3:
                return '积分抵扣';
            default:
                return (string)($rule['title'] ?? '优惠');
        }
    }

    protected function distanceText(string $distanceKm): string
    {
        if (bccomp($distanceKm, '0.90', 2) < 0) {
            return max((int)bcmul($distanceKm, '1000', 0), 1) . 'm';
        }
        return $distanceKm . 'km';
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
