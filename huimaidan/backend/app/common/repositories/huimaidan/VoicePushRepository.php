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

use app\common\dao\huimaidan\VoiceDeviceDao;
use app\common\dao\huimaidan\VoicePushLogDao;
use app\common\model\huimaidan\VoiceDevice;
use app\common\model\huimaidan\VoicePushLog;
use app\common\repositories\BaseRepository;
use app\exception\ValidateException;
use app\jobs\huimaidan\VoicePushJob;
use think\facade\Log;
use think\facade\Queue;

class VoicePushRepository extends BaseRepository
{
    protected $logDao;
    protected $deviceDao;

    public function __construct(VoicePushLogDao $logDao, VoiceDeviceDao $deviceDao)
    {
        $this->logDao    = $logDao;
        $this->deviceDao = $deviceDao;
    }

    /**
     * 创建收款播报任务
     * 在订单支付成功后调用
     *
     * @param int    $merId    商户ID
     * @param int    $orderId  订单ID
     * @param string $orderSn  订单号
     * @param float  $amount   金额
     * @return void
     */
    public function createPayBroadcast(int $merId, int $orderId, string $orderSn, float $amount): void
    {
        // 获取商户所有启用的设备
        $devices = $this->deviceDao->getEnabledDevices($merId);
        if (empty($devices)) {
            return; // 无设备，跳过
        }

        $amountStr = number_format($amount, 2);
        $content   = "微信收款{$amountStr}元";

        foreach ($devices as $device) {
            // 创建播报日志
            $log = $this->logDao->create([
                'mer_id'       => $merId,
                'device_id'    => $device['id'],
                'device_sn'    => $device['device_sn'],
                'order_id'     => $orderId,
                'order_sn'     => $orderSn,
                'push_type'    => VoicePushLog::PUSH_TYPE_PAY,
                'push_content' => $content,
                'push_amount'  => $amount,
                'push_status'  => VoicePushLog::PUSH_STATUS_PENDING,
                'create_time'  => time(),
                'update_time'  => time(),
            ]);

            // 投递到队列异步处理
            Queue::push(VoicePushJob::class, [
                'log_id'    => $log->id,
                'device_id' => $device['id'],
            ], 'huimaidan_voice_push');
        }
    }

    /**
     * 创建测试播报任务
     */
    public function createTestBroadcast(int $merId, int $deviceId): int
    {
        $device = $this->deviceDao->get([
            'id'     => $deviceId,
            'mer_id' => $merId,
            'status' => VoiceDevice::STATUS_ENABLE,
            'is_del' => 0,
        ]);

        if (empty($device)) {
            throw new ValidateException('设备不存在或已禁用');
        }

        $content = '测试播报，收款0.01元';

        $log = $this->logDao->create([
            'mer_id'       => $merId,
            'device_id'    => $deviceId,
            'device_sn'    => $device->device_sn,
            'order_id'     => 0,
            'order_sn'     => '',
            'push_type'    => VoicePushLog::PUSH_TYPE_TEST,
            'push_content' => $content,
            'push_amount'  => 0.01,
            'push_status'  => VoicePushLog::PUSH_STATUS_PENDING,
            'create_time'  => time(),
            'update_time'  => time(),
        ]);

        // 同步推送测试播报
        $this->processPush($log->id);

        return $log->id;
    }

    /**
     * 处理播报推送
     */
    public function processPush(int $logId): bool
    {
        try {
            $log = $this->logDao->get(['id' => $logId]);
            if (empty($log) || (int)$log->push_status !== VoicePushLog::PUSH_STATUS_PENDING) {
                return false;
            }

            $device = $this->deviceDao->get(['id' => $log->device_id, 'is_del' => 0]);
            if (empty($device)) {
                $this->updateLogStatus($logId, VoicePushLog::PUSH_STATUS_FAILED, '设备不存在');
                return false;
            }

            // 更新为推送中
            $this->updateLogStatus($logId, VoicePushLog::PUSH_STATUS_PUSHING);

            // 调用三木森API推送
            $service  = app()->make(\crmeb\services\huimaidan\SanmusenVoiceService::class);
            $result   = $service->pushMessage($device->toArray(), $log->push_content, (float)$log->push_amount);
            $pushTime = time();

            if ($result['success']) {
                $this->logDao->update($logId, [
                    'push_status' => VoicePushLog::PUSH_STATUS_SUCCESS,
                    'push_time'   => $pushTime,
                    'update_time' => $pushTime,
                ]);

                // 更新设备最后播报时间和次数
                $this->deviceDao->update($device->id, [
                    'last_push_time'   => $pushTime,
                    'total_push_count' => $device->total_push_count + 1,
                    'update_time'      => $pushTime,
                ]);

                return true;
            } else {
                $this->updateLogStatus($logId, VoicePushLog::PUSH_STATUS_FAILED, $result['error'] ?? '推送失败');
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('播报推送处理异常: logId=' . $logId . ', error=' . $e->getMessage());
            $this->updateLogStatus($logId, VoicePushLog::PUSH_STATUS_FAILED, '系统异常: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 更新日志状态
     */
    protected function updateLogStatus(int $logId, int $status, string $errorMsg = ''): void
    {
        $data = [
            'push_status' => $status,
            'update_time' => time(),
        ];
        if ($errorMsg) {
            $data['error_msg'] = $errorMsg;
        }
        $this->logDao->update($logId, $data);
    }

    /**
     * 获取播报日志列表（分页）
     */
    public function getLogList(array $where, int $page, int $limit): array
    {
        $query = $this->logDao->search($where);
        $count = $query->count();
        $list  = $query->page($page, $limit)->select();

        return compact('count', 'list');
    }

    /**
     * 获取播报统计
     */
    public function getStatistics(int $merId): array
    {
        $todayStart = strtotime(date('Y-m-d'));
        $model      = VoicePushLog::getDB();

        $todayCount = (clone $model)->where('mer_id', $merId)
            ->where('create_time', '>=', $todayStart)
            ->where('push_status', VoicePushLog::PUSH_STATUS_SUCCESS)
            ->count();

        $totalCount = (clone $model)->where('mer_id', $merId)
            ->where('push_status', VoicePushLog::PUSH_STATUS_SUCCESS)
            ->count();

        $failCount = (clone $model)->where('mer_id', $merId)
            ->where('push_status', VoicePushLog::PUSH_STATUS_FAILED)
            ->where('create_time', '>=', $todayStart)
            ->count();

        return [
            'today_count' => $todayCount,
            'total_count' => $totalCount,
            'fail_count'  => $failCount,
        ];
    }
}
