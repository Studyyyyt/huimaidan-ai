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

use think\exception\ValidateException;

class PoolRulePolicy
{
    public function poolId($poolId): int
    {
        $poolId = (int)$poolId;
        if ($poolId <= 0) {
            throw new ValidateException('惠买单优惠规则必须绑定垫资池');
        }
        return $poolId;
    }

    public function usableRules(array $rules): array
    {
        return array_values(array_filter($rules, function (array $rule) {
            return !empty($rule['pool_id'])
                && !empty($rule['pool'])
                && (int)$rule['pool']['status'] === PoolRepository::STATUS_ENABLED;
        }));
    }
}
