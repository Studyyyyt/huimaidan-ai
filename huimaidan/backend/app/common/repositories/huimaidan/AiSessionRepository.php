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

use think\facade\Cache;

class AiSessionRepository
{
    public function readOrCreate(string $sessionId, int $uid, array $location = []): array
    {
        if ($sessionId !== '') {
            $session = Cache::get($this->key($sessionId));
            if (is_array($session) && (int)($session['uid'] ?? 0) === $uid) {
                return $session;
            }
        }
        $sessionId = $sessionId !== '' ? $sessionId : $this->newSessionId($uid);
        $session = [
            'session_id' => $sessionId,
            'uid' => $uid,
            'city_id' => (int)($location['city_id'] ?? 0),
            'lat' => $location['latitude'] ?? null,
            'lng' => $location['longitude'] ?? null,
            'history' => [],
            'last_mer_ids' => [],
        ];
        $this->save($session);
        return $session;
    }

    public function append(array $session, string $role, string $text, array $extra = []): array
    {
        $history = (array)($session['history'] ?? []);
        $history[] = array_merge([
            'role' => $role,
            'text' => $text,
            'time' => date('Y-m-d H:i:s'),
        ], $extra);
        $maxHistory = (int)config('huimaidan.ai.session.max_history', 5);
        $session['history'] = array_slice($history, -max(1, $maxHistory * 2));
        if (isset($extra['mer_ids'])) {
            $session['last_mer_ids'] = array_values(array_map('intval', (array)$extra['mer_ids']));
        }
        $this->save($session);
        return $session;
    }

    public function save(array $session): void
    {
        Cache::set($this->key((string)$session['session_id']), $session, (int)config('huimaidan.ai.session.ttl', 3600));
    }

    protected function key(string $sessionId): string
    {
        return 'ai:session:' . $sessionId;
    }

    protected function newSessionId(int $uid): string
    {
        return 'ai_' . $uid . '_' . date('YmdHis') . '_' . substr(md5(uniqid('', true)), 0, 8);
    }
}
