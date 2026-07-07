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

use app\common\model\store\CityArea;
use app\common\model\system\merchant\Merchant;

class AiRecommendRepository
{
    protected $configRepository;
    protected $tagRepository;
    protected $profileRepository;
    protected $discountRepository;
    protected $presenter;

    public function __construct(
        AiConfigRepository $configRepository,
        MerchantTagRepository $tagRepository,
        MerchantProfileRepository $profileRepository,
        MerchantDiscountRepository $discountRepository,
        MerchantPresenter $presenter
    ) {
        $this->configRepository = $configRepository;
        $this->tagRepository = $tagRepository;
        $this->profileRepository = $profileRepository;
        $this->discountRepository = $discountRepository;
        $this->presenter = $presenter;
    }

    public function recommend(array $intentTags, array $location = [], int $uid = 0, int $limit = 0): array
    {
        $limit = $limit ?: $this->configRepository->int('result_limit', (int)config('huimaidan.ai.recall.result_limit', 3));
        $recall = $this->wideRecall($intentTags, $location, $uid);
        if (empty($recall['list'])) {
            return ['count' => 0, 'list' => []];
        }

        $ranked = $this->ruleRank($recall['list'], $intentTags, $location, $limit);
        return [
            'count' => count($ranked),
            'list' => array_slice($ranked, 0, max(1, $limit)),
            'city_relaxed' => $recall['city_relaxed'] ?? false,
            'candidates' => $ranked,
        ];
    }

    /**
     * 宽召回候选商户池
     *
     * 职责：只过滤无效/未启用/无优惠/城市不匹配/排除商户，保留尽可能多的候选，
     * 供 LLM 动态排序使用。复杂偏好（最远、最贵、评分最高等）不在此处硬排。
     */
    public function wideRecall(array $intentTags, array $location = [], int $uid = 0): array
    {
        $maxCandidates = $this->configRepository->int('llm_rerank_candidate_limit', (int)config('huimaidan.ai.rerank.candidate_limit', 50));
        $radius = $this->configRepository->number('recall_radius_km', (float)config('huimaidan.ai.recall.default_radius_km', 5));
        $merchantIds = array_values(array_filter(array_unique(array_map('intval', $this->discountRepository->eligibleMerchantIds()))));

        $matchedIds = $this->tagRepository->matchedMerchantIds($intentTags);
        if ($matchedIds) {
            $merchantIds = array_values(array_intersect($merchantIds, array_map('intval', $matchedIds)));
        }
        $excludeIds = array_values(array_map('intval', (array)($intentTags['exclude_mer_ids'] ?? [])));
        if ($excludeIds) {
            $merchantIds = array_values(array_diff($merchantIds, $excludeIds));
        }
        if (!$merchantIds) {
            return ['count' => 0, 'list' => []];
        }

        // 基础召回：不过滤距离、价格、标签硬匹配，只过滤城市和基础状态
        $queryLimit = $this->candidateQueryLimit($maxCandidates, $intentTags);
        $merchants = $this->queryCandidates($merchantIds, $location, $queryLimit, true, $intentTags);
        $relaxedCity = false;
        if (!$merchants && !empty($location['city_id'])) {
            $merchants = $this->queryCandidates($merchantIds, $location, $queryLimit, false, $intentTags);
            $relaxedCity = !empty($merchants);
        }
        if (!$merchants) {
            return ['count' => 0, 'list' => []];
        }

        $profiles = $this->profileRepository->displayProfiles(array_column($merchants, 'mer_id'));
        $discounts = $this->discountRepository->displayDiscounts(array_column($merchants, 'mer_id'), 0);
        if ($uid > 0) {
            $discounts = array_replace($discounts, $this->discountRepository->displayDiscounts(array_column($merchants, 'mer_id'), $uid));
        }
        $tagsByMerId = $this->tagRepository->byMerchantIds(array_column($merchants, 'mer_id'));
        $cityNames = $this->cityNames(array_column($merchants, 'city_id'));

        $candidates = [];
        foreach ($merchants as $merchant) {
            $merId = (int)$merchant['mer_id'];
            $merchant['city_name'] = $cityNames[(int)($merchant['city_id'] ?? 0)] ?? '';
            $merchant = array_merge($merchant, $profiles[$merId] ?? []);
            if (!empty($intentTags['requires_open_now']) && !$this->isOpenNow($merchant)) {
                continue;
            }
            // 宽召回不再按距离硬过滤，只记录距离用于后续排序
            $distanceKm = $this->distanceForMerchant($merchant, $location);
            $merchantTags = $tagsByMerId[$merId] ?? [];
            $merchant['tags'] = $merchantTags;
            $display = $this->presenter->present($merchant, $discounts[$merId] ?? null, $distanceKm);
            if (!empty($discounts[$merId]['member_discount'])) {
                $display['member_discount'] = (float)$discounts[$merId]['member_discount'];
            }
            $display['tags'] = $merchantTags;
            $display['score'] = 0;
            $display['score_factors'] = [];
            if ($relaxedCity) {
                $display['city_relaxed'] = true;
            }
            $candidates[] = $display;
        }

        return [
            'count' => count($candidates),
            'list' => $candidates,
            'city_relaxed' => $relaxedCity,
        ];
    }

    /**
     * 规则排序（兜底排序）
     */
    public function ruleRank(array $candidates, array $intentTags, array $location = [], int $limit = 0): array
    {
        $limit = $limit ?: $this->configRepository->int('result_limit', (int)config('huimaidan.ai.recall.result_limit', 3));
        $radius = $this->configRepository->number('recall_radius_km', (float)config('huimaidan.ai.recall.default_radius_km', 5));

        $strictRanked = [];
        $softRanked = [];
        foreach ($candidates as $merchant) {
            $merId = (int)$merchant['mer_id'];
            $merchantTags = $merchant['tags'] ?? [];
            $distanceKm = $merchant['distance_km'] ?? $this->distanceForMerchant($merchant, $location);
            $discount = null;
            if (!empty($merchant['member_discount'])) {
                $discount = ['member_discount' => $merchant['member_discount']];
            }
            $hardMatch = $this->hardFilterMatch($merchant, $merchantTags, $intentTags);
            $score = $this->score($merchant, $merchantTags, $discount, $distanceKm, $radius, $intentTags);
            if (!empty($merchant['city_relaxed'])) {
                $score['final'] *= 0.82;
                $score['factors']['city_relaxed'] = 1;
            }
            $merchant['score'] = round($score['final'], 4);
            $merchant['score_factors'] = $score['factors'];
            $merchant['recommend_reason'] = $this->reason($merchant, $score['top_factor'], $intentTags);
            if (!empty($merchant['city_relaxed']) && !empty($merchant['city_name'])) {
                $merchant['recommend_reason'] .= ' 当前城市暂无完全匹配商户，已为你放宽到' . $merchant['city_name'] . '的可用优惠商户。';
            }
            if ($hardMatch) {
                $strictRanked[] = $merchant;
            } else {
                $score['final'] *= 0.3;
                $merchant['score'] = round($score['final'], 4);
                $softRanked[] = $merchant;
            }
        }

        $ranked = $strictRanked;
        if (!$ranked || (count($ranked) < $limit && $this->shouldFillSoftMatches($intentTags))) {
            $ranked = array_merge($strictRanked, $softRanked);
        }
        $this->sortByExplicitIntent($ranked, $intentTags);
        return array_slice($ranked, 0, max(1, $limit));
    }

    protected function shouldFillSoftMatches(array $intentTags): bool
    {
        $action = (string)($intentTags['action'] ?? '');
        $distance = (string)($intentTags['distance'] ?? '');
        $features = array_map('strval', (array)($intentTags['feature'] ?? []));
        return in_array($action, ['expensive', 'cheaper', 'nearer', 'farther'], true)
            || in_array($distance, ['near', 'far'], true)
            || in_array('高评分', $features, true)
            || in_array('口碑好', $features, true);
    }

    protected function sortByExplicitIntent(array &$ranked, array $intentTags): void
    {
        $action = (string)($intentTags['action'] ?? '');
        $distance = (string)($intentTags['distance'] ?? '');
        $features = array_map('strval', (array)($intentTags['feature'] ?? []));
        $price = (string)($intentTags['price'] ?? '');

        if (in_array('高评分', $features, true) || in_array('口碑好', $features, true)) {
            usort($ranked, function (array $left, array $right) {
                return [$this->merchantRating($right), $right['score'] ?? 0] <=> [$this->merchantRating($left), $left['score'] ?? 0];
            });
            return;
        }

        if ($distance === 'far' || $action === 'farther') {
            usort($ranked, function (array $left, array $right) {
                return [$this->merchantDistance($right), $right['score'] ?? 0] <=> [$this->merchantDistance($left), $left['score'] ?? 0];
            });
            return;
        }

        if ($distance === 'near' || $action === 'nearer') {
            usort($ranked, function (array $left, array $right) {
                return [$this->merchantDistance($left), -($left['score'] ?? 0)] <=> [$this->merchantDistance($right), -($right['score'] ?? 0)];
            });
            return;
        }

        if ($action === 'cheaper' || in_array($price, ['0-30', '30-60'], true)) {
            usort($ranked, function (array $left, array $right) {
                return [$this->merchantPerCapita($left), -($left['score'] ?? 0)] <=> [$this->merchantPerCapita($right), -($right['score'] ?? 0)];
            });
            return;
        }

        if ($action === 'expensive') {
            usort($ranked, function (array $left, array $right) {
                return [$this->merchantPerCapita($right), $right['score'] ?? 0] <=> [$this->merchantPerCapita($left), $left['score'] ?? 0];
            });
            return;
        }

        usort($ranked, function (array $left, array $right) {
            return ($right['score'] ?? 0) <=> ($left['score'] ?? 0);
        });
    }

    protected function merchantRating(array $merchant): float
    {
        if (isset($merchant['rating']) && is_numeric($merchant['rating'])) {
            return (float)$merchant['rating'];
        }
        return max(
            (float)($merchant['product_score'] ?? 0),
            (float)($merchant['service_score'] ?? 0),
            (float)($merchant['postage_score'] ?? 0)
        );
    }

    protected function merchantDistance(array $merchant): float
    {
        if (isset($merchant['distance_km']) && is_numeric($merchant['distance_km'])) {
            return (float)$merchant['distance_km'];
        }
        if (!empty($merchant['distance']) && preg_match('/[\\d.]+/', (string)$merchant['distance'], $match)) {
            return (float)$match[0];
        }
        return 999999.0;
    }

    protected function merchantPerCapita(array $merchant): float
    {
        $value = $merchant['per_capita'] ?? 0;
        return is_numeric($value) && (float)$value > 0 ? (float)$value : 999999.0;
    }

    protected function candidateQueryLimit(int $maxCandidates, array $intentTags): int
    {
        if ($this->shouldFillSoftMatches($intentTags)) {
            return max($maxCandidates, min(80, $maxCandidates * 4));
        }
        return $maxCandidates;
    }

    protected function queryCandidates(array $merchantIds, array $location, int $maxCandidates, bool $filterCity, array $intentTags = []): array
    {
        $query = Merchant::getDB()
            ->whereIn('mer_id', $merchantIds)
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('mer_state', 1)
            ->field('mer_id,mer_name,mer_avatar,mer_address,service_phone,mer_phone,category_id,city_id,status,mer_state,long,lat,product_score,service_score,postage_score,sales,is_best,create_time')
            ->with(['categoryName'])
            ->limit($maxCandidates);
        if ($filterCity && !empty($location['city_id'])) {
            $query->where('city_id', (int)$location['city_id']);
        }
        // 有经纬度时按距离升序召回，确保“离我最近”等意图不会遗漏近距离商户
        if (!empty($location['latitude']) && !empty($location['longitude'])) {
            $lat = (float)$location['latitude'];
            $lng = (float)$location['longitude'];
            $direction = ((string)($intentTags['distance'] ?? '') === 'far' || (string)($intentTags['action'] ?? '') === 'farther') ? 'DESC' : 'ASC';
            $query->orderRaw(
                '6371 * ACOS(LEAST(1, GREATEST(-1, COS(RADIANS(?)) * COS(RADIANS(lat)) * COS(RADIANS(`long`) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(lat))))) ' . $direction,
                [$lat, $lng, $lat]
            );
        }
        $query->order('mer_id', 'asc');
        return $query->select()->toArray();
    }

    protected function score(array $merchant, array $merchantTags, ?array $discount, ?float $distanceKm, float $radius, array $intentTags): array
    {
        $weights = $this->configRepository->scoreWeights();
        $tagScore = $this->tagScore($merchantTags, $intentTags);
        $distanceScore = $this->distanceScore($distanceKm, $radius, (string)($intentTags['distance'] ?? ''));
        $sales = (int)($merchant['sales'] ?? 0) + (int)($merchant['configured_sales'] ?? 0);
        $heatScore = min(1, ($sales / 1000) * 0.5 + ((float)($merchant['product_score'] ?? 0) / 5) * 0.3 + (!empty($merchant['is_best']) ? 0.2 : 0));
        $discountRate = (float)($intentTags['discount_rate'] ?? 0);
        $promoScore = $this->promoScore($discount, (float)($merchant['per_capita'] ?? 0), (string)($intentTags['price'] ?? ''), $discountRate);
        $factors = [
            'tag' => round($tagScore, 4),
            'distance' => round($distanceScore, 4),
            'heat' => round($heatScore, 4),
            'promo' => round($promoScore, 4),
        ];
        $final = $factors['tag'] * $weights['tag'] + $factors['distance'] * $weights['distance'] + $factors['heat'] * $weights['heat'] + $factors['promo'] * $weights['promo'];
        $priceFit = $this->priceFitMultiplier((float)($merchant['per_capita'] ?? 0), (string)($intentTags['price'] ?? ''), (string)($intentTags['action'] ?? ''));
        if ($priceFit !== 1.0) {
            $final *= $priceFit;
            $factors['price_fit'] = round($priceFit, 4);
        }
        arsort($factors);
        reset($factors);
        return [
            'final' => $final,
            'factors' => $factors,
            'top_factor' => (string)key($factors),
        ];
    }

    protected function tagScore(array $merchantTags, array $intentTags): float
    {
        $targets = [];
        foreach (['category', 'scene', 'taste', 'facility', 'feature', 'meal', 'promotion'] as $type) {
            if ($type === 'meal' && !empty($intentTags['meal_is_default'])) {
                continue;
            }
            foreach ((array)($intentTags[$type] ?? []) as $value) {
                $targets[$type . ':' . $value] = true;
            }
        }
        if (!empty($intentTags['price'])) {
            foreach ($this->priceTagValues((string)$intentTags['price']) as $priceTag) {
                $targets['price:' . $priceTag] = true;
            }
        }
        if (!$targets) {
            return 0.5;
        }
        $matched = 0;
        $total = count($targets);
        foreach ($merchantTags as $tag) {
            $key = $tag['tag_type'] . ':' . $tag['tag_value'];
            if (isset($targets[$key])) {
                $matched += max(1, (int)($tag['tag_weight'] ?? 10)) / 100;
            }
        }
        return min(1, $matched / max(1, $total * 0.6));
    }

    protected function distanceScore(?float $distanceKm, float $radius, string $distanceIntent): float
    {
        if (is_null($distanceKm)) {
            return 0.3;
        }
        $base = max(0, 1 - ($distanceKm / max($radius, 0.1)));
        if ($distanceIntent === 'far') {
            return 1 - $base;
        }
        return $base;
    }

    protected function promoScore(?array $discount, float $perCapita, string $priceRange, float $targetDiscountRate = 0): float
    {
        $score = is_null($discount) ? 0 : 0.5;
        $memberDiscount = isset($discount['member_discount']) ? (float)$discount['member_discount'] : 0;
        if ($memberDiscount > 0 && $memberDiscount <= 1) {
            $score += max(0, min(0.3, (1 - $memberDiscount) * 2));
        }
        if ($priceRange !== '' && $this->priceMatches($perCapita, $priceRange)) {
            $score += 0.3;
        }
        if ($targetDiscountRate > 0 && $memberDiscount > 0) {
            $diff = abs($memberDiscount - $targetDiscountRate);
            if ($diff <= 0.01) {
                $matchScore = 0.4;
            } elseif ($diff <= 0.05) {
                $matchScore = 0.25;
            } elseif ($diff <= 0.1) {
                $matchScore = 0.12;
            } else {
                $matchScore = 0.02;
            }
            if ($memberDiscount <= $targetDiscountRate) {
                $matchScore += 0.1;
            }
            $score += $matchScore;
        }
        return min(1, $score);
    }

    protected function priceFitMultiplier(float $perCapita, string $priceRange, string $action): float
    {
        if ($action !== 'cheaper' || $perCapita <= 0 || $priceRange === '' || $this->priceMatches($perCapita, $priceRange)) {
            return 1.0;
        }
        $upper = $this->priceUpperBound($priceRange);
        if ($upper <= 0 || $perCapita <= $upper) {
            return 1.0;
        }
        $ratio = $perCapita / $upper;
        if ($ratio >= 2.0) {
            return 0.35;
        }
        if ($ratio >= 1.5) {
            return 0.5;
        }
        if ($ratio >= 1.2) {
            return 0.7;
        }
        return 0.85;
    }

    protected function priceUpperBound(string $range): float
    {
        $range = trim($range);
        $map = [
            '0-30' => 30,
            '30-60' => 60,
            '60-100' => 100,
            '100-150' => 150,
            '150+' => 999999,
        ];
        if (isset($map[$range])) {
            return (float)$map[$range];
        }
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $range, $match)) {
            return (float)$match[2];
        }
        return 0;
    }

    protected function priceMatches(float $amount, string $range): bool
    {
        if ($amount <= 0) {
            return false;
        }
        $map = [
            '0-30' => [0, 30],
            '30-60' => [30, 60],
            '60-100' => [60, 100],
            '100-150' => [100, 150],
            '150+' => [150, 999999],
        ];
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $range, $match)) {
            $target = [(int)$match[1], (int)$match[2]];
            return $amount >= $target[0] && $amount <= $target[1];
        }
        $target = $map[$range] ?? null;
        return $target && $amount >= $target[0] && $amount <= $target[1];
    }

    protected function priceTagValues(string $range): array
    {
        $range = trim($range);
        if ($range === '') {
            return [];
        }
        $known = ['0-30', '30-60', '60-100', '100-150', '150+'];
        if (in_array($range, $known, true)) {
            return [$range];
        }
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $range, $match)) {
            $min = (int)$match[1];
            $max = (int)$match[2];
            return array_values(array_filter($known, function ($item) use ($min, $max) {
                if ($item === '150+') {
                    return $max >= 150;
                }
                [$left, $right] = array_map('intval', explode('-', $item));
                return $right > $min && $left < $max;
            }));
        }
        return [$range];
    }

    protected function hardFilterMatch(array $merchant, array $merchantTags, array $intentTags): bool
    {
        // 价格区间硬过滤：用户明确指定价格区间时，商户人均消费必须落在区间内
        $priceRange = (string)($intentTags['price'] ?? '');
        if ($priceRange === '' && !empty($intentTags['price_range'])) {
            $priceRange = (string)$intentTags['price_range'];
        }
        if ($priceRange !== '') {
            $perCapita = (float)($merchant['per_capita'] ?? 0);
            if ($perCapita <= 0 || !$this->priceMatches($perCapita, $priceRange)) {
                return false;
            }
        }

        // 标签类硬过滤：用户明确指定设施/场景/口味/品类时，商户必须至少匹配一个
        $tagIndex = [];
        foreach ($merchantTags as $tag) {
            $tagIndex[(string)($tag['tag_type'] ?? '') . ':' . (string)($tag['tag_value'] ?? '')] = true;
        }
        foreach (['category', 'scene', 'taste', 'facility', 'feature'] as $type) {
            $values = array_filter((array)($intentTags[$type] ?? []), function ($v) {
                return $v !== '';
            });
            if (!$values) {
                continue;
            }
            $matched = false;
            foreach ($values as $value) {
                if (isset($tagIndex[$type . ':' . $value])) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return false;
            }
        }

        return true;
    }

    protected function reason(array $merchant, string $topFactor, array $intentTags): string
    {
        $distanceIntent = (string)($intentTags['distance'] ?? '');
        if ($topFactor === 'distance') {
            if ($distanceIntent === 'far' && !empty($merchant['distance'])) {
                return '离你约' . $merchant['distance'] . '，距离较远，适合特意去尝尝。';
            }
            if (!empty($merchant['distance'])) {
                return '离你很近，约' . $merchant['distance'] . '，适合现在就去。';
            }
        }
        if ($topFactor === 'promo' && !empty($merchant['discount_label'])) {
            return '当前有' . $merchant['discount_label'] . '优惠，比较符合你要划算的需求。';
        }
        if ($topFactor === 'heat') {
            return '这家店近期人气和评分表现不错，口碑更稳。';
        }
        $tags = [];
        foreach (['category', 'scene', 'taste', 'facility'] as $key) {
            $tags = array_merge($tags, (array)($intentTags[$key] ?? []));
        }
        $tags = array_values(array_filter(array_map([$this, 'intentDisplayLabel'], $tags)));
        if ($tags) {
            return '匹配你提到的' . implode('、', array_slice(array_unique($tags), 0, 3)) . '需求。';
        }
        return '综合距离、优惠和口碑后优先推荐这家。';
    }

    protected function intentDisplayLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $map = [
            'has_private_room' => '包间',
            'private_room' => '包间',
            'has_parking' => '停车方便',
            'parking' => '停车方便',
            'has_baby_chair' => '宝宝椅',
            'baby_chair' => '宝宝椅',
            'has_large_table' => '大桌',
            'large_table' => '大桌',
            'is_non_smoking' => '无烟环境',
            'non_smoking' => '无烟环境',
        ];
        return $map[$value] ?? $value;
    }

    protected function distanceForMerchant(array $merchant, array $location): ?float
    {
        if (empty($location['latitude']) || empty($location['longitude']) || $merchant['lat'] === '' || $merchant['long'] === '' || is_null($merchant['lat']) || is_null($merchant['long'])) {
            return null;
        }
        $lat1 = deg2rad((float)$location['latitude']);
        $lat2 = deg2rad((float)$merchant['lat']);
        $lng1 = deg2rad((float)$location['longitude']);
        $lng2 = deg2rad((float)$merchant['long']);
        $latDiff = $lat2 - $lat1;
        $lngDiff = $lng2 - $lng1;
        return round(2 * asin(sqrt(pow(sin($latDiff / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($lngDiff / 2), 2))) * 6371, 4);
    }

    protected function isOpenNow(array $merchant, ?int $minuteOfDay = null): bool
    {
        if (empty($merchant['status']) || empty($merchant['mer_state'])) {
            return false;
        }
        $hours = $merchant['business_hours'] ?? [];
        if (is_string($hours)) {
            $decoded = json_decode($hours, true);
            $hours = is_array($decoded) ? $decoded : $hours;
        }
        if (!is_array($hours) || !$hours) {
            return true;
        }
        $minuteOfDay = is_null($minuteOfDay) ? ((int)date('G') * 60 + (int)date('i')) : $minuteOfDay;
        $hasParsedRange = false;
        foreach ($hours as $item) {
            $text = is_array($item) ? trim((string)($item['time'] ?? '')) : trim((string)$item);
            if ($text === '') {
                continue;
            }
            foreach ($this->timeRanges($text) as $range) {
                $hasParsedRange = true;
                if ($this->minuteInRange($minuteOfDay, $range[0], $range[1])) {
                    return true;
                }
            }
        }
        return !$hasParsedRange;
    }

    protected function timeRanges(string $text): array
    {
        $text = str_replace(['：', '－', '—', '至', '~'], [':', '-', '-', '-', '-'], $text);
        if (preg_match('/24\s*小时|全天/u', $text)) {
            return [[0, 1440]];
        }
        preg_match_all('/(2[0-3]|[01]?\d)(?::([0-5]\d))?\s*-\s*(2[0-3]|[01]?\d)(?::([0-5]\d))?/u', $text, $matches, PREG_SET_ORDER);
        $ranges = [];
        foreach ($matches as $match) {
            $start = ((int)$match[1]) * 60 + (int)($match[2] ?? 0);
            $end = ((int)$match[3]) * 60 + (int)($match[4] ?? 0);
            if ($start === $end) {
                $ranges[] = [0, 1440];
            } else {
                $ranges[] = [$start, $end];
            }
        }
        return $ranges;
    }

    protected function minuteInRange(int $minute, int $start, int $end): bool
    {
        if ($end >= 1440) {
            return $minute >= $start;
        }
        if ($start < $end) {
            return $minute >= $start && $minute < $end;
        }
        return $minute >= $start || $minute < $end;
    }

    protected function cityNames(array $cityIds): array
    {
        $cityIds = array_values(array_filter(array_unique(array_map('intval', $cityIds))));
        return $cityIds ? CityArea::getDB()->whereIn('id', $cityIds)->where('level', 2)->column('name', 'id') : [];
    }
}
