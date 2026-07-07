<?php

namespace app\common\repositories\wechat;

use ArrayAccess;

class RoutinePhoneBindingPolicy
{
    public function requiresBinding($user): bool
    {
        if (!$user) {
            return true;
        }

        if (is_array($user) || $user instanceof ArrayAccess) {
            return empty($user['phone']);
        }

        return empty($user->phone);
    }

    public function switchValue(): string
    {
        return '1';
    }

    public function loginTypePayload($user, string $key): array
    {
        $bindPhone = $this->requiresBinding($user);

        return [
            'bindPhone' => $bindPhone,
            'key' => $bindPhone ? $key : '',
            'wechat_phone_switch' => $this->switchValue(),
        ];
    }
}
