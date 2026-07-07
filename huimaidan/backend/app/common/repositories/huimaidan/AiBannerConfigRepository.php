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

use app\common\dao\huimaidan\AiBannerConfigDao;
use app\common\repositories\BaseRepository;

/**
 * @mixin AiBannerConfigDao
 */
class AiBannerConfigRepository extends BaseRepository
{
    public function __construct(AiBannerConfigDao $dao)
    {
        $this->dao = $dao;
    }

    public function currentMealType(?int $hour = null): string
    {
        $hour = is_null($hour) ? (int)date('G') : $hour;
        if ($hour < 6) {
            return 'late_night';
        }
        if ($hour < 9) {
            return 'breakfast';
        }
        if ($hour < 11) {
            return 'brunch';
        }
        if ($hour < 14) {
            return 'lunch';
        }
        if ($hour < 17) {
            return 'tea';
        }
        if ($hour < 21) {
            return 'dinner';
        }
        if ($hour < 24) {
            return 'late_night';
        }
        return 'late_night';
    }

    public function configByMealType(string $mealType): array
    {
        try {
            $row = $this->dao->search(['meal_type' => $mealType, 'is_enabled' => 1])->find();
            if ($row) {
                return $row->toArray();
            }
        } catch (\Throwable $e) {
        }
        return $this->defaultConfig($mealType);
    }

    public function defaultConfig(string $mealType): array
    {
        $defaults = [
            'breakfast' => ['早餐时间到！', '为您推荐附近营业中的优惠早餐', '#FFF8E1', '#F57F17'],
            'brunch' => ['早午餐吃点什么？', '轻松找一家离你近、评价好的店', '#F3E5F5', '#6A1B9A'],
            'lunch' => ['午餐时间到！', '为您推荐附近高分又有优惠的好店', '#FFF3E0', '#E65100'],
            'tea' => ['下午茶时光', '甜品茶饮和轻食优惠都在这里', '#E8F5E9', '#2E7D32'],
            'dinner' => ['晚餐推荐', '适合聚餐、约会的优惠商家已备好', '#FFEBEE', '#C62828'],
            'supper' => ['夜宵时间', '附近还在营业的夜宵好店推荐', '#E3F2FD', '#1565C0'],
            'late_night' => ['夜宵时间', '附近还在营业的夜宵好店推荐', '#E3F2FD', '#1565C0'],
        ];
        $item = $defaults[$mealType] ?? $defaults['lunch'];
        return [
            'meal_type' => $mealType,
            'title_template' => $item[0],
            'subtitle_template' => $item[1],
            'bg_color' => $item[2],
            'text_color' => $item[3],
        ];
    }
}
