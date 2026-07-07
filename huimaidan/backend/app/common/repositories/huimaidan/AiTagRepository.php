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

use app\common\dao\huimaidan\AiTagDao;
use app\common\repositories\BaseRepository;

/**
 * @mixin AiTagDao
 */
class AiTagRepository extends BaseRepository
{
    public function __construct(AiTagDao $dao)
    {
        $this->dao = $dao;
    }

    public function enabledTags(): array
    {
        try {
            return $this->dao->search(['status' => 1])->select()->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function keywordMap(): array
    {
        $map = [];
        foreach ($this->enabledTags() as $tag) {
            $type = (string)($tag['tag_type'] ?? '');
            $value = (string)($tag['tag_value'] ?? '');
            if ($type === '' || $value === '') {
                continue;
            }
            $words = [$value, (string)($tag['tag_label'] ?? '')];
            $synonyms = $tag['synonyms'] ?? [];
            if (is_string($synonyms)) {
                $decoded = json_decode($synonyms, true);
                $synonyms = is_array($decoded) ? $decoded : [];
            }
            foreach ((array)$synonyms as $word) {
                $words[] = (string)$word;
            }
            foreach (array_filter(array_unique($words)) as $word) {
                $map[$word] = ['type' => $type, 'value' => $value];
            }
        }
        return $map ?: $this->defaultKeywordMap();
    }

    protected function defaultKeywordMap(): array
    {
        $defaults = [
            '火锅' => ['category', '火锅'],
            '涮肉' => ['category', '火锅'],
            '川菜' => ['category', '川菜'],
            '四川菜' => ['category', '川菜'],
            '烧烤' => ['category', '烧烤'],
            '烤串' => ['category', '烧烤'],
            '奶茶' => ['category', '奶茶'],
            '饮品' => ['category', '奶茶'],
            '快餐' => ['category', '快餐'],
            '简餐' => ['category', '快餐'],
            '日料' => ['category', '日料'],
            '寿司' => ['category', '日料'],
            '聚餐' => ['scene', '聚餐'],
            '约会' => ['scene', '约会'],
            '亲子' => ['scene', '亲子'],
            '带娃' => ['scene', '亲子'],
            '商务' => ['scene', '商务'],
            '请客' => ['scene', '商务'],
            '辣' => ['taste', '辣'],
            '麻辣' => ['taste', '辣'],
            '清淡' => ['taste', '清淡'],
            '不辣' => ['taste', '清淡'],
            '甜' => ['taste', '甜'],
            '包间' => ['facility', '包间'],
            '包厢' => ['facility', '包间'],
            '大桌' => ['facility', '大桌'],
            '宝宝椅' => ['facility', '宝宝椅'],
            '儿童椅' => ['facility', '宝宝椅'],
            '无烟' => ['facility', '无烟'],
            '便宜' => ['price', '30-60'],
            '不贵' => ['price', '30-60'],
            '实惠' => ['price', '30-60'],
            '高端' => ['price', '150+'],
        ];
        $map = [];
        foreach ($defaults as $word => $tag) {
            $map[$word] = ['type' => $tag[0], 'value' => $tag[1]];
        }
        return $map;
    }
}
