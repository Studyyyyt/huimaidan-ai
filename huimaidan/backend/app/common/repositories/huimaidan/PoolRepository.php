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

use app\common\dao\huimaidan\CapitalPoolDao;
use app\common\dao\huimaidan\PoolAlarmRecordDao;
use app\common\dao\huimaidan\PoolTransactionDao;
use app\common\model\system\merchant\Merchant;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\system\notice\SystemNoticeRepository;
use crmeb\services\LockService;
use crmeb\services\SwooleTaskService;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

/**
 * @mixin CapitalPoolDao
 */
class PoolRepository extends BaseRepository
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    const TRANS_RECHARGE = 1;
    const TRANS_DEDUCT = 2;
    const TRANS_ADJUST_IN = 3;
    const TRANS_ADJUST_OUT = 4;

    protected $transactionDao;
    protected $alarmRecordDao;
    protected $alarmFormatter;

    public function __construct(
        CapitalPoolDao $dao,
        PoolTransactionDao $transactionDao,
        PoolAlarmRecordDao $alarmRecordDao,
        PoolAlarmFormatter $alarmFormatter
    )
    {
        $this->dao = $dao;
        $this->transactionDao = $transactionDao;
        $this->alarmRecordDao = $alarmRecordDao;
        $this->alarmFormatter = $alarmFormatter;
    }

    public function getList(array $where, $page, $limit)
    {
        $where = $this->normalizePoolSearch($where);
        $query = $this->dao->search($where);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

    public function getOrCreateByMerId(int $merId)
    {
        if (!app()->make(MerchantRepository::class)->get($merId)) {
            throw new ValidateException('商户不存在');
        }
        $pool = $this->dao->getByMerId($merId);
        if ($pool) {
            return $pool;
        }

        try {
            return $this->dao->create([
                'mer_id' => $merId,
                'balance' => '0.00',
                'total_recharge' => '0.00',
                'total_consume' => '0.00',
                'status' => self::STATUS_ENABLED,
                'alarm_balance' => '100.00',
                'alarm_enabled' => 1,
            ]);
        } catch (\Throwable $e) {
            $pool = $this->dao->getByMerId($merId);
            if ($pool) {
                return $pool;
            }
            throw $e;
        }
    }

    public function detail(int $poolId, ?int $merId = null)
    {
        $where = ['pool_id' => $poolId];
        if (!is_null($merId)) {
            $where['mer_id'] = $merId;
        }
        $pool = $this->dao->getWhere($where);
        if (!$pool) {
            throw new ValidateException('垫资池不存在');
        }
        return $pool;
    }

    public function transactions(array $where, $page, $limit)
    {
        $query = $this->transactionDao->search($where);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

    public function alarms(array $where, $page, $limit)
    {
        $where['alarm_status'] = 1;
        return $this->getList($where, $page, $limit);
    }

    public function alarmRecords(array $where, $page, $limit)
    {
        $query = $this->alarmRecordDao->search($where);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

    public function recharge(int $poolId, $amount, string $remark = '', int $adminId = 0)
    {
        return $this->changeBalance($poolId, $amount, self::TRANS_RECHARGE, 0, $remark ?: '平台充值', $adminId);
    }

    public function adjust(int $poolId, $amount, int $type, string $remark = '', int $adminId = 0)
    {
        if (!in_array($type, [self::TRANS_ADJUST_IN, self::TRANS_ADJUST_OUT], true)) {
            throw new ValidateException('调整类型有误');
        }
        return $this->changeBalance($poolId, $amount, $type, 0, $remark ?: '平台调整', $adminId);
    }

    public function deduct(int $poolId, $amount, int $orderId)
    {
        $amount = $this->money($amount);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new ValidateException('垫资池扣减金额必须大于0');
        }

        $old = $this->transactionDao->getByOrder($orderId, self::TRANS_DEDUCT);
        if ($old) {
            return $old;
        }

        return app()->make(LockService::class)->exec('huimaidan.pool.' . $poolId, function () use ($poolId, $amount, $orderId) {
            return Db::transaction(function () use ($poolId, $amount, $orderId) {
                $old = $this->transactionDao->getByOrder($orderId, self::TRANS_DEDUCT);
                if ($old) {
                    return $old;
                }

                $pool = $this->dao->lockById($poolId);
                if (!$pool || (int)$pool->status !== self::STATUS_ENABLED) {
                    throw new ValidateException('该商家优惠暂不可用，请联系商家');
                }

                $before = $this->money($pool->balance);
                $after = $this->deductBalance($before, $amount, true);
                $pool->balance = $after;
                $pool->total_consume = $this->money(bcadd($pool->total_consume, $amount, 2));
                $pool->save();

                $transaction = $this->transactionDao->create([
                    'pool_id' => $pool->pool_id,
                    'mer_id' => $pool->mer_id,
                    'type' => self::TRANS_DEDUCT,
                    'amount' => $amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'order_id' => $orderId,
                    'remark' => '惠买单订单扣减',
                    'admin_id' => 0,
                ]);

                $this->checkAlarm($pool);

                return $transaction;
            });
        });
    }

    public function ensureUsable(int $poolId, $amount, ?int $merId = null)
    {
        $pool = $this->dao->get($poolId);
        if (!$pool || (int)$pool->status !== self::STATUS_ENABLED) {
            throw new ValidateException('该商家优惠暂不可用，请联系商家');
        }
        if (!is_null($merId) && (int)$pool->mer_id !== $merId) {
            throw new ValidateException('该商家优惠暂不可用，请联系商家');
        }
        return $pool;
    }

    public function saveAlarm(int $poolId, array $data, ?int $merId = null)
    {
        $pool = $this->detail($poolId, $merId);
        $update = [
            'alarm_enabled' => isset($data['alarm_enabled']) ? (int)$data['alarm_enabled'] : (int)$pool->alarm_enabled,
            'alarm_balance' => $this->money($data['alarm_balance'] ?? $pool->alarm_balance),
        ];
        $this->assertAlarmConfig($update);
        $this->dao->update($pool->pool_id, $update);
        return $this->dao->get($pool->pool_id);
    }

    public function batchSaveAlarm(array $poolIds, array $data): int
    {
        $poolIds = array_values(array_unique(array_map('intval', $poolIds)));
        if (!$poolIds || min($poolIds) <= 0) {
            throw new ValidateException('请选择垫资池');
        }
        $existingIds = array_map('intval', $this->dao->existingIds($poolIds));
        sort($poolIds);
        sort($existingIds);
        if ($poolIds !== $existingIds) {
            throw new ValidateException('垫资池不存在');
        }
        $update = [
            'alarm_enabled' => (int)$data['alarm_enabled'],
            'alarm_balance' => $this->money($data['alarm_balance']),
        ];
        $this->assertAlarmConfig($update);
        return $this->dao->updates($poolIds, $update);
    }

    public function changeStatus(int $poolId, int $status)
    {
        if (!in_array($status, [self::STATUS_DISABLED, self::STATUS_ENABLED], true)) {
            throw new ValidateException('垫资池状态有误');
        }
        $this->dao->update($poolId, ['status' => $status]);
    }

    protected function changeBalance(int $poolId, $amount, int $type, int $orderId = 0, string $remark = '', int $adminId = 0)
    {
        $amount = $this->money($amount);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new ValidateException('金额必须大于0');
        }

        return app()->make(LockService::class)->exec('huimaidan.pool.' . $poolId, function () use ($poolId, $amount, $type, $orderId, $remark, $adminId) {
            return Db::transaction(function () use ($poolId, $amount, $type, $orderId, $remark, $adminId) {
                $pool = $this->dao->lockById($poolId);
                if (!$pool) {
                    throw new ValidateException('垫资池不存在');
                }

                $before = $this->money($pool->balance);
                if (in_array($type, [self::TRANS_DEDUCT, self::TRANS_ADJUST_OUT], true)) {
                    $after = $this->deductBalance($before, $amount, $type === self::TRANS_DEDUCT);
                    $pool->total_consume = $this->money(bcadd($pool->total_consume, $amount, 2));
                } else {
                    $after = $this->money(bcadd($before, $amount, 2));
                    $pool->total_recharge = $this->money(bcadd($pool->total_recharge, $amount, 2));
                }

                $pool->balance = $after;
                $pool->save();

                return $this->transactionDao->create([
                    'pool_id' => $pool->pool_id,
                    'mer_id' => $pool->mer_id,
                    'type' => $type,
                    'amount' => $amount,
                    'balance_before' => $before,
                    'balance_after' => $after,
                    'order_id' => $orderId,
                    'remark' => $remark,
                    'admin_id' => $adminId,
                ]);
            });
        });
    }

    protected function checkAlarm($pool)
    {
        if (!$this->alarmFormatter->shouldNotify(
            $pool->balance,
            $pool->alarm_balance,
            (int)$pool->alarm_enabled,
            $pool->last_alarm_time
        )) {
            return;
        }

        $record = $this->alarmRecordDao->create($this->alarmFormatter->recordData(
            (int)$pool->pool_id,
            (int)$pool->mer_id,
            $pool->balance,
            $pool->alarm_balance,
            PoolAlarmFormatter::SOURCE_DEDUCT
        ));
        $this->dao->update($pool->pool_id, ['last_alarm_time' => date('Y-m-d H:i:s')]);

        try {
            app()->make(SystemNoticeRepository::class)->create([
                'notice_title' => '惠买单垫资池余额预警',
                'notice_content' => '您的惠买单垫资池余额已低于预警值，当前余额' . $pool->balance . '元，请及时补充。',
                'type' => 1,
                'mer_id' => [$pool->mer_id],
                'status' => 1,
            ], 0);
            SwooleTaskService::admin('notice', [
                'type' => 'huimaidan_pool_alarm',
                'data' => [
                    'title' => '惠买单垫资池余额预警',
                    'message' => '商户ID ' . $pool->mer_id . ' 的惠买单垫资池余额已低于预警值，请及时处理。',
                    'id' => (int)$pool->pool_id,
                ],
            ]);
            $this->alarmRecordDao->update($record->alarm_record_id, [
                'notice_status' => PoolAlarmFormatter::STATUS_SENT,
                'notice_message' => '商户站内信已写入，平台在线提示已触发',
            ]);
        } catch (\Throwable $e) {
            $this->alarmRecordDao->update($record->alarm_record_id, [
                'notice_status' => PoolAlarmFormatter::STATUS_FAILED,
                'notice_message' => mb_substr($e->getMessage(), 0, 255),
            ]);
            Log::error('HuimaidanPoolAlarm:通知失败', [
                'pool_id' => $pool->pool_id,
                'mer_id' => $pool->mer_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function normalizePoolSearch(array $where): array
    {
        if (!isset($where['keyword']) || trim((string)$where['keyword']) === '') {
            return $where;
        }
        $where['mer_ids'] = Merchant::getDB()
            ->whereLike('mer_name|real_name|mer_phone', '%' . trim((string)$where['keyword']) . '%')
            ->column('mer_id');
        return $where;
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }

    protected function deductBalance($before, $amount, bool $allowNegative = false): string
    {
        $before = $this->money($before);
        $amount = $this->money($amount);
        if (!$allowNegative && bccomp($before, $amount, 2) < 0) {
            throw new ValidateException('垫资池余额不足');
        }
        return $this->money(bcsub($before, $amount, 2));
    }

    protected function assertAlarmConfig(array $data): void
    {
        if (!in_array((int)$data['alarm_enabled'], [0, 1], true)) {
            throw new ValidateException('预警开关有误');
        }
        if (bccomp($data['alarm_balance'], '0.00', 2) < 0) {
            throw new ValidateException('预警金额不能小于0');
        }
    }
}
