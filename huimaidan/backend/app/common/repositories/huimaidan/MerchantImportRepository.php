<?php

namespace app\common\repositories\huimaidan;

use app\common\model\store\CityArea;
use app\common\model\system\merchant\Merchant;
use app\common\model\system\merchant\MerchantAdmin;
use app\common\model\system\merchant\MerchantCategory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\exception\ValidateException;
use think\facade\Db;

class MerchantImportRepository
{
    const HEADERS = [
        '外部商户ID', '商户名称*', '分类名称', '店铺类型名称', '店铺区域名称', '所属商户名称', '店铺分组名称', '省份', '城市名称', '区县', '详细地址*', '经度', '纬度',
        '门头图/头像URL', '联系电话', '商户登录账号', '商户登录密码', '负责人姓名', '人均消费', '营业开始', '营业结束', '营业时间文字',
        '设施', '优惠说明/商户标语', '展示销量', '商家折扣', '会员折扣',
        'AI分类标签', 'AI场景标签', 'AI口味标签', 'AI设施标签', 'AI价格标签', 'AI餐段标签',
    ];

    protected $profileRepository;
    protected $merchantTagRepository;
    protected $initializerRepository;
    protected $aiTagRepository;

    public function __construct(
        MerchantProfileRepository $profileRepository,
        MerchantTagRepository $merchantTagRepository,
        MerchantTagInitializerRepository $initializerRepository,
        AiTagRepository $aiTagRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->merchantTagRepository = $merchantTagRepository;
        $this->initializerRepository = $initializerRepository;
        $this->aiTagRepository = $aiTagRepository;
    }

    public function templateFile(): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('商户导入模板');
        foreach (self::HEADERS as $index => $title) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $title);
            $sheet->getColumnDimensionByColumn($index + 1)->setWidth(in_array($index, [6, 9, 16], true) ? 30 : 18);
        }
        $sheet->fromArray([
            [
                '257', '宋北魏家宴（印象江南店）', '中餐', '本地生活', '默认区域', '默认商户主体', '默认分组', '内蒙古自治区', '呼和浩特市', '玉泉区', '宋北魏家宴(印象江南店)',
                '111.647551', '40.795721', 'https://example.com/shop.jpg', '3900111', 'songbeiwei', '000000', '宋北魏家宴', '0',
                '00:00', '00:00', '周一至周日 00:00-00:00', '包间,大桌,电话预订',
                '全场消费8折（酒水饮料除外）', '1', '0.80', '0.80',
                '中餐', '聚餐,日常', '清淡', '包间,大桌', '0-30', '午餐,晚餐',
            ],
            [
                '', '测试奶茶轻食', '奶茶', '本地生活', '默认区域', '默认商户主体', '默认分组', '北京市', '北京', '朝阳区', '北京市朝阳区测试路2号',
                '116.410526', '39.90803', '/static/images/default-avatar.png', '13000000001', '', '', '', '24',
                '09:00', '22:00', '周一至周日 09:00-22:00', '无烟',
                '下午茶和轻食好去处', '188', '0.90', '0.88',
                '奶茶', '下午茶', '甜', '无烟', '0-30', '下午茶',
            ],
        ], null, 'A2');
        $sheet->setCellValue('A5', '填写说明：商户名称、详细地址必填；分类、类型、区域不填时系统会自动创建/使用默认值；商户登录账号不填时系统自动生成，密码不填默认000000；经纬度建议填写，AI按距离推荐会用到。');
        $sheet->mergeCells('A5:AH5');
        $sheet->getStyle('A1:AH1')->getFont()->setBold(true);
        $sheet->getStyle('A5')->getFont()->getColor()->setRGB('606266');

        $tips = $spreadsheet->createSheet();
        $tips->setTitle('填写说明');
        $tips->fromArray([
            ['列名', '怎么填写', '示例'],
            ['AI分类标签', '商户主营品类，可填多个，逗号分隔', '火锅,中餐,奶茶'],
            ['AI场景标签', '适合什么消费场景', '聚餐,亲子,约会,商务,日常,下午茶'],
            ['AI口味标签', '用户会用来描述口味的词', '辣,清淡,甜'],
            ['AI设施标签', '用户会关心的设施', '包间,大桌,宝宝椅,无烟'],
            ['AI价格标签', '按人均消费选择一个或多个', '0-30,30-60,60-100,100-150,150+'],
            ['AI餐段标签', '适合推荐的时间段', '早餐,午餐,下午茶,晚餐,夜宵'],
            ['商家折扣/会员折扣', '可填0.80，也可直接填8，系统会按8折处理', '0.80 或 8'],
            ['分类名称', 'CRMEB 店铺分类；不存在时自动创建', '火锅'],
            ['店铺类型名称', 'CRMEB 店铺类型；不填默认本地生活', '本地生活'],
            ['店铺区域名称', 'CRMEB 店铺区域；不填默认默认区域', '默认区域'],
            ['所属商户名称', '商户主体；同一个品牌多门店可填同一个名称', '默认商户主体'],
            ['店铺分组名称', '后台和小程序分类分组；不填默认默认分组', '默认分组'],
            ['商户登录账号', '商户网页后台登录账号；不填则系统自动生成', 'songbeiwei'],
            ['商户登录密码', '商户网页后台初始密码；不填默认000000', '000000'],
            ['负责人姓名', '商户后台管理员姓名；不填默认使用商户名称', '张三'],
        ], null, 'A1');
        foreach ([1, 2, 3] as $column) {
            $tips->getColumnDimensionByColumn($column)->setWidth($column === 2 ? 34 : 22);
        }
        $tips->getStyle('A1:C1')->getFont()->setBold(true);
        $spreadsheet->setActiveSheetIndex(0);

        $dir = app()->getRuntimePath() . 'huimaidan_ai';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir . DIRECTORY_SEPARATOR . 'huimaidan_merchant_import_template.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return [
            'absolute_path' => $path,
            'file_name' => '惠买单商户AI推荐导入模板.xlsx',
        ];
    }

    public function importFile(string $path): array
    {
        if (!is_file($path)) {
            throw new ValidateException('请上传有效的Excel文件');
        }
        $ext = ucfirst(strtolower(pathinfo($path, PATHINFO_EXTENSION)));
        if (!in_array($ext, ['Xlsx', 'Xls'], true)) {
            throw new ValidateException('仅支持xlsx或xls文件');
        }
        $sheet = IOFactory::createReader($ext)->load($path)->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        if ($highestRow < 2) {
            throw new ValidateException('模板中没有可导入的商户数据');
        }

        $result = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
            'health_report' => [
                'excellent' => 0,
                'usable' => 0,
                'needs_improvement' => 0,
                'missing_field_count' => 0,
                'merchants' => [],
            ],
        ];
        $affectedMerIds = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $data = $this->readRow($sheet, $row);
            if ($this->emptyRow($data)) {
                continue;
            }
            try {
                $created = $this->saveMerchant($data);
                $created ? $result['created']++ : $result['updated']++;
                // 记录商户名称用于后续健康检查
                $merchant = Merchant::getDB()->where('mer_name', trim((string)$data['name']))->where('is_del', 0)->field('mer_id')->find();
                if ($merchant) {
                    $affectedMerIds[] = (int)$merchant['mer_id'];
                }
            } catch (\Throwable $e) {
                $result['failed']++;
                $result['errors'][] = '第' . $row . '行：' . $e->getMessage();
            }
        }

        if ($affectedMerIds) {
            $health = app()->make(AiMerchantHealthRepository::class)->checkMerchants($affectedMerIds, 500);
            foreach ($health['list'] as $item) {
                $result['health_report'][$item['status']]++;
                $result['health_report']['missing_field_count'] += count($item['missing_items']);
                if ($item['status'] === 'needs_improvement') {
                    $result['health_report']['merchants'][] = $item;
                }
            }
        }

        return $result;
    }

    protected function saveMerchant(array $data): bool
    {
        $name = trim((string)$data['name']);
        $address = trim((string)$data['address']);
        if ($name === '' || $address === '') {
            throw new ValidateException('商户名称和地址不能为空');
        }

        return Db::transaction(function () use ($data, $name, $address) {
            $merchant = Merchant::getDB()->where('mer_name', $name)->where('is_del', 0)->find();
            $created = !$merchant;
            $payload = [
                'category_id' => $this->categoryId((int)$data['category_id'], (string)$data['category_name']),
                'city_id' => $this->cityId((int)$data['city_id'], (string)$data['city_name']),
                'type_id' => $this->typeId((int)($data['type_id'] ?? 0), (string)($data['type_name'] ?? '')),
                'mer_name' => $this->limit($name, 32),
                'real_name' => $this->limit($name, 32),
                'mer_phone' => $this->phone((string)$data['phone']),
                'mer_address' => $this->limit($address, 64),
                'mer_keyword' => $this->limit($name, 64),
                'mer_avatar' => $this->limit(trim((string)$data['avatar']), 128),
                'mer_banner' => '',
                'mini_banner' => '',
                'sales' => max(0, (int)$data['sales']),
                'product_score' => '5.0',
                'service_score' => '5.0',
                'postage_score' => '5.0',
                'mark' => 'AI推荐Excel导入',
                'sort' => 0,
                'status' => 1,
                'long' => $this->coordinate((string)$data['lng'], 180),
                'lat' => $this->coordinate((string)$data['lat'], 90),
                'is_del' => 0,
                'is_best' => 1,
                'mer_state' => 1,
                'mer_info' => $this->limit(trim((string)$data['slogan']), 255),
                'service_phone' => $this->phone((string)$data['phone']),
                'huimaidan_settlement_mode' => 2,
                'region_id' => $this->regionId((int)($data['region_id'] ?? 0), (string)($data['region_name'] ?? '')),
                'business_id' => $this->businessId((int)($data['business_id'] ?? 0), (string)($data['business_name'] ?? '')),
            ];
            if ($created) {
                $payload += [
                    'reg_admin_id' => 0,
                    'commission_switch' => 0,
                    'is_audit' => 0,
                    'is_bro_room' => 1,
                    'is_bro_goods' => 1,
                    'is_trader' => 0,
                    'copy_product_num' => 0,
                    'export_dump_num' => 0,
                    'mer_money' => '0.00',
                    'huimaidan_withdraw_rate' => '0.00',
                    'huimaidan_min_extract_money' => '500.00',
                    'financial_type' => 1,
                    'sub_mchid' => '',
                    'delivery_way' => '',
                    'delivery_balance' => '0.00',
                    'margin' => '0.00',
                    'ot_margin' => '0.00',
                    'is_margin' => 0,
                    'offline_switch' => 0,
                    'care_ficti' => 0,
                    'applyment_id' => '',
                    'applyment_switch' => 1,
                ];
                $merId = (int)Merchant::getDB()->insertGetId($payload);
            } else {
                $merId = (int)$merchant->mer_id;
                Merchant::getDB()->where('mer_id', $merId)->update($payload);
            }

            $this->profileRepository->saveByMerId($merId, [
                'per_capita' => $data['per_capita'],
                'business_hours' => (string)$data['business_hours'],
                'facilities' => $this->facilities((string)$data['facilities']),
                'promo_image' => $this->limit(trim((string)$data['avatar']), 255),
                'slogan' => $this->limit(trim((string)$data['slogan']), 255),
                'configured_sales' => max(0, (int)$data['sales']),
            ]);
            $manualTags = $this->tags((string)$data['ai_tags']);
            if ($manualTags) {
                $this->merchantTagRepository->replaceManualTags($merId, $manualTags);
            }
            $this->initializerRepository->initialize($merId);
            $this->saveDiscount($merId, (string)$data['merchant_discount'], (string)$data['member_discount']);
            $this->saveMerchantAdmin($merId, $data, $name);
            $this->attachStoreGroup($merId, (string)($data['group_name'] ?? ''));

            return $created;
        });
    }

    protected function saveMerchantAdmin(int $merId, array $data, string $merchantName): void
    {
        $account = $this->normalizeAccount((string)($data['admin_account'] ?? ''));
        if ($account === '') {
            $account = $this->defaultAccount($merId);
        }
        $existsForOtherMerchant = MerchantAdmin::getDB()
            ->where('account', $account)
            ->where('mer_id', '<>', $merId)
            ->where('is_del', 0)
            ->find();
        if ($existsForOtherMerchant) {
            throw new ValidateException('商户登录账号已被其他商户使用：' . $account);
        }

        $admin = MerchantAdmin::getDB()->where('mer_id', $merId)->where('level', 0)->where('is_del', 0)->find();
        $payload = [
            'account' => $account,
            'real_name' => $this->limit(trim((string)($data['admin_name'] ?? '')) ?: $merchantName, 16),
            'phone' => $this->phone((string)($data['phone'] ?? '')),
            'level' => 0,
            'status' => 1,
        ];
        $password = trim((string)($data['admin_password'] ?? ''));
        if (!$admin || $password !== '') {
            $payload['pwd'] = password_hash($password !== '' ? $password : '000000', PASSWORD_BCRYPT);
        }
        if ($admin) {
            MerchantAdmin::getDB()->where('merchant_admin_id', (int)$admin['merchant_admin_id'])->update($payload);
        } else {
            $payload += [
                'mer_id' => $merId,
                'roles' => '',
                'is_del' => 0,
                'login_count' => 0,
            ];
            MerchantAdmin::getDB()->insert($payload);
        }
    }

    protected function saveDiscount(int $merId, string $merchantDiscount, string $memberDiscount): void
    {
        if (trim($merchantDiscount) === '' && trim($memberDiscount) === '') {
            return;
        }
        $merchantDiscount = $this->rate($merchantDiscount ?: '1.00');
        $memberDiscount = $this->rate($memberDiscount ?: $merchantDiscount);
        $discount = Db::name('huimaidan_merchant_discount')->where('mer_id', $merId)->order('discount_id DESC')->find();
        $payload = [
            'mer_id' => $merId,
            'pool_id' => 0,
            'merchant_discount' => $merchantDiscount,
            'status' => 1,
            'start_time' => null,
            'end_time' => null,
            'sort' => 0,
            'remark' => 'AI推荐Excel导入',
        ];
        if ($discount) {
            Db::name('huimaidan_merchant_discount')->where('discount_id', (int)$discount['discount_id'])->update($payload);
            $discountId = (int)$discount['discount_id'];
        } else {
            $discountId = (int)Db::name('huimaidan_merchant_discount')->insertGetId($payload);
        }
        Db::name('huimaidan_member_discount')->where('discount_id', $discountId)->delete();
        Db::name('huimaidan_member_discount')->insert([
            'discount_id' => $discountId,
            'mer_id' => $merId,
            'member_level' => 1,
            'member_discount' => $memberDiscount,
            'status' => 1,
        ]);
    }

    protected function readRow($sheet, int $row): array
    {
        $headers = [];
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        for ($column = 1; $column <= $highestColumn; $column++) {
            $headers[trim((string)$sheet->getCellByColumnAndRow($column, 1)->getFormattedValue())] = $column;
        }
        if (isset($headers['外部商户ID']) || isset($headers['详细地址*'])) {
            return $this->readChineseRow($sheet, $row, $headers);
        }

        $keys = ['name', 'category_id', 'category_name', 'type_id', 'type_name', 'region_id', 'region_name', 'city_id', 'city_name', 'address', 'lng', 'lat', 'avatar', 'phone', 'admin_account', 'admin_password', 'admin_name', 'per_capita', 'business_hours', 'facilities', 'slogan', 'sales', 'merchant_discount', 'member_discount', 'ai_tags'];
        $data = [];
        foreach ($keys as $index => $key) {
            $data[$key] = trim((string)$sheet->getCellByColumnAndRow($index + 1, $row)->getFormattedValue());
        }
        return $data;
    }

    protected function readChineseRow($sheet, int $row, array $headers): array
    {
        $value = function (array $names) use ($sheet, $row, $headers): string {
            foreach ($names as $name) {
                if (isset($headers[$name])) {
                    return trim((string)$sheet->getCellByColumnAndRow($headers[$name], $row)->getFormattedValue());
                }
            }
            return '';
        };

        $businessHours = $value(['营业时间文字']);
        if ($businessHours === '') {
            $start = $value(['营业开始']);
            $end = $value(['营业结束']);
            if ($start !== '' || $end !== '') {
                $businessHours = '周一至周日 ' . ($start ?: '00:00') . '-' . ($end ?: '24:00');
            }
        }

        $province = $value(['省份']);
        $city = $value(['城市名称', '城市']);
        $district = $value(['区县', '区域']);
        $address = $this->joinAddress($province, $city, $district, $value(['详细地址*', '详细地址', '地址*', '地址']));

        return [
            'name' => $value(['商户名称*', '商户名称']),
            'category_id' => $value(['店铺分类ID', '商户分类ID', '分类ID']),
            'category_name' => $value(['分类名称', '分类']),
            'type_id' => $value(['店铺类型ID', '商户类型ID', '类型ID']),
            'type_name' => $value(['店铺类型名称', '商户类型名称', '类型名称']),
            'region_id' => $value(['店铺区域ID', '区域ID']),
            'region_name' => $value(['店铺区域名称', '区域名称']),
            'business_id' => $value(['所属商户ID', '商户主体ID']),
            'business_name' => $value(['所属商户名称', '商户主体名称']),
            'group_name' => $value(['店铺分组名称', '分组名称']),
            'city_id' => $value(['城市ID']),
            'city_name' => $city,
            'address' => $address,
            'lng' => $value(['经度', 'longitude']),
            'lat' => $value(['纬度', 'latitude']),
            'avatar' => $value(['门头图/头像URL', '头像URL', '图片URL']),
            'phone' => $value(['联系电话', '电话']),
            'admin_account' => $value(['商户登录账号', '登录账号', '商户账号']),
            'admin_password' => $value(['商户登录密码', '登录密码', '商户密码']),
            'admin_name' => $value(['负责人姓名', '管理员姓名', '负责人']),
            'per_capita' => $value(['人均消费', '人均']),
            'business_hours' => $businessHours,
            'facilities' => $value(['设施']),
            'slogan' => $value(['优惠说明/商户标语', '商户标语', '优惠说明']),
            'sales' => $value(['展示销量', '销量']),
            'merchant_discount' => $value(['商家折扣']),
            'member_discount' => $value(['会员折扣']),
            'ai_tags' => $this->buildAiTagsFromChineseColumns($value),
        ];
    }

    protected function buildAiTagsFromChineseColumns(callable $value): string
    {
        $map = [
            'category' => ['AI分类标签', '分类标签'],
            'scene' => ['AI场景标签', '场景标签'],
            'taste' => ['AI口味标签', '口味标签'],
            'facility' => ['AI设施标签', '设施标签'],
            'price' => ['AI价格标签', '价格标签'],
            'meal' => ['AI餐段标签', '餐段标签'],
        ];
        $items = [];
        foreach ($map as $type => $names) {
            foreach (preg_split('/[,，、\n\r]+/u', $value($names)) as $tag) {
                $tag = trim($tag);
                if ($tag !== '') {
                    $items[] = $type . ':' . $this->normalizeMealTag($tag) . ':70';
                }
            }
        }
        return implode(',', $items);
    }

    protected function normalizeMealTag(string $tag): string
    {
        $map = ['早餐' => 'breakfast', '午餐' => 'lunch', '下午茶' => 'tea', '晚餐' => 'dinner', '夜宵' => 'supper'];
        return $map[$tag] ?? $tag;
    }

    protected function joinAddress(string $province, string $city, string $district, string $address): string
    {
        $address = trim($address);
        $prefix = '';
        foreach ([$province, $city, $district] as $part) {
            $part = trim($part);
            if ($part !== '' && mb_strpos($address, $part) === false && mb_strpos($prefix, $part) === false) {
                $prefix .= $part;
            }
        }
        return $this->limit($prefix . $address, 255);
    }

    protected function emptyRow(array $data): bool
    {
        return implode('', array_map('trim', $data)) === '' || strpos((string)$data['name'], '填写说明') === 0;
    }

    protected function categoryId(int $categoryId, string $categoryName): int
    {
        if ($categoryId > 0) {
            return $categoryId;
        }
        $categoryName = $this->limit(trim($categoryName) ?: '餐饮美食', 32);
        if ($categoryName !== '' && class_exists(MerchantCategory::class)) {
            $id = (int)MerchantCategory::getDB()->where('category_name', $categoryName)->value('merchant_category_id');
            if ($id > 0) {
                return $id;
            }
            return (int)MerchantCategory::getDB()->insertGetId([
                'category_name' => $categoryName,
                'commission_rate' => '0.0000',
            ]);
        }
        return 0;
    }

    protected function typeId(int $typeId, string $typeName): int
    {
        if ($typeId > 0) {
            $this->grantHuimaidanMerchantAuth($typeId);
            return $typeId;
        }
        $typeName = $this->limit(trim($typeName) ?: '本地生活', 16);
        $id = (int)Db::name('merchant_type')->where('type_name', $typeName)->value('mer_type_id');
        if ($id > 0) {
            $this->grantHuimaidanMerchantAuth($id);
            return $id;
        }
        $id = (int)Db::name('merchant_type')->insertGetId([
            'type_name' => $typeName,
            'type_info' => '',
            'description' => '',
            'margin' => '0.00',
            'is_margin' => 0,
            'mark' => 'AI推荐Excel导入自动创建',
        ]);
        $this->grantHuimaidanMerchantAuth($id);
        return $id;
    }

    protected function grantHuimaidanMerchantAuth(int $typeId): void
    {
        if ($typeId <= 0) {
            return;
        }
        $menuIds = Db::name('system_menu')
            ->where('is_mer', 1)
            ->where(function ($query) {
                $query->where('route', 'like', '/huimaidan/%')
                    ->whereOr('route', 'like', 'merchantHuimaidan%');
            })
            ->column('menu_id');
        if (!$menuIds) {
            return;
        }
        $exists = Db::name('relevance')
            ->where('left_id', $typeId)
            ->where('type', 'mer_auth')
            ->whereIn('right_id', $menuIds)
            ->column('right_id');
        $exists = array_map('intval', $exists);
        $rows = [];
        foreach ($menuIds as $menuId) {
            $menuId = (int)$menuId;
            if (!in_array($menuId, $exists, true)) {
                $rows[] = [
                    'left_id' => $typeId,
                    'right_id' => $menuId,
                    'type' => 'mer_auth',
                ];
            }
        }
        if ($rows) {
            Db::name('relevance')->insertAll($rows);
        }
    }

    protected function regionId(int $regionId, string $regionName): int
    {
        return $this->organizationId($regionId, $regionName ?: '默认区域', 0);
    }

    protected function businessId(int $businessId, string $businessName): int
    {
        return $this->organizationId($businessId, $businessName ?: '默认商户主体', 1);
    }

    protected function organizationId(int $id, string $name, int $type): int
    {
        if ($id > 0) {
            return $id;
        }
        $name = $this->limit(trim($name), 255);
        if ($name === '') {
            return 0;
        }
        $exists = (int)Db::name('circle')->where('name', $name)->where('type', $type)->value('circle_id');
        if ($exists > 0) {
            return $exists;
        }
        return (int)Db::name('circle')->insertGetId([
            'name' => $name,
            'pid' => 0,
            'path' => '/',
            'circle_agent_id' => 0,
            'commission_type' => 0,
            'commission_rate' => '0.00',
            'level' => 0,
            'remark' => 'AI推荐Excel导入自动创建',
            'sort' => 0,
            'status' => 1,
            'type' => $type,
            'role_id' => 0,
            'business_store_category' => $type === 1 ? $this->categoryId(0, '餐饮美食') : 0,
            'business_store_type' => $type === 1 ? $this->typeId(0, '本地生活') : 0,
        ]);
    }

    protected function attachStoreGroup(int $merId, string $groupName = ''): void
    {
        $groupId = $this->storeGroupId($groupName);
        if ($groupId <= 0) {
            return;
        }
        $exists = Db::name('relevance')
            ->where('left_id', $groupId)
            ->where('right_id', $merId)
            ->where('type', 'store_group')
            ->count();
        if (!$exists) {
            Db::name('relevance')->insert([
                'left_id' => $groupId,
                'right_id' => $merId,
                'type' => 'store_group',
            ]);
        }
    }

    protected function storeGroupId(string $groupName = ''): int
    {
        $groupName = $this->limit(trim($groupName) ?: '默认分组', 64);
        $exists = (int)Db::name('store_group')->where('name', $groupName)->value('store_group_id');
        if ($exists > 0) {
            return $exists;
        }
        return (int)Db::name('store_group')->insertGetId([
            'pid' => 0,
            'path' => '/',
            'name' => $groupName,
            'level' => 0,
            'positioning_status' => 1,
            'longitude' => '',
            'latitude' => '',
            'address' => '',
            'diy_temp_id' => 0,
            'remark' => 'AI推荐Excel导入自动创建',
            'sort' => 0,
            'status' => 1,
        ]);
    }

    protected function cityId(int $cityId, string $cityName): int
    {
        if ($cityId > 0) {
            return $cityId;
        }
        $cityName = trim($cityName);
        if ($cityName === '') {
            return 0;
        }
        return (int)CityArea::getDB()
            ->where('name', $cityName)
            ->whereOr('name', rtrim($cityName, '市'))
            ->value('id');
    }

    protected function facilities(string $text): array
    {
        $map = $this->facilityLabelMap();
        $result = [];
        foreach ($map as $word => $key) {
            $result[$key] = mb_strpos($text, $word) !== false;
        }
        return $result;
    }

    /**
     * 从 AI 标签字典读取设施标签映射（中文 label => 存储 key）。
     * 字典读取失败时返回硬编码兜底。
     */
    protected function facilityLabelMap(): array
    {
        try {
            $tags = $this->aiTagRepository->search(['tag_type' => 'facility', 'status' => 1])->select()->toArray();
            $map = [];
            foreach ($tags as $tag) {
                $key = (string)($tag['tag_value'] ?? '');
                $label = (string)($tag['tag_label'] ?? '');
                if ($key !== '' && $label !== '') {
                    $map[$label] = $key;
                }
            }
            if ($map) {
                return $map;
            }
        } catch (\Throwable $e) {
            // 兜底
        }
        return [
            '大桌' => 'has_large_table',
            '宝宝椅' => 'has_baby_chair',
            '包间' => 'has_private_room',
            '电话预订' => 'can_phone_reserve',
            '无烟' => 'is_non_smoking',
        ];
    }

    protected function tags(string $text): array
    {
        $rows = [];
        foreach (preg_split('/[,，\n\r]+/u', $text) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            $parts = array_map('trim', explode(':', $item));
            if (count($parts) < 2) {
                continue;
            }
            $rows[] = [
                'tag_type' => $parts[0],
                'tag_value' => $parts[1],
                'tag_weight' => isset($parts[2]) ? (int)$parts[2] : 70,
            ];
        }
        return $rows;
    }

    protected function phone(string $phone): string
    {
        return mb_substr(preg_replace('/\s+/', '', $phone), 0, 13);
    }

    protected function normalizeAccount(string $account): string
    {
        $account = trim($account);
        if ($account === '') {
            return '';
        }
        $account = preg_replace('/\s+/', '', $account);
        if (!preg_match('/^[A-Za-z0-9_@.-]{4,32}$/', $account)) {
            throw new ValidateException('商户登录账号需为4-32位字母、数字、下划线、点或横线');
        }
        return $account;
    }

    protected function defaultAccount(int $merId): string
    {
        return 'hmd_mer_' . $merId;
    }

    protected function coordinate(string $value, int $maxAbs): string
    {
        $value = trim($value);
        if ($value === '' || !is_numeric($value)) {
            return '';
        }
        $number = (float)$value;
        if (abs($number) > $maxAbs) {
            return '';
        }
        return rtrim(rtrim(number_format($number, 6, '.', ''), '0'), '.');
    }

    protected function limit(string $value, int $length): string
    {
        return mb_substr($value, 0, $length);
    }

    protected function rate(string $rate): string
    {
        $value = round((float)$rate, 2);
        if ($value > 1 && $value <= 10) {
            $value = $value / 10;
        } elseif ($value > 10 && $value <= 100) {
            $value = $value / 100;
        }
        $rate = number_format($value, 2, '.', '');
        if ($value <= 0 || $value > 1) {
            throw new ValidateException('折扣必须大于0且不超过1；8折可填0.80或8');
        }
        return $rate;
    }
}
