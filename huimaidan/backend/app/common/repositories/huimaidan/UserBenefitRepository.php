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

use app\common\model\store\order\StoreOrder;
use app\common\model\user\User;
use think\exception\ValidateException;

class UserBenefitRepository
{
    public function summary(int $uid): array
    {
        $query = StoreOrder::getDB()->where('uid', $uid)->where('order_scene', OrderRepository::ORDER_SCENE)
            ->where('paid', 1)->where('is_del', 0);
        return [
            'can_use' => true,
            'benefit_text' => '注册用户可使用惠买单到店优惠',
            'order_count' => (int)$query->count(),
            'pay_amount' => $this->money(StoreOrder::getDB()->where('uid', $uid)->where('order_scene', OrderRepository::ORDER_SCENE)->where('paid', 1)->where('is_del', 0)->sum('pay_price')),
            'saved_amount' => $this->money(StoreOrder::getDB()->where('uid', $uid)->where('order_scene', OrderRepository::ORDER_SCENE)->where('paid', 1)->where('is_del', 0)->sum('coupon_price')),
        ];
    }

    public function assets(int $uid): array
    {
        $user = User::getDB()->where('uid', $uid)
            ->field('uid,brokerage_price,integral,member_level')
            ->find();
        if (!$user) {
            throw new ValidateException('用户不存在');
        }
        $user->append(['total_coupon']);

        return $this->assetsPayload($user);
    }

    public function miniProgramSuccessPayload(array $data): array
    {
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $data,
        ];
    }

    protected function assetsPayload($user): array
    {
        return [
            'commission' => $this->money($this->rowValue($user, 'brokerage_price', '0.00')),
            'points' => (int)$this->rowValue($user, 'integral', 0),
            'couponCount' => (int)$this->rowValue($user, 'total_coupon', 0),
            'vipLevel' => (int)$this->rowValue($user, 'member_level', 0),
        ];
    }

    protected function rowValue($row, string $field, $default = null)
    {
        if (is_array($row)) {
            return array_key_exists($field, $row) ? $row[$field] : $default;
        }
        if ($row instanceof \ArrayAccess) {
            return isset($row[$field]) ? $row[$field] : $default;
        }
        if (is_object($row)) {
            return isset($row->{$field}) ? $row->{$field} : $default;
        }
        return $default;
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
