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

class AiUserLearningRepository
{
    protected $logRepository;

    public function __construct(AiRecommendLogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    public function trackEvent(int $uid, array $data): array
    {
        $updated = $this->logRepository->trackEvent($uid, $data);
        return [
            'updated' => $updated,
            'preference_updated' => false,
            'preference_message' => '用户长期偏好画像本期仅预留，暂不自动更新',
        ];
    }
}
