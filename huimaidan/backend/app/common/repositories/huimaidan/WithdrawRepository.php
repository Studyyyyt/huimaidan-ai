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

use app\common\dao\system\financial\FinancialDao;
use app\common\repositories\system\financial\FinancialRepository;
use app\common\repositories\system\merchant\FinancialRecordRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use think\exception\ValidateException;
use think\facade\Db;

class WithdrawRepository
{
    const BUSINESS_TYPE = 'huimaidan';
    const TRADE_CHANNEL = 'huimaidan_withdraw';
    const ACCOUNT_WECHAT = 2;
    const ACCOUNT_ALIPAY = 3;
    const DEFAULT_MIN_EXTRACT_MONEY = '500.00';

    protected $financialDao;
    protected $merchantRepository;

    public function __construct(FinancialDao $financialDao, MerchantRepository $merchantRepository)
    {
        $this->financialDao = $financialDao;
        $this->merchantRepository = $merchantRepository;
    }

    public function overview(int $merId): array
    {
        $merchant = $this->withdrawMerchant($merId);
        $current = $this->current($merId);
        $financialType = (int)($merchant->financial_type ?? 0);
        return [
            'mer_id' => $merId,
            'settlement_mode' => (int)$merchant->huimaidan_settlement_mode,
            'mer_money' => $this->money($merchant->mer_money),
            'withdraw_rate' => $this->money($merchant->huimaidan_withdraw_rate),
            'min_extract_money' => $this->money($merchant->huimaidan_min_extract_money ?? self::DEFAULT_MIN_EXTRACT_MONEY),
            'account_type' => $financialType,
            'account_type_label' => $this->accountTypeLabel($financialType),
            'has_account' => $this->hasAccount($merchant, $financialType),
            'has_unfinished_apply' => $current ? 1 : 0,
            'current' => $current,
        ];
    }

    public function current(int $merId)
    {
        return $this->financialDao->search($this->unfinishedStatusWhere($merId))
            ->where(function ($query) {
                $query->where('Financial.status', 0)
                    ->whereOr(function ($query) {
                        $query->where('Financial.status', 1)->where('Financial.financial_status', 0);
                    });
            })->find();
    }

    public function currentData(int $merId): array
    {
        return $this->currentPayload($this->current($merId));
    }

    public function records(int $merId, array $where, int $page, int $limit): array
    {
        $where['mer_id'] = $merId;
        $where['business_type'] = self::BUSINESS_TYPE;
        $where['is_del'] = 0;
        $query = $this->financialDao->search($where);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

    /**
     * 商户端提现记录列表（格式化版本）
     * @param int $merId 商户ID
     * @param array $where 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     */
    public function list(int $merId, array $where, int $page, int $limit): array
    {
        $where['mer_id'] = $merId;
        $where['business_type'] = self::BUSINESS_TYPE;
        $where['is_del'] = 0;
        $query = $this->financialDao->search($where);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        // 格式化数据以适配前端需求
        $formattedList = [];
        foreach ($list as $item) {
            $formattedList[] = $this->formatWithdrawRecord($item);
        }

        return ['count' => $count, 'list' => $formattedList];
    }

    /**
     * 格式化提现记录
     * @param mixed $item Financial模型对象
     * @return array
     */
    protected function formatWithdrawRecord($item): array
    {
        // 提现方式映射
        $financialTypeMap = [
            1 => '银行卡',
            2 => '微信',
            3 => '支付宝',
        ];

        // 状态映射：综合 status 和 financial_status
        $status = (int)$item->status;
        $financialStatus = (int)$item->financial_status;

        if ($status === 0) {
            $statusText = '处理中';
            $statusCode = 0;
        } elseif ($status === -1) {
            $statusText = '已拒绝';
            $statusCode = 2;
        } elseif ($status === 1 && $financialStatus === 0) {
            $statusText = '待打款';
            $statusCode = 0;
        } elseif ($status === 1 && $financialStatus === 1) {
            $statusText = '成功';
            $statusCode = 1;
        } else {
            $statusText = '处理中';
            $statusCode = 0;
        }

        // 提取银行卡后四位
        $cardLastFour = '';
        $financialType = (int)$item->financial_type;
        if ($financialType === 1 && !empty($item->financial_account)) {
            $account = is_string($item->financial_account) ? json_decode($item->financial_account, true) : $item->financial_account;
            if (isset($account['card_no']) && strlen($account['card_no']) >= 4) {
                $cardLastFour = substr($account['card_no'], -4);
            }
        }

        return [
            'id' => (int)$item->financial_id,
            'amount' => $this->money($item->extract_money),
            'balance' => $this->money($item->mer_money),
            'financial_type' => $financialType,
            'financial_type_text' => $financialTypeMap[$financialType] ?? '未知',
            'card_last_four' => $cardLastFour,
            'status' => $statusCode,
            'status_text' => $statusText,
            'mark' => $item->mark ?? '',
            'create_time' => $item->create_time,
        ];
    }

    public function saveAccount(int $merId, array $data): void
    {
        $financialType = (int)($data['financial_type'] ?? 0);
        $this->assertAccountType($financialType);
        if ($financialType === self::ACCOUNT_WECHAT && (empty($data['name']) || empty($data['wechat']) || empty($data['wechat_code']))) {
            throw new ValidateException('请填写微信收款人、微信号和收款二维码');
        }
        if ($financialType === self::ACCOUNT_ALIPAY && (empty($data['name']) || empty($data['alipay']) || empty($data['alipay_code']))) {
            throw new ValidateException('请填写支付宝收款人、支付宝账号和收款二维码');
        }
        $this->withdrawMerchant($merId);
        app()->make(FinancialRepository::class)->saveAccount($merId, $data);
    }

    public function apply(int $merId, array $data)
    {
        return Db::transaction(function () use ($merId, $data) {
            $merchant = $this->withdrawMerchant($merId, true);
            if ($this->current($merId)) {
                throw new ValidateException('当前存在未完成提现申请，请等待平台处理后再申请');
            }
            $extractMoney = $this->money($data['extract_money'] ?? 0);
            $minExtractMoney = $this->money($merchant->huimaidan_min_extract_money ?? self::DEFAULT_MIN_EXTRACT_MONEY);
            if (bccomp($extractMoney, $minExtractMoney, 2) < 0) {
                throw new ValidateException('惠买单最低提现金额' . $minExtractMoney . '元');
            }
            if (bccomp($extractMoney, $this->money($merchant->mer_money), 2) > 0) {
                throw new ValidateException('提现金额大于可提现余额');
            }

            $financialType = (int)($data['financial_type'] ?? ($merchant->financial_type ?? 0));
            $this->assertAccountType($financialType);
            $account = $this->account($merchant, $financialType);
            if (!$account) {
                throw new ValidateException('请先配置微信或支付宝收款码');
            }

            $fee = $this->feeAmounts($extractMoney, $merchant->huimaidan_withdraw_rate);
            $leftMoney = bcsub($this->money($merchant->mer_money), $extractMoney, 2);
            $ret = [
                'status' => 0,
                'mer_id' => $merId,
                'mer_money' => $leftMoney,
                'financial_sn' => date('YmdHis') . $merId,
                'extract_money' => $extractMoney,
                'financial_type' => $financialType,
                'financial_account' => json_encode($account, JSON_UNESCAPED_UNICODE),
                'financial_status' => 0,
                'mer_admin_id' => (int)($data['mer_admin_id'] ?? 0),
                'mark' => $data['mark'] ?? '',
                'refusal' => '',
                'business_type' => self::BUSINESS_TYPE,
                'fee_rate' => $this->money($merchant->huimaidan_withdraw_rate),
                'fee_amount' => $fee['fee_amount'],
                'real_transfer_amount' => $fee['real_transfer_amount'],
                'account_type' => $financialType,
                'trade_channel' => self::TRADE_CHANNEL,
                'audit_remark' => '',
                'type' => 0,
            ];
            $financial = $this->financialDao->create($ret);
            $merchant->mer_money = $leftMoney;
            $merchant->save();
            return $financial;
        });
    }

    public function adminList(array $where, int $page, int $limit): array
    {
        $where['business_type'] = self::BUSINESS_TYPE;
        $where['is_del'] = 0;
        $query = $this->financialDao->search($where)->with(['merchant' => function ($query) {
            $query->field('mer_id,mer_name,mer_avatar,mer_phone,mer_money,huimaidan_settlement_mode,huimaidan_withdraw_rate');
        }]);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

    public function detail(int $id, ?int $merId = null)
    {
        $where = ['financial_id' => $id, 'business_type' => self::BUSINESS_TYPE, 'is_del' => 0];
        if ($merId) {
            $where['mer_id'] = $merId;
        }
        $data = $this->financialDao->search($where)->with(['merchant' => function ($query) {
            $query->field('mer_id,mer_name,mer_avatar,mer_phone,mer_money');
        }])->find();
        if (!$data) {
            throw new ValidateException('惠买单提现申请不存在');
        }
        return $data;
    }

    public function audit(int $id, int $status, array $data): void
    {
        if (!in_array($status, [1, -1], true)) {
            throw new ValidateException('审核状态错误');
        }
        if ($status === -1 && empty($data['refusal'])) {
            throw new ValidateException('请输入拒绝理由');
        }
        Db::transaction(function () use ($id, $status, $data) {
            $financial = $this->detail($id);
            if ((int)$financial->status !== 0) {
                throw new ValidateException('请勿重复审核');
            }
            $update = [
                'status' => $status,
                'status_time' => date('Y-m-d H:i:s'),
                'admin_id' => (int)($data['admin_id'] ?? 0),
                'refusal' => $status === -1 ? $data['refusal'] : '',
                'audit_remark' => $data['audit_remark'] ?? '',
            ];
            if ($status === -1) {
                $merchant = $this->merchantRepository->get((int)$financial->mer_id);
                if (!$merchant) {
                    throw new ValidateException('商户不存在');
                }
                $merchant->mer_money = bcadd((string)$merchant->mer_money, $this->money($financial->extract_money), 2);
                $merchant->save();
                app()->make(FinancialRecordRepository::class)->inc($this->rejectReturnRecord($financial, $merchant->mer_money), (int)$financial->mer_id);
            }
            $this->financialDao->update($id, $update);
        });
    }

    public function transfer(int $id, array $image, int $adminId): void
    {
        if (!$image) {
            throw new ValidateException('请上传打款凭证');
        }
        Db::transaction(function () use ($id, $image, $adminId) {
            $financial = $this->detail($id);
            if ((int)$financial->status !== 1 || (int)$financial->financial_status === 1) {
                throw new ValidateException('当前状态无法完成打款');
            }
            if (bccomp($this->money($financial->fee_amount), '0.00', 2) > 0) {
                app()->make(FinancialRecordRepository::class)->dec($this->withdrawFeeRecord($financial), (int)$financial->mer_id);
            }
            $this->financialDao->update($id, [
                'image' => implode(',', $image),
                'admin_id' => $adminId,
                'update_time' => date('Y-m-d H:i:s'),
                'financial_status' => 1,
            ]);
        });
    }

    public function stats(array $where): array
    {
        $where['business_type'] = self::BUSINESS_TYPE;
        $where['is_del'] = 0;
        $base = $this->financialDao->search($where);
        return [
            'pending_audit_amount' => $this->money($this->financialDao->search($where)->where('Financial.status', 0)->sum('extract_money')),
            'pending_transfer_amount' => $this->money($this->financialDao->search($where)->where('Financial.status', 1)->where('Financial.financial_status', 0)->sum('extract_money')),
            'finished_amount' => $this->money($this->financialDao->search($where)->where('Financial.status', 1)->where('Financial.financial_status', 1)->sum('extract_money')),
            'finished_fee_amount' => $this->money($this->financialDao->search($where)->where('Financial.status', 1)->where('Financial.financial_status', 1)->sum('fee_amount')),
            'wechat_count' => (int)$this->financialDao->search($where)->where('Financial.account_type', self::ACCOUNT_WECHAT)->count(),
            'wechat_amount' => $this->money($this->financialDao->search($where)->where('Financial.account_type', self::ACCOUNT_WECHAT)->sum('extract_money')),
            'alipay_count' => (int)$this->financialDao->search($where)->where('Financial.account_type', self::ACCOUNT_ALIPAY)->count(),
            'alipay_amount' => $this->money($this->financialDao->search($where)->where('Financial.account_type', self::ACCOUNT_ALIPAY)->sum('extract_money')),
            'total_count' => (int)$base->count(),
        ];
    }

    protected function withdrawMerchant(int $merId, bool $lock = false)
    {
        $query = $this->merchantRepository->getSearch(['mer_id' => $merId])->field('mer_id,mer_name,mer_money,financial_bank,financial_wechat,financial_alipay,financial_type,huimaidan_settlement_mode,huimaidan_withdraw_rate,huimaidan_min_extract_money');
        if ($lock) {
            $query->lock(true);
        }
        $merchant = $query->find();
        if (!$merchant) {
            throw new ValidateException('商户不存在');
        }
        if ((int)$merchant->huimaidan_settlement_mode !== MerchantRepository::HUIMAIDAN_SETTLEMENT_WITHDRAW) {
            throw new ValidateException('当前商户不是惠买单提现模式');
        }
        return $merchant;
    }

    protected function assertAccountType(int $financialType): void
    {
        if (!in_array($financialType, [self::ACCOUNT_WECHAT, self::ACCOUNT_ALIPAY], true)) {
            throw new ValidateException('惠买单提现仅支持微信或支付宝收款码');
        }
    }

    protected function hasAccount($merchant, int $financialType): bool
    {
        return (bool)$this->account($merchant, $financialType);
    }

    protected function account($merchant, int $financialType)
    {
        if ($financialType === self::ACCOUNT_WECHAT) {
            return $merchant->financial_wechat ?? null;
        }
        if ($financialType === self::ACCOUNT_ALIPAY) {
            return $merchant->financial_alipay ?? null;
        }
        return null;
    }

    protected function accountTypeLabel(int $financialType): string
    {
        return $financialType === self::ACCOUNT_WECHAT ? '微信' : ($financialType === self::ACCOUNT_ALIPAY ? '支付宝' : '');
    }

    protected function feeAmounts($extractMoney, $rate): array
    {
        $extractMoney = $this->money($extractMoney);
        $rate = $this->money($rate);
        $feeAmount = $this->money(bcdiv(bcmul($extractMoney, $rate, 4), '100', 4));
        return [
            'fee_amount' => $feeAmount,
            'real_transfer_amount' => $this->money(bcsub($extractMoney, $feeAmount, 4)),
        ];
    }

    protected function unfinishedStatusWhere(int $merId): array
    {
        return [
            'business_type' => self::BUSINESS_TYPE,
            'mer_id' => $merId,
            'is_del' => 0,
            'unfinished_status' => [[0, 0], [1, 0]],
        ];
    }

    protected function currentPayload($current): array
    {
        return ['current' => $current];
    }

    protected function rejectReturnRecord($financial, $balance): array
    {
        return [
            'order_id' => (int)$financial->financial_id,
            'order_sn' => (string)$financial->financial_sn,
            'user_info' => '惠买单提现驳回',
            'user_id' => 0,
            'financial_type' => 'huimaidan_withdraw_reject',
            'number' => $this->money($financial->extract_money),
            'type' => 0,
            'mer_id' => (int)$financial->mer_id,
            'pay_type' => 0,
        ];
    }

    protected function withdrawFeeRecord($financial): array
    {
        return [
            'order_id' => (int)$financial->financial_id,
            'order_sn' => (string)$financial->financial_sn,
            'user_info' => '惠买单提现手续费',
            'user_id' => 0,
            'financial_type' => 'huimaidan_withdraw_fee',
            'number' => $this->money($financial->fee_amount),
            'type' => 0,
            'mer_id' => (int)$financial->mer_id,
            'pay_type' => 0,
        ];
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
