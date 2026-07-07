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
use app\common\model\system\merchant\Merchant;

class StoreQrcode extends BaseModel
{
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;

    const GENERATE_FAIL = 0;
    const GENERATE_SUCCESS = 1;

    public static function tablePk(): ?string
    {
        return 'id';
    }

    public static function tableName(): string
    {
        return 'huimaidan_store_qrcode';
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'mer_id', 'mer_id')
            ->field('mer_id,mer_name,mer_avatar,status,mer_state,is_del');
    }

    public function getStatusTextAttr($value, $data): string
    {
        return ((int)($data['status'] ?? self::STATUS_DISABLE) === self::STATUS_ENABLE) ? '可用' : '禁用';
    }

    public function getLastGenerateStatusTextAttr($value, $data): string
    {
        return ((int)($data['last_generate_status'] ?? self::GENERATE_FAIL) === self::GENERATE_SUCCESS) ? '生成成功' : '生成失败';
    }
}
