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

class PoolAlarmFormatter
{
    const SOURCE_DEDUCT = 'deduct';

    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_FAILED = 2;

    const THROTTLE_SECONDS = 1800;

    public function shouldNotify($balance, $alarmBalance, int $enabled, ?string $lastAlarmTime, ?int $now = null): bool
    {
        if (!$enabled || bccomp($this->money($balance), $this->money($alarmBalance), 2) > 0) {
            return false;
        }
        if (!$lastAlarmTime) {
            return true;
        }
        $now = $now ?: time();
        return strtotime($lastAlarmTime) <= $now - self::THROTTLE_SECONDS;
    }

    public function recordData(int $poolId, int $merId, $balance, $alarmBalance, string $source): array
    {
        return [
            'pool_id' => $poolId,
            'mer_id' => $merId,
            'balance' => $this->money($balance),
            'alarm_balance' => $this->money($alarmBalance),
            'source' => $source,
            'notice_status' => self::STATUS_PENDING,
            'notice_message' => '',
        ];
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
