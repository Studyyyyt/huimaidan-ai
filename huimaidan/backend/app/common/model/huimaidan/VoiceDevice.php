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

class VoiceDevice extends BaseModel
{
    // 状态常量
    const STATUS_ENABLE  = 1;
    const STATUS_DISABLE = 0;

    // 设备类型
    const TYPE_SANMUSEN = 1;

    // 绑定状态
    const BIND_NO  = 0;
    const BIND_YES = 1;

    public static function tablePk(): ?string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'merchant_voice_device';
    }

    /**
     * 关联商户
     */
    public function merchant()
    {
        return $this->hasOne(\app\common\model\system\merchant\Merchant::class, 'mer_id', 'mer_id')
            ->field('mer_id,mer_name');
    }

    /**
     * 获取器：状态文本
     */
    public function getStatusTextAttr($value, $data): string
    {
        $status = [
            self::STATUS_ENABLE  => '启用',
            self::STATUS_DISABLE => '禁用',
        ];
        return $status[$data['status']] ?? '未知';
    }

    /**
     * 获取器：绑定状态文本
     */
    public function getBindStatusTextAttr($value, $data): string
    {
        return $data['bind_status'] == self::BIND_YES ? '已绑定' : '未绑定';
    }
}
