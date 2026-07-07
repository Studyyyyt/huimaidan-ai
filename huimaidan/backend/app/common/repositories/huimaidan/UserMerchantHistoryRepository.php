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

use app\common\dao\huimaidan\UserMerchantHistoryDao;
use app\common\model\store\CityArea;
use app\common\model\system\merchant\Merchant;
use app\common\model\system\merchant\StoreGroup;
use app\common\model\system\Relevance;
use app\common\repositories\BaseRepository;
use think\exception\ValidateException;
use think\facade\Log;

/**
 * @mixin UserMerchantHistoryDao
 */
class UserMerchantHistoryRepository extends BaseRepository
{
    const MAX_HISTORY = 60;

    protected $presenter;
    protected $merchantDiscountRepository;
    protected $profileRepository;

    public function __construct(
        UserMerchantHistoryDao $dao,
        MerchantPresenter $presenter,
        MerchantDiscountRepository $merchantDiscountRepository,
        MerchantProfileRepository $profileRepository
    ) {
        $this->dao = $dao;
        $this->presenter = $presenter;
        $this->merchantDiscountRepository = $merchantDiscountRepository;
        $this->profileRepository = $profileRepository;
    }

    public function record(int $uid, int $merId): void
    {
        if ($uid <= 0) {
            return;
        }
        try {
            $this->dao->createOrUpdate($uid, $merId);
            $this->dao->pruneOldest($uid, self::MAX_HISTORY);
        } catch (\Throwable $e) {
            Log::error('惠买单店铺浏览历史写入失败：uid=' . $uid . ' mer_id=' . $merId . ' error=' . $e->getMessage());
            throw $e;
        }
    }

    public function getList(int $uid, array $where, $page, $limit): array
    {
        if ($uid <= 0) {
            throw new ValidateException('请登录后查看浏览记录');
        }

        $location = $this->location($where);
        $storeGroupId = $this->requestedStoreGroupId($where);
        $visibleMerIds = $this->visibleMerchantIds($storeGroupId);
        if (!$visibleMerIds) {
            return ['count' => 0, 'list' => []];
        }

        $query = $this->dao->search(['uid' => $uid, 'mer_ids' => $visibleMerIds])
            ->order('last_visit_time DESC,user_merchant_history_id DESC');
        $count = $query->count();
        $histories = $query->page($page, $limit)->select()->toArray();
        if (!$histories) {
            return compact('count') + ['list' => []];
        }

        $merIds = array_column($histories, 'mer_id');
        $merchants = $this->merchantCards($merIds);
        $rules = $this->merchantDiscountRepository->displayDiscounts($merIds, $uid);

        $list = [];
        foreach ($histories as $history) {
            $merId = (int)$history['mer_id'];
            if (!isset($merchants[$merId])) {
                continue;
            }
            $merchant = $merchants[$merId];
            $distance = $location && $this->hasCoordinate($merchant['lat'] ?? null) && $this->hasCoordinate($merchant['long'] ?? null)
                ? getDistance($location['lat'], $location['long'], $merchant['lat'], $merchant['long'])
                : null;
            $shop = $this->presenter->present($merchant, $rules[$merId] ?? null, $distance);
            $list[] = $this->historyPayload($history, $shop);
        }

        return compact('count', 'list');
    }

    public function deleteOne(int $uid, int $historyId): void
    {
        if ($historyId <= 0) {
            throw new ValidateException('浏览记录不存在');
        }
        $history = $this->dao->getWhere([
            'user_merchant_history_id' => $historyId,
            'uid' => $uid,
        ]);
        if (!$history) {
            throw new ValidateException('浏览记录不存在');
        }
        $this->dao->delete($historyId);
    }

    public function deleteBatch(int $uid, array $params): void
    {
        $payload = $this->batchDeletePayload($params);
        if ($payload['clear']) {
            $this->dao->deleteByUid($uid);
            return;
        }

        $ids = $payload['history_ids'];
        if ($this->dao->countByUidAndIds($uid, $ids) !== count($ids)) {
            throw new ValidateException('浏览记录不存在');
        }
        $this->dao->deleteByIdsForUid($uid, $ids);
    }

    protected function visibleMerchantIds(?int $storeGroupId = null): array
    {
        $eligibleIds = $this->merchantDiscountRepository->eligibleMerchantIds();
        if (!$eligibleIds) {
            return [];
        }
        $visibleIds = Merchant::getDB()
            ->whereIn('mer_id', $eligibleIds)
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('mer_state', 1)
            ->column('mer_id');
        $visibleIds = array_values(array_unique(array_map('intval', $visibleIds)));

        if (!$storeGroupId) {
            return $visibleIds;
        }

        $groupIds = $this->collectStoreGroupIds($storeGroupId);
        if (!$groupIds) {
            return [];
        }

        $groupMerIds = Relevance::where('type', 'store_group')
            ->whereIn('left_id', $groupIds)
            ->column('right_id');
        $groupMerIds = array_values(array_unique(array_map('intval', $groupMerIds)));

        return array_values(array_intersect($visibleIds, $groupMerIds));
    }

    protected function merchantCards(array $merIds): array
    {
        $merIds = array_values(array_filter(array_unique(array_map('intval', $merIds))));
        if (!$merIds) {
            return [];
        }

        $rows = Merchant::getDB()
            ->whereIn('mer_id', $merIds)
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('mer_state', 1)
            ->field('mer_id,mer_name,mer_avatar,mer_address,service_phone,category_id,city_id,status,mer_state,long,lat,product_score,service_score,postage_score,sales,create_time')
            ->with(['categoryName'])
            ->select()
            ->toArray();
        $cityNames = $this->cityNames(array_column($rows, 'city_id'));
        $profiles = $this->profileRepository->displayProfiles(array_column($rows, 'mer_id'));

        $result = [];
        foreach ($rows as $row) {
            $merId = (int)$row['mer_id'];
            $row['city_name'] = $cityNames[(int)($row['city_id'] ?? 0)] ?? '';
            $row = array_merge($row, $profiles[$merId] ?? []);
            $result[$merId] = $row;
        }
        return $result;
    }

    protected function cityNames(array $cityIds): array
    {
        $cityIds = array_values(array_filter(array_unique(array_map('intval', $cityIds))));
        return $cityIds ? CityArea::getDB()->whereIn('id', $cityIds)->where('level', 2)->column('name', 'id') : [];
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
            throw new ValidateException('经纬度格式错误');
        }
        return [
            'lat' => $latitude,
            'long' => $longitude,
        ];
    }

    protected function requestedStoreGroupId(array $where): ?int
    {
        if (!isset($where['store_group_id']) || $where['store_group_id'] === '') {
            return null;
        }
        if (!is_numeric($where['store_group_id'])) {
            throw new ValidateException('店铺分组参数格式错误');
        }
        $storeGroupId = (int)$where['store_group_id'];
        if ($storeGroupId <= 0) {
            throw new ValidateException('店铺分组参数格式错误');
        }
        return $storeGroupId;
    }

    protected function collectStoreGroupIds(int $groupId): array
    {
        $ids = [$groupId];
        $childIds = StoreGroup::where('pid', $groupId)->column('store_group_id');
        foreach ($childIds as $childId) {
            $ids = array_merge($ids, $this->collectStoreGroupIds((int)$childId));
        }
        return array_values(array_unique(array_map('intval', $ids)));
    }

    protected function historyPayload(array $history, array $shop): array
    {
        return [
            'history_id' => (int)($history['user_merchant_history_id'] ?? 0),
            'mer_id' => (int)($history['mer_id'] ?? 0),
            'browseTime' => (string)($history['last_visit_time'] ?? ''),
            'visitCount' => (int)($history['visit_count'] ?? 0),
            'shop' => $shop,
        ];
    }

    protected function batchDeletePayload(array $params): array
    {
        $clear = (int)($params['clear'] ?? 0) === 1;
        $ids = $params['history_ids'] ?? [];
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids), function ($id) {
                return trim((string)$id) !== '';
            });
        }
        if (!is_array($ids)) {
            throw new ValidateException('浏览记录参数格式错误');
        }
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $ids = array_values(array_filter($ids, function (int $id): bool {
            return $id > 0;
        }));

        if ($clear && $ids) {
            throw new ValidateException('清空和批量删除不能同时操作');
        }
        if (!$clear && !$ids) {
            throw new ValidateException('请选择要删除的浏览记录');
        }

        return [
            'clear' => $clear,
            'history_ids' => $clear ? [] : $ids,
        ];
    }

    protected function hasCoordinate($coordinate): bool
    {
        return !is_null($coordinate) && $coordinate !== '';
    }
}
