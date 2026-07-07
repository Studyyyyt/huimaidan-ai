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

use app\common\dao\huimaidan\AiConfigDao;
use app\common\repositories\BaseRepository;

/**
 * @mixin AiConfigDao
 */
class AiConfigRepository extends BaseRepository
{
    public function __construct(AiConfigDao $dao)
    {
        $this->dao = $dao;
    }

    public function allKeyValue(): array
    {
        try {
            return $this->dao->search([])->column('config_value', 'config_key');
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function number(string $key, $default): float
    {
        $values = $this->allKeyValue();
        return isset($values[$key]) && is_numeric($values[$key]) ? (float)$values[$key] : (float)$default;
    }

    public function int(string $key, int $default): int
    {
        return (int)round($this->number($key, $default));
    }

    public function text(string $key, string $default = ''): string
    {
        $values = $this->allKeyValue();
        $value = isset($values[$key]) ? trim((string)$values[$key]) : '';
        return $value !== '' ? $value : $default;
    }

    public function scoreWeights(): array
    {
        $defaults = config('huimaidan.ai.score_weights', []);
        return [
            'tag' => $this->number('score_weight_tag', $defaults['tag'] ?? 0.35),
            'distance' => $this->number('score_weight_distance', $defaults['distance'] ?? 0.25),
            'heat' => $this->number('score_weight_heat', $defaults['heat'] ?? 0.25),
            'promo' => $this->number('score_weight_promo', $defaults['promo'] ?? 0.15),
        ];
    }
}
