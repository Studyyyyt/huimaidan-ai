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
use app\common\model\store\CityArea;
use app\common\model\system\merchant\Merchant;
use app\common\model\system\merchant\MerchantCategory;
use app\common\model\system\merchant\StoreGroup;
use app\common\model\system\Relevance;
use think\exception\ValidateException;
use think\facade\Db;

class MerchantDiscoveryRepository
{
    protected $ruleDao;
    protected $presenter;
    protected $discountEngine;
    protected $poolRulePolicy;
    protected $merchantDiscountRepository;
    protected $profileRepository;

    public function __construct(
        DiscountRuleDao $ruleDao,
        MerchantPresenter $presenter,
        DiscountEngineRepository $discountEngine,
        PoolRulePolicy $poolRulePolicy,
        MerchantDiscountRepository $merchantDiscountRepository,
        MerchantProfileRepository $profileRepository
    ) {
        $this->ruleDao = $ruleDao;
        $this->presenter = $presenter;
        $this->discountEngine = $discountEngine;
        $this->poolRulePolicy = $poolRulePolicy;
        $this->merchantDiscountRepository = $merchantDiscountRepository;
        $this->profileRepository = $profileRepository;
    }

    public function getList(array $where, $page, $limit, int $uid = 0): array
    {
        $location = $this->location($where);
        $merchantIds = $this->eligibleMerchantIds();

        $query = $this->merchantQuery($where, $location, $merchantIds);
        $count = $query->count();
        $merchants = $query->page($page, $limit)->select()->toArray();
        $rules = $this->merchantDiscountRepository->displayDiscounts(array_column($merchants, 'mer_id'), $uid);
        $cityNames = $this->cityNames(array_column($merchants, 'city_id'));
        $profiles = $this->profileRepository->displayProfiles(array_column($merchants, 'mer_id'));

        $list = [];
        foreach ($merchants as $merchant) {
            $merchant['city_name'] = $cityNames[$merchant['city_id']] ?? '';
            $merchant = array_merge($merchant, $profiles[(int)$merchant['mer_id']] ?? []);
            $distance = $location && $this->hasCoordinate($merchant['lat']) && $this->hasCoordinate($merchant['long'])
                ? $this->distanceKm((float)$location['lat'], (float)$location['long'], (float)$merchant['lat'], (float)$merchant['long'])
                : null;
            $list[] = $this->presenter->present($merchant, $rules[$merchant['mer_id']] ?? null, $distance);
        }
        return compact('count', 'list');
    }

    public function nearby(array $where, $page, $limit, int $uid = 0): array
    {
        $location = $this->location($where);
        if (!$location) {
            throw new ValidateException('请提供经纬度');
        }
        $where['order'] = 'location';
        return $this->getList($where, $page, $limit, $uid);
    }

    public function detail(int $merId, int $uid = 0, array $location = []): array
    {
        $detail = $this->discountEngine->merchantDetail($merId, $uid);
        $merchant = $this->publicMerchant($detail['merchant']->toArray());
        $merchant = array_merge($merchant, $this->profileRepository->displayProfileByMerId($merId));
        $merchant['city_name'] = $this->cityNames([(int)($merchant['city_id'] ?? 0)])[$merchant['city_id'] ?? 0] ?? '';
        $rules = $this->publicRules(is_array($detail['rules']) ? $detail['rules'] : $detail['rules']->toArray());
        $displayRule = $rules[0] ?? null;

        $distance = $location && !empty($location['latitude']) && !empty($location['longitude'])
            && $this->hasCoordinate($merchant['lat']) && $this->hasCoordinate($merchant['long'])
            ? $this->distanceKm((float)$location['latitude'], (float)$location['longitude'], (float)$merchant['lat'], (float)$merchant['long'])
            : null;

        return [
            'merchant' => $merchant,
            'rules' => $rules,
            'display' => $this->presenter->present($merchant, $displayRule, $distance),
            'branches' => $this->branchStores($merId),
        ];
    }

    public function branchStores(int $merId): array
    {
        if ($merId <= 0) {
            throw new ValidateException('商户ID不能为空');
        }

        $current = Merchant::getDB()
            ->where('mer_id', $merId)
            ->where('is_del', 0)
            ->field('mer_id,business_id')
            ->find();
        if (!$current) {
            throw new ValidateException('商户不存在');
        }

        $businessId = (int)($current['business_id'] ?? 0);
        $query = Merchant::getDB()
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('mer_state', 1);
        if ($businessId > 0) {
            $query->where('business_id', $businessId);
        } else {
            $query->where('mer_id', $merId);
        }

        $merchants = $query
            ->field('mer_id,mer_name,mer_address,service_phone,mer_phone,long,lat,status,mer_state,sort')
            ->order('sort DESC,mer_id ASC')
            ->select()
            ->toArray();

        $profiles = $this->profileRepository->displayProfiles(array_column($merchants, 'mer_id'));
        foreach ($merchants as &$merchant) {
            $merchant = array_merge($merchant, $profiles[(int)$merchant['mer_id']] ?? []);
        }
        unset($merchant);

        return $this->branchStorePayload($merchants, $merId);
    }

    public function categories(): array
    {
        $merchantIds = $this->eligibleMerchantIds();
        if (!$merchantIds) {
            return [];
        }
        $categoryIds = $this->eligibleMerchants($merchantIds)->where('category_id', '>', 0)->column('category_id');
        if (!$categoryIds) {
            return [];
        }
        return MerchantCategory::getDB()->whereIn('merchant_category_id', array_unique($categoryIds))
            ->field('merchant_category_id,category_name')->order('merchant_category_id ASC')->select()->toArray();
    }

    public function cities(): array
    {
        $merchantIds = $this->eligibleMerchantIds();
        if (!$merchantIds) {
            return [];
        }
        $cityIds = $this->eligibleMerchants($merchantIds)->where('city_id', '>', 0)->column('city_id');
        if (!$cityIds) {
            return [];
        }
        return CityArea::getDB()->whereIn('id', array_unique($cityIds))->where('level', 2)
            ->field('id,name,code')->order('id ASC')->select()->toArray();
    }

    public function filters(): array
    {
        return $this->filtersPayload($this->categories(), $this->cities());
    }

    protected function merchantQuery(array $where, ?array $location, array $discountMerchantIds = [])
    {
        $query = Merchant::getDB()
            ->where('is_del', 0)->where('status', 1)->where('mer_state', 1)
            ->when(isset($where['keyword']) && trim((string)$where['keyword']) !== '', function ($query) use ($where) {
                $query->whereLike('mer_name|mer_keyword', '%' . trim((string)$where['keyword']) . '%');
            })
            ->when(isset($where['category_id']) && $where['category_id'] !== '', function ($query) use ($where) {
                $query->where('category_id', (int)$where['category_id']);
            })
            ->when(isset($where['store_group_id']) && $where['store_group_id'] !== '', function ($query) use ($where) {
                $groupId = (int)$where['store_group_id'];
                // 获取该分组及其所有子分组的ID（支持树形层级筛选）
                $groupIds = $this->collectStoreGroupIds($groupId);
                if ($groupIds) {
                    // 通过 relevance 表（type=store_group）查找关联的商户ID
                    $merIds = Relevance::where('type', 'store_group')
                        ->whereIn('left_id', $groupIds)
                        ->column('right_id');
                    if ($merIds) {
                        $query->whereIn('mer_id', array_unique($merIds));
                    } else {
                        // 没有关联商户，返回空结果
                        $query->whereRaw('1 = 0');
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when(isset($where['city_id']) && $where['city_id'] !== '', function ($query) use ($where) {
                $query->where('city_id', (int)$where['city_id']);
            })
            ->field('mer_id,mer_name,mer_avatar,mer_address,service_phone,mer_phone,category_id,city_id,status,mer_state,long,lat,product_score,service_score,postage_score,sales,create_time')
            ->with(['categoryName']);

        $distanceLimit = $this->distanceLimit($where);
        if ($distanceLimit !== null) {
            if (!$location) {
                throw new ValidateException('筛选距离时请提供经纬度');
            }
            $query->whereNotNull('lat')->whereNotNull('long')
                ->whereRaw($this->distanceSql((float)$location['lat'], (float)$location['long']) . ' <= ' . $distanceLimit);
        }

        if (($where['order'] ?? '') === 'location' && $location) {
            $lng = (float)$location['long'];
            $lat = (float)$location['lat'];
            $query->whereNotNull('lat')->whereNotNull('long')
                ->order(Db::raw($this->distanceSql($lat, $lng) . ' ASC'));
        } else {
            $query->order('is_best DESC,sort DESC,create_time DESC');
        }
        // 有惠买单优惠的商户排在前面
        if ($discountMerchantIds) {
            $safeIds = array_map('intval', $discountMerchantIds);
            $query->order(Db::raw('FIELD(mer_id,' . implode(',', $safeIds) . ') DESC'));
        }
        return $query;
    }

    protected function filtersPayload(array $categories, array $cities): array
    {
        return [
            [
                'id' => 1,
                'name' => '附近',
                'key' => 'distance',
                'sort' => 1,
                'options' => [
                    ['id' => 1, 'name' => '附近1km', 'value' => 1],
                    ['id' => 2, 'name' => '附近3km', 'value' => 3],
                    ['id' => 3, 'name' => '附近5km', 'value' => 5],
                    ['id' => 4, 'name' => '附近10km', 'value' => 10],
                ],
            ],
            [
                'id' => 2,
                'name' => '分类',
                'key' => 'category',
                'sort' => 2,
                'options' => array_map(function (array $category) {
                    $id = (int)($category['merchant_category_id'] ?? 0);
                    return [
                        'id' => $id,
                        'name' => (string)($category['category_name'] ?? ''),
                        'value' => $id,
                    ];
                }, $categories),
            ],
            [
                'id' => 3,
                'name' => '城市',
                'key' => 'city',
                'sort' => 3,
                'options' => array_map(function (array $city) {
                    $id = (int)($city['id'] ?? 0);
                    return [
                        'id' => $id,
                        'name' => (string)($city['name'] ?? ''),
                        'value' => $id,
                        'code' => (string)($city['code'] ?? ''),
                    ];
                }, $cities),
            ],
            [
                'id' => 4,
                'name' => '排序',
                'key' => 'sort',
                'sort' => 4,
                'options' => [
                    ['id' => 1, 'name' => '综合排序', 'value' => 'default'],
                    ['id' => 2, 'name' => '距离最近', 'value' => 'location'],
                ],
            ],
        ];
    }

    protected function branchStorePayload(array $merchants, int $currentMerId = 0): array
    {
        $branches = array_values(array_map(function (array $merchant) {
            $branchName = trim((string)($merchant['branch_name'] ?? ''));
            $merchantName = (string)($merchant['mer_name'] ?? '');
            $name = $branchName !== '' ? $branchName : $merchantName;
            return [
                'id' => (int)($merchant['mer_id'] ?? 0),
                'mer_id' => (int)($merchant['mer_id'] ?? 0),
                'name' => $name,
                'mer_name' => $merchantName,
                'branch_name' => $branchName,
                'store_branch_name' => $branchName,
                'address' => (string)($merchant['mer_address'] ?? ''),
                'mer_address' => (string)($merchant['mer_address'] ?? ''),
                'phone' => (string)(($merchant['service_phone'] ?? '') ?: ($merchant['mer_phone'] ?? '')),
                'longitude' => (string)($merchant['long'] ?? ''),
                'latitude' => (string)($merchant['lat'] ?? ''),
            ];
        }, array_filter($merchants, function (array $merchant) {
            return (int)($merchant['mer_id'] ?? 0) > 0;
        })));

        if ($currentMerId > 0) {
            usort($branches, function (array $left, array $right) use ($currentMerId) {
                $leftCurrent = (int)$left['mer_id'] === $currentMerId;
                $rightCurrent = (int)$right['mer_id'] === $currentMerId;
                if ($leftCurrent !== $rightCurrent) {
                    return $leftCurrent ? -1 : 1;
                }
                return (int)$left['mer_id'] <=> (int)$right['mer_id'];
            });
        }

        return $branches;
    }

    protected function distanceLimit(array $where): ?string
    {
        if (!isset($where['distance']) || $where['distance'] === '') {
            return null;
        }
        if (!is_numeric($where['distance']) || (float)$where['distance'] <= 0) {
            throw new ValidateException('距离筛选格式错误');
        }
        return $this->money($where['distance']);
    }

    protected function distanceSql(float $lat, float $lng): string
    {
        return "(2 * 6371 * ASIN(SQRT(POW(SIN(PI() * (`lat` - $lat) / 360), 2) + COS(PI() * $lat / 180) * COS(`lat` * PI() / 180) * POW(SIN(PI() * (`long` - $lng) / 360), 2))))";
    }

    protected function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radLat1 = deg2rad($lat1);
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $latDiff = $radLat2 - $radLat1;
        $lngDiff = $radLng2 - $radLng1;
        $distance = 2 * asin(sqrt(
            pow(sin($latDiff / 2), 2)
            + cos($radLat1) * cos($radLat2) * pow(sin($lngDiff / 2), 2)
        )) * 6371;
        return round($distance, 4);
    }

    protected function eligibleMerchantIds(): array
    {
        return $this->merchantDiscountRepository->eligibleMerchantIds();
    }

    protected function topRules(array $merchantIds): array
    {
        if (!$merchantIds) {
            return [];
        }
        $rules = $this->poolRulePolicy->usableRules($this->ruleDao->search([
            'status' => 1,
            'active_at' => date('Y-m-d H:i:s'),
        ])->whereIn('mer_id', $merchantIds)->select()->toArray());
        $result = [];
        foreach ($rules as $rule) {
            if (!isset($result[$rule['mer_id']])) {
                $result[$rule['mer_id']] = $rule;
            }
        }
        return $result;
    }

    protected function cityNames(array $cityIds): array
    {
        $cityIds = array_values(array_filter(array_unique(array_map('intval', $cityIds))));
        return $cityIds ? CityArea::getDB()->whereIn('id', $cityIds)->where('level', 2)->column('name', 'id') : [];
    }

    protected function eligibleMerchants(array $merchantIds)
    {
        return Merchant::getDB()->whereIn('mer_id', $merchantIds)
            ->where('is_del', 0)->where('status', 1)->where('mer_state', 1);
    }

    protected function location(array $where): ?array
    {
        $hasLatitude = isset($where['latitude']) && $where['latitude'] !== '';
        $hasLongitude = isset($where['longitude']) && $where['longitude'] !== '';
        if (!$hasLatitude && !$hasLongitude) {
            return null;
        }
        if (!$hasLatitude || !$hasLongitude) {
            throw new ValidateException('请同时提供经纬度');
        }
        if (!is_numeric($where['latitude']) || !is_numeric($where['longitude'])) {
            throw new ValidateException('经纬度格式错误');
        }
        $latitude = (float)$where['latitude'];
        $longitude = (float)$where['longitude'];
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new ValidateException('经纬度超出合法范围');
        }
        return [
            'lat' => $latitude,
            'long' => $longitude,
        ];
    }

    protected function hasCoordinate($coordinate): bool
    {
        return !is_null($coordinate) && $coordinate !== '';
    }

    /**
     * 递归收集店铺分组ID（包含自身及所有子分组）
     */
    protected function collectStoreGroupIds(int $groupId): array
    {
        $ids = [$groupId];
        $childIds = StoreGroup::where('pid', $groupId)->column('store_group_id');
        foreach ($childIds as $childId) {
            $ids = array_merge($ids, $this->collectStoreGroupIds((int)$childId));
        }
        return $ids;
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }

    protected function publicMerchant(array $merchant): array
    {
        return array_intersect_key($merchant, array_flip([
            'mer_id', 'mer_name', 'mer_avatar', 'mer_banner', 'mer_info', 'mer_keyword',
            'mer_address', 'service_phone', 'mer_phone', 'category_id', 'city_id', 'status', 'mer_state', 'long', 'lat',
            'product_score', 'service_score', 'postage_score', 'sales', 'create_time',
        ]));
    }

    protected function publicRules(array $rules): array
    {
        $fields = array_flip([
            'rule_id', 'mer_id', 'rule_type', 'title', 'platform_discount',
            'coupon_amount', 'point_ratio', 'member_level', 'member_level_name',
            'member_discount', 'min_amount', 'start_time', 'end_time',
        ]);
        return array_map(function (array $rule) use ($fields) {
            return array_intersect_key($rule, $fields);
        }, $rules);
    }
}
