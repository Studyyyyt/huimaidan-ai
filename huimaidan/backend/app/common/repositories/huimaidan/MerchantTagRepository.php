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

use app\common\dao\huimaidan\MerchantTagDao;
use app\common\model\huimaidan\MerchantTag;
use app\common\repositories\BaseRepository;
use think\facade\Db;

/**
 * @mixin MerchantTagDao
 */
class MerchantTagRepository extends BaseRepository
{
    public function __construct(MerchantTagDao $dao)
    {
        $this->dao = $dao;
    }

    public function byMerchantIds(array $merIds): array
    {
        $merIds = array_values(array_filter(array_unique(array_map('intval', $merIds))));
        if (!$merIds) {
            return [];
        }
        try {
            $rows = $this->dao->search(['mer_ids' => $merIds])->select()->toArray();
        } catch (\Throwable $e) {
            return [];
        }
        $result = [];
        foreach ($rows as $row) {
            $merId = (int)($row['mer_id'] ?? 0);
            if ($merId <= 0) {
                continue;
            }
            $result[$merId][] = [
                'tag_type' => (string)($row['tag_type'] ?? ''),
                'tag_value' => (string)($row['tag_value'] ?? ''),
                'tag_weight' => (int)($row['tag_weight'] ?? 10),
            ];
        }
        return $result;
    }

    public function matchedMerchantIds(array $conditions): array
    {
        $groups = [];
        foreach (['category', 'scene', 'taste', 'facility', 'feature', 'meal', 'promotion', 'price'] as $type) {
            foreach ((array)($conditions[$type] ?? []) as $item) {
                if ($item !== '') {
                    if ($type === 'price') {
                        $groups[$type] = array_merge($groups[$type] ?? [], $this->priceTagValues((string)$item));
                    } else {
                        $groups[$type][] = (string)$item;
                    }
                }
            }
        }
        $groups = array_filter(array_map(function (array $values) {
            return array_values(array_unique(array_filter($values, function ($value) {
                return trim((string)$value) !== '';
            })));
        }, $groups));
        if (!$groups) {
            return [];
        }

        try {
            $ids = [];
            $categoryIds = [];
            foreach ($groups as $type => $values) {
                $matched = MerchantTag::getDB()
                    ->where('tag_type', $type)
                    ->whereIn('tag_value', $values)
                    ->column('mer_id');
                $matched = array_values(array_unique(array_map('intval', $matched)));
                if ($type === 'category') {
                    $categoryIds = $matched;
                }
                $ids = array_merge($ids, $matched);
            }
            $ids = array_values(array_unique(array_filter($ids)));
            if ($categoryIds) {
                $ids = array_values(array_intersect($ids, $categoryIds));
            }
            return $ids;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function replaceAutoTags(int $merId, array $tags): void
    {
        $this->replaceTags($merId, $tags, 1);
    }

    public function replaceManualTags(int $merId, array $tags): void
    {
        $this->replaceTags($merId, $tags, 0);
    }

    protected function replaceTags(int $merId, array $tags, int $isAuto): void
    {
        if ($merId <= 0) {
            return;
        }
        Db::transaction(function () use ($merId, $tags, $isAuto) {
            MerchantTag::getDB()->where('mer_id', $merId)->where('is_auto', $isAuto)->delete();
            $preserveKeys = [];
            $preserveRows = MerchantTag::getDB()->where('mer_id', $merId)->where('is_auto', $isAuto ? 0 : 1)->field('tag_type,tag_value')->select()->toArray();
            foreach ($preserveRows as $row) {
                $preserveKeys[$row['tag_type'] . ':' . $row['tag_value']] = true;
            }
            $rows = [];
            foreach ($tags as $tag) {
                $type = trim((string)($tag['tag_type'] ?? ''));
                $value = trim((string)($tag['tag_value'] ?? ''));
                if ($type === '' || $value === '') {
                    continue;
                }
                if (isset($preserveKeys[$type . ':' . $value])) {
                    continue;
                }
                $rows[] = [
                    'mer_id' => $merId,
                    'tag_type' => $type,
                    'tag_value' => $value,
                    'tag_weight' => max(1, min(100, (int)($tag['tag_weight'] ?? 10))),
                    'is_auto' => $isAuto,
                ];
            }
            if ($rows) {
                $this->dao->insertAll($rows);
            }
        });
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
}
