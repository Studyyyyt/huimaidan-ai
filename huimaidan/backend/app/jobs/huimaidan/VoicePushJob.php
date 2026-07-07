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

namespace app\jobs\huimaidan;

use app\common\repositories\huimaidan\VoicePushRepository;
use think\facade\Log;
use think\queue\Job;

class VoicePushJob
{
    /**
     * 任务执行
     */
    public function fire(Job $job, array $data): void
    {
        $logId = $data['log_id'] ?? 0;
        if (!$logId) {
            $job->delete();
            return;
        }

        try {
            $repository = app()->make(VoicePushRepository::class);
            $result     = $repository->processPush($logId);

            if ($result) {
                $job->delete();
            } else {
                $this->retryJob($job, $data);
            }
        } catch (\Throwable $e) {
            Log::error('语音播报任务执行异常: ' . $e->getMessage());
            $this->retryJob($job, $data);
        }
    }

    /**
     * 任务失败处理
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('语音播报任务异常: ' . $exception->getMessage());
    }

    /**
     * 重试任务
     */
    protected function retryJob(Job $job, array $data): void
    {
        $job->release(30); // 30秒后重试
    }
}
