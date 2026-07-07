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

class AiResponseParser
{
    public function parseJson(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $candidates = [$text];
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $text, $match)) {
            $candidates[] = trim($match[1]);
        }
        if (preg_match('/\{[\s\S]*\}/', $text, $match)) {
            $candidates[] = trim($match[0]);
        }

        foreach ($candidates as $candidate) {
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return null;
    }

    public function bailianText(array $response): string
    {
        return (string)($response['output']['text'] ?? '');
    }

    public function bailianSessionId(array $response): string
    {
        return (string)($response['output']['session_id'] ?? '');
    }
}
