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


namespace app\common\repositories\user;

use app\common\dao\user\UserRelationDao as dao;
use app\common\model\user\UserRelation;
use app\common\repositories\BaseRepository;
use app\common\repositories\huimaidan\MerchantDiscountRepository;
use app\common\repositories\huimaidan\MerchantPresenter;
use app\common\repositories\huimaidan\MerchantProfileRepository;
use app\common\repositories\store\product\ProductAssistRepository;
use app\common\repositories\store\product\ProductGroupRepository;
use app\common\repositories\store\product\ProductPresellRepository;
use app\common\repositories\store\product\ProductRepository;
use app\common\repositories\store\product\SpuRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\model\system\merchant\Merchant;
use app\common\model\system\merchant\StoreGroup;
use app\common\model\system\Relevance;
use think\exception\ValidateException;
use think\facade\Db;

/**
 * Class UserRelationRepository
 * @package app\common\repositories\user
 * @mixin dao
 */
class UserRelationRepository extends BaseRepository
{

    protected $dao;

    /** @var MerchantProfileRepository|null 收藏列表展示用缓存实例 */
    protected $_collectionProfiles;
    /** @var MerchantDiscountRepository|null 收藏列表展示用缓存实例 */
    protected $_collectionDiscounts;
    /** @var MerchantPresenter|null 收藏列表展示用缓存实例 */
    protected $_collectionPresenter;

    /**
     * UserRelationRepository constructor.
     * @param dao $dao
     */
    public function __construct(dao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 检查特定类型的商品或服务是否存在。
     *
     * 本函数根据传入的类型参数，决定检查哪种类型的商品或服务是否存在。
     * 支持的类型包括普通商品、秒杀商品、预售商品、助力商品、拼团商品和商铺。
     * 通过调用相应类型的仓库方法来执行存在性检查。
     *
     * @param array $params 包含类型和类型ID的参数数组。
     * @param int $params['type'] 商品或服务的类型，对应不同的业务实体。
     * @param int $params['type_id'] 商品或服务的类型ID，用于特定类型的存在性检查。
     * @return bool|mixed 如果类型有效并找到对应的商品或服务，则返回true或相关对象；否则返回false。
     */
    public function fieldExists($params)
    {
        switch ($params['type']) {
            case 0: // 普通商品
            case 1: // 秒杀商品
                // 对于类型0和1，使用ProductRepository来检查商品是否存在
                return app()->make(ProductRepository::class)->apiExists(0, $params['type_id']);
                break;
            case 2: // 预售商品
                // 使用ProductPresellRepository来检查预售商品是否存在
                return app()->make(ProductPresellRepository::class)->getWhereCount(['product_presell_id' => $params['type_id']]);
                break;
            case 3: // 助力商品
                // 使用ProductAssistRepository来检查助力商品是否存在
                return app()->make(ProductAssistRepository::class)->getWhereCount(['product_assist_id' => $params['type_id']]);
                break;
            case 4: // 拼团商品
                // 使用ProductGroupRepository来检查拼团商品是否存在
                return app()->make(ProductGroupRepository::class)->getWhereCount(['product_group_id' => $params['type_id']]);
                break;
            case 10: // 商铺
                // 使用MerchantRepository来检查商铺是否存在
                return app()->make(MerchantRepository::class)->apiGetOne($params['type_id']);
                break;
            default:
                // 如果类型无效，返回false
                return false;
                break;
        }
    }

    /**
     * 检查用户与特定SPU的关系是否存在
     *
     * 本函数用于确定给定用户是否与特定类型的SPU存在某种关系。这可以通过检查用户ID是否出现在API字段中来实现。
     * 参数中的$type用于指定关系的类型，而$type_id则用于指定特定SPU的ID。
     *
     * @param array $params 包含关系类型和其他相关参数的数组
     * @param int $uid 用户的ID
     * @return bool 如果用户与SPU的关系存在，则返回true；否则返回false。
     */
    public function getUserRelation(array $params, int $uid)
    {
        // 检查传入的类型是否在允许的范围内
        if (in_array($params['type'], [0, 1, 2, 3, 4])) {
            // 获取SPU信息
            $spu = $this->getSpu($params);
            // 更新参数数组，以使用SPU的ID和类型1
            $params['type_id'] = $spu['spu_id'];
            $params['type'] = 1;
        }
        // 返回用户关系是否存在，通过检查特定字段在数据库中的数量是否大于0来确定
        return ($this->dao->apiFieldExists('type_id', $params['type_id'], $params['type'], $uid)->count()) > 0;
    }

    public function merchantCollectionCheck(int $uid, int $merId): array
    {
        return $this->collectionCheckPayload($this->getUserRelation([
            'type_id' => $merId,
            'type' => 10,
        ], $uid));
    }

    protected function collectionCheckPayload(bool $isCollected): array
    {
        return ['isCollected' => $isCollected];
    }

    /**
     * 根据条件搜索信息，并分页返回结果。
     *
     * 此方法用于根据给定的条件数组$where，从数据库中搜索相关信息。
     * 它支持两种类型的信息搜索：SPU类型和商家类型。搜索结果将根据创建时间降序排列。
     * 方法返回一个包含搜索结果总数和分页后的结果列表的数组。
     *
     * @param array $where 搜索条件数组，包含各种过滤条件。
     * @param int $page 当前页码，用于分页。
     * @param int $limit 每页显示的记录数，用于分页。
     * @return array 返回一个包含总数和列表的数组。
     */
    public function search(array $where, int $page, int $limit)
    {
        // 初始化关联加载数组
        $with = [];

        // 当搜索类型为1时，加载SPU关联数据
        if($where['type'] == 1) {
            $with = [
                'spu'
            ];
        }

        // 当搜索类型为10时，加载商家关联数据，并指定加载的字段（扩展展示所需字段）
        if($where['type'] == 10) {
            $with = [
                'merchant' => function($query){
                    $query->field('mer_id,type_id,mer_name,mer_avatar,mer_address,service_phone,mer_phone,category_id,city_id,status,mer_state,long,lat,product_score,service_score,postage_score,sales,mer_info,care_count,is_del,create_time')
                        ->with(['categoryName']);
                }
            ];
        }

        if ($where['type'] == 10) {
            $storeGroupId = $this->requestedStoreGroupId($where);
            if ($storeGroupId !== null) {
                $groupIds = $this->collectStoreGroupIds($storeGroupId);
                $merIds = $groupIds
                    ? Relevance::where('type', 'store_group')->whereIn('left_id', $groupIds)->column('right_id')
                    : [];
                $where['type_ids'] = $merIds ? array_values(array_unique(array_map('intval', $merIds))) : [-1];
            }
        }

        // 初始化查询
        $query = $this->dao->search($where);

        // 关联加载
        $query->with($with);

        // 收藏商户（type=10）：处理位置筛选（距离筛选 + 按距离排序）
        $location = null;
        if ($where['type'] == 10) {
            $location = $this->collectionLocation($where);
            $this->applyCollectionLocationQuery($query, $where, $location);
        }

        // 先计算总记录数（必须在设置 order 之前：count 不需要 order，
        // 且距离排序字段 collection_distance 是 field 别名，count 会把 field
        // 替换成 COUNT(*) 导致别名丢失，若 order 提前设置会触发 "Unknown column"）
        $query->removeOption('order');
        $count = $query->count();

        // 排序：选了"距离最近"且有坐标时按距离升序，否则保持收藏时间倒序
        // create_time 在 user_relation 和 merchant 表都存在，JOIN 后必须带主表前缀
        $relationTable = UserRelation::getDB()->getTable();
        if ($location && ($where['order'] ?? '') === 'location') {
            $query->order('collection_distance ASC');
        } else {
            $query->order("{$relationTable}.create_time DESC");
        }

        // 进行分页查询
        $list = $query->page($page, $limit)->select();

        // 遍历结果集，处理特定类型的数据显示
        if ($where['type'] == 10) {
            // 商家类型：批量获取 profile、折扣规则，用 MerchantPresenter 格式化展示数据
            if (!isset($this->_collectionProfiles)) {
                $this->_collectionProfiles = app()->make(MerchantProfileRepository::class);
                $this->_collectionDiscounts = app()->make(MerchantDiscountRepository::class);
                $this->_collectionPresenter = app()->make(MerchantPresenter::class);
            }

            // 收集所有商户ID
            $allMerIds = [];
            foreach ($list as $item) {
                if (isset($item['merchant']) && $item['merchant']) {
                    $allMerIds[] = (int)($item['merchant']['mer_id'] ?? 0);
                }
            }
            $allMerIds = array_values(array_unique(array_filter($allMerIds)));

            // 批量查询
            $allProfiles = $this->_collectionProfiles->displayProfiles($allMerIds);
            $uid = (int)($where['uid'] ?? 0);
            $allRules = $this->_collectionDiscounts->displayDiscounts($allMerIds, $uid);

            // 格式化每个商户
            foreach ($list as &$item) {
                if (isset($item['merchant']) && $item['merchant']) {
                    $merchant = $item['merchant']->toArray();
                    $merId = (int)($merchant['mer_id'] ?? 0);
                    $merchant = array_merge($merchant, $allProfiles[$merId] ?? []);
                    $distanceKm = $this->collectionDistanceKm($location, $merchant);
                    $item['merchant'] = $this->_collectionPresenter->present($merchant, $allRules[$merId] ?? null, $distanceKm);
                }
            }
            unset($item);
        } else {
            foreach ($list as &$item) {
                if ($item['type'] == 1) {
                    if (isset($item['spu']['product_type']) && $item['spu']['product_type'] == 1) {
                        $item['spu']['stop_time'] = $item->stop_time;
                        $item['spu']['status'] = 1;
                        unset($item['spu']['seckillActive']);
                    }
                }
            }
            unset($item);
        }

        // 返回计算后的总数和处理后的列表
        return compact('count', 'list');
    }

    /**
     * 解析收藏列表请求中的经纬度（对齐首页 MerchantDiscoveryRepository::location）
     * 任一经纬度缺失则视为未提供位置；二者其一缺失或格式非法则抛错。
     */
    protected function collectionLocation(array $where): ?array
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

    /**
     * 解析距离筛选值（km），对齐首页 distanceLimit()
     */
    protected function collectionDistanceLimit(array $where): ?string
    {
        if (!isset($where['distance']) || $where['distance'] === '') {
            return null;
        }
        if (!is_numeric($where['distance']) || (float)$where['distance'] <= 0) {
            throw new ValidateException('距离筛选格式错误');
        }
        return number_format(round((float)$where['distance'], 2), 2, '.', '');
    }

    /**
     * 收藏列表距离筛选/排序查询注入：
     * INNER JOIN merchant 表（只保留有效商户），并按需追加距离筛选 whereRaw、
     * 预计算距离字段 collection_distance 供排序使用。
     */
    protected function applyCollectionLocationQuery($query, array $where, ?array $location): void
    {
        // 仅当存在距离筛选或按距离排序时才需要 JOIN merchant 表
        $needDistanceFilter = isset($where['distance']) && $where['distance'] !== '';
        $needDistanceOrder = $location && ($where['order'] ?? '') === 'location';
        if (!$needDistanceFilter && !$needDistanceOrder) {
            return;
        }

        $distanceLimit = $this->collectionDistanceLimit($where);
        if ($distanceLimit !== null && !$location) {
            // 对齐首页：距离筛选必须提供经纬度
            throw new ValidateException('筛选距离时请提供经纬度');
        }

        // 收藏记录主表完整表名（含前缀），用于 JOIN 条件与字段引用
        $relationTable = UserRelation::getDB()->getTable();
        $merTable = Merchant::getDB()->getTable();
        $query->join([$merTable => 'mer'], "mer.mer_id = `{$relationTable}`.type_id", 'INNER');
        // 只保留有效商户，与首页列表基底保持一致
        $query->where('mer.is_del', 0)->where('mer.status', 1)->where('mer.mer_state', 1);
        // JOIN 后显式限定主表字段，避免 SELECT * 时 merchant 同名列覆盖收藏记录字段
        $query->field("`{$relationTable}`.*");

        // 距离筛选：排除无坐标商户，仅保留指定范围内的
        if ($distanceLimit !== null && $location) {
            $query->whereNotNull('mer.lat')->whereNotNull('mer.long')
                ->whereRaw($this->collectionDistanceSql('mer.lat', 'mer.long', $location['lat'], $location['long']) . ' <= ' . $distanceLimit);
        }

        // 按距离排序时预计算距离字段，并排除无坐标商户（无坐标无法排序）
        if ($needDistanceOrder) {
            $query->whereNotNull('mer.lat')->whereNotNull('mer.long')
                ->field(Db::raw($this->collectionDistanceSql('mer.lat', 'mer.long', $location['lat'], $location['long']) . ' AS collection_distance'));
        }
    }

    /**
     * 半正矢距离 SQL（km），对齐首页 MerchantDiscoveryRepository::distanceSql，
     * 区别在于 lat/long 通过参数化列名引用（收藏列表用 JOIN 别名 mer.lat/mer.long）
     */
    protected function collectionDistanceSql(string $latField, string $lngField, float $lat, float $lng): string
    {
        return "(2 * 6371 * ASIN(SQRT(POW(SIN(PI() * ({$latField} - {$lat}) / 360), 2) + COS(PI() * {$lat} / 180) * COS({$latField} * PI() / 180) * POW(SIN(PI() * ({$lngField} - {$lng}) / 360), 2))))";
    }

    /**
     * 收藏列表展示距离：有定位且有商户坐标时返回 km 数，否则返回 null
     */
    protected function collectionDistanceKm(?array $location, array $merchant)
    {
        if (!$location) {
            return null;
        }
        $lat = $merchant['lat'] ?? null;
        $lng = $merchant['long'] ?? null;
        if ($lat === null || $lat === '' || $lng === null || $lng === '') {
            return null;
        }
        return $this->distanceKm((float)$location['lat'], (float)$location['long'], (float)$lat, (float)$lng);
    }

    /**
     * 半正矢距离计算（km），对齐首页 MerchantDiscoveryRepository::distanceKm
     */
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

    protected function requestedStoreGroupId(array $where): ?int
    {
        if (!isset($where['store_group_id']) || $where['store_group_id'] === '') {
            return null;
        }
        if (!is_numeric($where['store_group_id'])) {
            throw new ValidateException('店铺分组筛选格式错误');
        }
        return (int)$where['store_group_id'];
    }

    protected function collectStoreGroupIds(int $groupId): array
    {
        if ($groupId <= 0) {
            return [];
        }
        $ids = [$groupId];
        $childIds = StoreGroup::where('pid', $groupId)->column('store_group_id');
        foreach ($childIds as $childId) {
            $ids = array_merge($ids, $this->collectStoreGroupIds((int)$childId));
        }
        return array_values(array_unique($ids));
    }

    /**
     * 批量创建记录
     *
     * 本函数用于在数据库中批量创建某种类型的记录。它首先检查每种类型是否已存在，
     * 如果不存在，则创建该记录。这个过程是在一个数据库事务中完成的，确保了数据的一致性。
     *
     * @param int $uid 用户ID，表示这些记录属于哪个用户。
     * @param array $data 包含待创建记录信息的数组。其中，'type_id' 键包含待创建记录的类型ID列表，
     *                   'type' 键表示记录的类型。
     */
    public function batchCreate(int $uid, array $data)
    {
        // 开启数据库事务处理
        Db::transaction(function () use ($data, $uid) {
            // 遍历类型ID列表
            foreach ($data['type_id'] as $item) {
                // 组装待插入的数据参数
                $param = ['type' => $data['type'], 'type_id' => $item, 'uid' => $uid];

                // 检查当前类型ID是否已存在，如果不存在则创建新记录
                if(!$this->dao->getWhereCount($param)) {
                    $this->dao->create($param);
                }
            }
        });
    }

    /**
     * 根据传入的参数创建相关记录。
     * 此函数用于处理不同类型项的创建逻辑，包括用户商家信息和商品信息的创建。
     * 根据$type的值，决定是创建用户商家关联还是商品关联。
     *
     * @param array $params 包含创建所需信息的参数数组。
     * @return bool|void 返回事务处理结果。
     */
    public function create(array $params)
    {
        // 根据$type的值来决定处理逻辑
        if($params['type'] == 10) {
            // 处理用户商家关联的创建逻辑
            $id = $params['type_id'];
            app()->make(UserMerchantRepository::class)->getInfo($params['uid'], $params['type_id']);
            $make = app()->make(MerchantRepository::class);
        }else{
            // 处理商品关联的创建逻辑
            $spu = $this->getSpu($params);
            $params['type_id'] = $spu->spu_id;
            $params['type'] = 1;
            $make = app()->make(ProductRepository::class);
            $id = $spu->product_id;
        }

        // 使用事务来确保操作的完整性
        return Db::transaction(function()use($params,$make,$id){
            // 增加关注计数
            $make->incCareCount($id);
            // 创建相关记录
            $this->dao->create($params);
        });
    }


    /**
     * 批量删除
     * @param array $ids
     * @param $uid
     * @param $type
     * @author Qinii
     * @day 2023/2/16
     */
    public function batchDestory(array $ids,$uid, $type = 1)
    {
        if ($type == 10) {
            app()->make(MerchantRepository::class)->decCareCount($ids);
        } else {
            $pids = app()->make(SpuRepository::class)->search(['spu_ids' => $ids])->column('S.product_id');
            app()->make(ProductRepository::class)->decCareCount($pids);
        }
        $this->dao->search(['uid' => $uid,'type' => $type])->where('type_id','in',$ids)->delete();
    }

    /**
     * 给指定用户添加支付者信息。
     * 该方法用于处理将一个用户与多个新的商户ID关联起来的逻辑。它首先检查哪些商户ID已经与用户关联，
     * 然后只处理那些还未关联的商户ID。这样可以避免重复添加相同的关联信息。
     *
     * @param int $uid 用户ID。表示要添加支付者信息的用户的唯一标识。
     * @param array $merIds 商户ID数组。包含要将用户与之关联的所有新的商户ID。
     */
    public function payer($uid, array $merIds)
    {
        // 检查已存在的商户ID与用户ID的交集，以避免重复添加
        $isset = $this->dao->intersectionPayer($uid, $merIds);

        // 筛选出还未关联的商户ID
        $merIds = array_diff($merIds, $isset);

        // 如果没有新的商户ID需要添加，则直接返回
        if (!count($merIds)) return;

        // 准备要插入的数据数组
        $data = [];
        foreach ($merIds as $merId) {
            $data[] = [
                'type_id' => $merId,
                'type' => 12, // 这里的类型标识12具体代表什么含义，需要根据实际业务进行解释
                'uid' => $uid
            ];
        }

        // 批量插入新的支付者信息
        $this->dao->insertAll($data);
    }


    /**
     * 根据传入的数据获取SPU（Special Product Unit）信息。
     *
     * 此方法用于根据产品类型和相关ID获取特定的SPU。它支持三种类型的产品：
     * 0和1代表普通产品和秒杀产品，这两种类型的产品通过product_id来识别；
     * 其他类型的產品通过activity_id来识别。
     * 如果找不到对应的SPU信息，则抛出一个ValidateException异常。
     *
     * @param array $data 包含产品类型和相关ID的数据数组。
     * @return mixed 返回找到的SPU信息。
     * @throws ValidateException 如果SPU不存在则抛出异常。
     */
    public function getSpu(array $data)
    {
        // 通过依赖注入创建SpuRepository实例
        $make = app()->make(SpuRepository::class);

        // 初始化用于查询条件的数组
        $where['product_type'] = $data['type'];

        // 根据产品类型设置查询条件
        switch ($data['type']) {
            case 0:
            case 1:
                // 普通产品和秒杀产品通过product_id查询
                $where['product_id'] = $data['type_id'];
                break;
            default:
                // 其他类型产品通过activity_id查询
                $where['activity_id'] = $data['type_id'];
                break;
        }

        // 执行查询并获取SPU信息
        $ret = $make->getSearch($where)->find();

        // 如果查询结果为空，则抛出异常
        if (!$ret) {
            throw new ValidateException('SPU不存在');
        }

        // 返回查询结果
        return $ret;
    }

    /**
     * 获取用户发布到社区的产品信息
     *
     * 本函数用于查询用户发布到社区的产品列表，支持通过关键字进行筛选。
     * 参数:
     * $keyword - 可选，搜索关键字，用于筛选产品。
     * $uid - 用户ID，指定查询哪个用户的发布记录。
     * $page - 分页页码，用于分页查询。
     * $limit - 每页记录数，用于控制分页查询的数量。
     * 返回值:
     * 一个包含两个元素的数组，'count' 表示满足条件的记录总数，'list' 表示当前页的记录列表。
     *
     * @param ?string $keyword 搜索关键字
     * @param int $uid 用户ID
     * @param int $page 分页页码
     * @param int $limit 每页记录数
     * @return array 返回包含记录总数和列表的数组
     */
    public function getUserProductToCommunity(?string $keyword, int $uid, int $page, int $limit)
    {
        // 根据关键字和用户ID查询用户发布到社区的产品，并按产品ID分组
        $query = $this->dao->getUserProductToCommunity($keyword, $uid)->group('product_id');

        // 计算满足条件的记录总数
        $count = $query->count();

        // 设置查询字段，指定需要返回的字段列表，并进行分页查询，返回当前页的记录列表
        $list = $query->setOption('field',[])->field('uid,product_id,product_type,spu_id,image,store_name,price')
            ->page($page, $limit)->select();

        // 返回记录总数和记录列表
        return compact('count', 'list');
    }
}
