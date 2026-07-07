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

class UserMerchantHistory extends BaseModel
{
    public static function tablePk(): ?string
    {
        return 'user_merchant_history_id';
    }

    public static function tableName(): string
    {
        return 'user_merchant_history';
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'mer_id', 'mer_id');
    }
}
