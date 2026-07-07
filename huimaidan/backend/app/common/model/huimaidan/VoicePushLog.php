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

namespace app\common\model\huimaidan;

use app\common\model\BaseModel;

class VoicePushLog extends BaseModel
{
    // 推送状态
    const PUSH_STATUS_PENDING  = 0; // 待推送
    const PUSH_STATUS_PUSHING  = 1; // 推送中
    const PUSH_STATUS_SUCCESS  = 2; // 成功
    const PUSH_STATUS_FAILED   = 3; // 失败

    // 播报类型
    const PUSH_TYPE_PAY  = 1; // 收款播报
    const PUSH_TYPE_TEST = 2; // 测试播报

    public static function tablePk(): ?string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'voice_push_log';
    }

    /**
     * 关联设备
     */
    public function device()
    {
        return $this->hasOne(VoiceDevice::class, 'id', 'device_id');
    }
}
