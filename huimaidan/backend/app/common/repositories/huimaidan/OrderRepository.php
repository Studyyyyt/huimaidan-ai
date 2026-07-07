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

use app\common\model\store\order\StoreGroupOrder;
use app\common\model\store\order\StoreOrder;
use app\common\model\store\order\StoreRefundOrder;
use app\common\model\system\merchant\Merchant;
use app\common\model\user\User;
use app\common\repositories\store\order\StoreGroupOrderRepository;
use app\common\repositories\store\order\StoreOrderRepository as CrmebStoreOrderRepository;
use app\common\repositories\store\order\StoreOrderStatusRepository;
use app\common\repositories\store\coupon\StoreCouponUserRepository;
use app\common\repositories\system\merchant\FinancialRecordRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\user\UserBillRepository;
use app\common\repositories\user\UserBrokerageRepository;
use app\common\repositories\user\UserRepository;
use crmeb\jobs\UserBrokerageLevelJob;
use crmeb\services\LockService;
use crmeb\services\PayService;
use crmeb\services\PayStatusService;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;
use think\facade\Queue;

class OrderRepository
{
    const ORDER_SCENE = 1;
    const PAY_ATTACH = 'huimaidan_order';
    const CONFIG_DISCOUNT_STACK = 'huimaidan_discount_stack_enabled';

    protected $groupOrderRepository;
    protected $storeOrderRepository;
    protected $discountEngine;
    protected $poolRepository;
    protected $poolRulePolicy;

    public function __construct(
        StoreGroupOrderRepository $groupOrderRepository,
        CrmebStoreOrderRepository $storeOrderRepository,
        DiscountEngineRepository $discountEngine,
        PoolRepository $poolRepository,
        PoolRulePolicy $poolRulePolicy
    ) {
        $this->groupOrderRepository = $groupOrderRepository;
        $this->storeOrderRepository = $storeOrderRepository;
        $this->discountEngine = $discountEngine;
        $this->poolRepository = $poolRepository;
        $this->poolRulePolicy = $poolRulePolicy;
    }

    public function create(User $user, array $data): array
    {
        $payType = $data['pay_type'] ?? '';
        $payTypeId = $this->payTypeId($payType);
        return $this->storePendingOrder($user, $data, $payTypeId);
    }

    public function createCombined(User $user, array $data): array
    {
        $payType = $data['pay_type'] ?? '';
        $payTypeId = $this->payTypeId($payType);
        return $this->storeCombinedOrder($user, $data, $payTypeId);
    }

    public function prepare(User $user, array $data): array
    {
        $this->assertNoUnsupportedMiniProgramPrepareFields($data);
        return $this->preparePayload($this->storePendingOrder($user, $data, 0));
    }

    protected function storePendingOrder(User $user, array $data, int $payTypeId): array
    {
        $amount = $this->money($data['amount']);
        $merId = (int)$data['mer_id'];

        return app()->make(LockService::class)->exec('huimaidan.order.create.' . $user->uid, function () use ($user, $data, $payTypeId, $amount, $merId) {
            $discount = $this->discountEngine->calculate(
                $merId,
                $amount,
                (int)$user->uid,
                $this->discountInputs($data)['use_member_discount']
            );
            $deduction = $this->buildDeductionAdjustment($user, $merId, $amount, $discount, $data);
            $discount = $deduction['discount'];
            $merchant = app()->make(MerchantRepository::class)->get($merId);
            if (!$merchant) {
                throw new ValidateException('商户不存在');
            }
            $orderSn = $this->storeOrderRepository->getNewOrderId(CrmebStoreOrderRepository::TYPE_SN_ORDER) . 'H';

            $groupData = [
                'uid' => $user->uid,
                'group_order_sn' => $orderSn,
                'total_postage' => '0.00',
                'total_price' => $amount,
                'total_num' => 1,
                'integral' => $deduction['integral'],
                'integral_price' => $deduction['integral_price'],
                'give_integral' => 0,
                'coupon_price' => $discount['saved_amount'],
                'real_name' => $user->real_name ?? ($user->nickname ?? ''),
                'user_phone' => $user->phone ?? '',
                'user_address' => '',
                'pay_price' => $discount['pay_amount'],
                'pay_postage' => '0.00',
                'cost' => $discount['merchant_cost_amount'],
                'paid' => 0,
                'pay_type' => $payTypeId,
                'activity_type' => 0,
                'coupon_id' => $deduction['coupon_user_id'] > 0 ? (string)$deduction['coupon_user_id'] : '',
            ];

            $orderData = [
                'group_order_id' => 0,
                'order_sn' => $orderSn,
                'uid' => $user->uid,
                'spread_uid' => $user->spread_uid ?? 0,
                'top_uid' => $user->top_uid ?? 0,
                'real_name' => $user->real_name ?? ($user->nickname ?? ''),
                'user_phone' => $user->phone ?? '',
                'user_address' => '',
                'cart_id' => '',
                'total_num' => 1,
                'total_price' => $amount,
                'total_postage' => '0.00',
                'pay_price' => $discount['pay_amount'],
                'pay_postage' => '0.00',
                'integral' => $deduction['integral'],
                'integral_price' => $deduction['integral_price'],
                'give_integral' => 0,
                'coupon_id' => $deduction['coupon_user_id'] > 0 ? (string)$deduction['coupon_user_id'] : '',
                'coupon_price' => $discount['saved_amount'],
                'platform_coupon_price' => $deduction['coupon_deduction_amount'],
                'order_type' => 0,
                'paid' => 0,
                'pay_type' => $payTypeId,
                'status' => 0,
                'is_virtual' => 1,
                'mark' => $data['mark'] ?? '',
                'activity_type' => 0,
                'order_extend' => '',
                'mer_id' => $merId,
                'cost' => $discount['merchant_cost_amount'],
                'refund_switch' => 0,
                'merchant_take_info' => '',
                'pool_id' => $discount['pool_id'],
                'merchant_cost_amount' => $discount['merchant_cost_amount'],
                'platform_profit' => $discount['platform_profit'],
                'discount_snapshot' => json_encode($discount['snapshot'], JSON_UNESCAPED_UNICODE),
                'pool_transaction_id' => null,
                'order_scene' => self::ORDER_SCENE,
            ] + $this->settlementFields($merchant);

            return Db::transaction(function () use ($groupData, $orderData, $user, $deduction) {
                if ($deduction['coupon_user_id'] > 0) {
                    app()->make(StoreCouponUserRepository::class)->updates([$deduction['coupon_user_id']], [
                        'use_time' => date('Y-m-d H:i:s'),
                        'status' => 1,
                    ]);
                }
                $groupOrder = $this->groupOrderRepository->create($groupData);
                if ($deduction['integral'] > 0) {
                    $lockedUser = User::where('uid', $user->uid)->lock(true)->find();
                    if (!$lockedUser || bccomp((string)$lockedUser->integral, (string)$deduction['integral'], 0) < 0) {
                        throw new ValidateException('积分不足');
                    }
                    $lockedUser->integral = bcsub((string)$lockedUser->integral, (string)$deduction['integral'], 0);
                    $lockedUser->save();
                    app()->make(UserBillRepository::class)->decBill((int)$user->uid, 'integral', 'deduction', [
                        'link_id' => $groupOrder->group_order_id,
                        'status' => 1,
                        'title' => '惠买单买单',
                        'number' => $deduction['integral'],
                        'mark' => '惠买单订单使用积分抵扣' . floatval($deduction['integral_price']) . '元',
                        'balance' => $lockedUser->integral,
                    ]);
                }
                $orderData['group_order_id'] = $groupOrder->group_order_id;
                $order = $this->storeOrderRepository->create($orderData);

                app()->make(StoreOrderStatusRepository::class)->batchCreateLog([[
                    'order_id' => $order->order_id,
                    'order_sn' => $order->order_sn,
                    'type' => StoreOrderStatusRepository::TYPE_ORDER,
                    'change_message' => '惠买单订单生成',
                    'change_type' => StoreOrderStatusRepository::ORDER_STATUS_CREATE,
                    'uid' => $user->uid,
                    'nickname' => $user->nickname ?? '',
                    'user_type' => StoreOrderStatusRepository::U_TYPE_USER,
                ]]);

                return [
                    'group_order_id' => $groupOrder->group_order_id,
                    'order_id' => $order->order_id,
                    'order_sn' => $order->order_sn,
                    'pay_price' => $groupOrder->pay_price,
                    'discount' => $this->publicDiscountSnapshot(json_decode($order->discount_snapshot, true) ?: []),
                ];
            });
        });
    }

    protected function storeCombinedOrder(User $user, array $data, int $payTypeId): array
    {
        $discountAmount = $this->money($data['discount_amount'] ?? 0);
        $noDiscountAmount = $this->money($data['no_discount_amount'] ?? 0);
        $merId = (int)$data['mer_id'];

        if (bccomp($discountAmount, '0.00', 2) <= 0 && bccomp($noDiscountAmount, '0.00', 2) <= 0) {
            throw new ValidateException('优惠金额和不参与优惠金额不能同时为空或<=0');
        }

        return app()->make(LockService::class)->exec('huimaidan.order.create.' . $user->uid, function () use ($user, $data, $payTypeId, $discountAmount, $noDiscountAmount, $merId) {
            $merchant = app()->make(MerchantRepository::class)->get($merId);
            if (!$merchant) {
                throw new ValidateException('商户不存在');
            }

            $totalAmount = $this->money(bcadd($discountAmount, $noDiscountAmount, 2));

            // 只对优惠金额部分计算会员折扣和优惠券/积分抵扣
            $discount = ['pay_amount' => $discountAmount, 'saved_amount' => '0.00', 'merchant_cost_amount' => '0.00', 'platform_profit' => '0.00', 'pool_id' => null, 'snapshot' => []];
            $couponUserId = 0;
            $couponDeductionAmount = '0.00';
            $integral = 0;
            $integralPrice = '0.00';

            if (bccomp($discountAmount, '0.00', 2) > 0) {
                // 1. 计算会员折扣（只对优惠金额部分）
                $discount = $this->discountEngine->calculate(
                    $merId,
                    $discountAmount,
                    (int)$user->uid,
                    $this->discountInputs($data)['use_member_discount']
                );

                // 2. 优惠券 + 积分抵扣（只对优惠金额部分）
                $deduction = $this->buildDeductionAdjustment($user, $merId, $discountAmount, $discount, $data);
                $discount = $deduction['discount'];
                $couponUserId = $deduction['coupon_user_id'];
                $couponDeductionAmount = $deduction['coupon_deduction_amount'];
                $integral = $deduction['integral'];
                $integralPrice = $deduction['integral_price'];
            }

            // 最终支付金额 = 优惠金额折后 + 不参与优惠金额
            $payPrice = $this->money(bcadd($discount['pay_amount'], $noDiscountAmount, 2));

            // 商户成本 = 优惠金额部分的商户成本 + 不参与优惠金额部分的商户成本(0，平台不抽成)
            $merchantCostAmount = $discount['merchant_cost_amount'];
            $platformProfit = $discount['platform_profit'];

            // 构建优惠快照，增加 no_discount_amount 信息
            $snapshot = $discount['snapshot'] ?? [];
            $snapshot['discount_amount'] = $discountAmount;
            $snapshot['no_discount_amount'] = $noDiscountAmount;

            $orderSn = $this->storeOrderRepository->getNewOrderId(CrmebStoreOrderRepository::TYPE_SN_ORDER) . 'H';

            $groupData = [
                'uid' => $user->uid,
                'group_order_sn' => $orderSn,
                'total_postage' => '0.00',
                'total_price' => $totalAmount,
                'total_num' => 1,
                'integral' => $integral,
                'integral_price' => $integralPrice,
                'give_integral' => 0,
                'coupon_price' => $discount['saved_amount'],
                'real_name' => $user->real_name ?? ($user->nickname ?? ''),
                'user_phone' => $user->phone ?? '',
                'user_address' => '',
                'pay_price' => $payPrice,
                'pay_postage' => '0.00',
                'cost' => $merchantCostAmount,
                'paid' => 0,
                'pay_type' => $payTypeId,
                'activity_type' => 0,
                'coupon_id' => $couponUserId > 0 ? (string)$couponUserId : '',
            ];

            $orderData = [
                'group_order_id' => 0,
                'order_sn' => $orderSn,
                'uid' => $user->uid,
                'spread_uid' => $user->spread_uid ?? 0,
                'top_uid' => $user->top_uid ?? 0,
                'real_name' => $user->real_name ?? ($user->nickname ?? ''),
                'user_phone' => $user->phone ?? '',
                'user_address' => '',
                'cart_id' => '',
                'total_num' => 1,
                'total_price' => $totalAmount,
                'total_postage' => '0.00',
                'pay_price' => $payPrice,
                'pay_postage' => '0.00',
                'integral' => $integral,
                'integral_price' => $integralPrice,
                'give_integral' => 0,
                'coupon_id' => $couponUserId > 0 ? (string)$couponUserId : '',
                'coupon_price' => $discount['saved_amount'],
                'platform_coupon_price' => $couponDeductionAmount,
                'order_type' => 0,
                'paid' => 0,
                'pay_type' => $payTypeId,
                'status' => 0,
                'is_virtual' => 1,
                'mark' => $data['mark'] ?? '',
                'activity_type' => 0,
                'order_extend' => '',
                'mer_id' => $merId,
                'cost' => $merchantCostAmount,
                'refund_switch' => 0,
                'merchant_take_info' => '',
                'pool_id' => $discount['pool_id'],
                'merchant_cost_amount' => $merchantCostAmount,
                'platform_profit' => $platformProfit,
                'discount_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'pool_transaction_id' => null,
                'order_scene' => self::ORDER_SCENE,
            ] + $this->settlementFields($merchant);

            return Db::transaction(function () use ($groupData, $orderData, $user, $couponUserId, $integral, $integralPrice) {
                if ($couponUserId > 0) {
                    app()->make(StoreCouponUserRepository::class)->updates([$couponUserId], [
                        'use_time' => date('Y-m-d H:i:s'),
                        'status' => 1,
                    ]);
                }
                $groupOrder = $this->groupOrderRepository->create($groupData);
                if ($integral > 0) {
                    $lockedUser = User::where('uid', $user->uid)->lock(true)->find();
                    if (!$lockedUser || bccomp((string)$lockedUser->integral, (string)$integral, 0) < 0) {
                        throw new ValidateException('积分不足');
                    }
                    $lockedUser->integral = bcsub((string)$lockedUser->integral, (string)$integral, 0);
                    $lockedUser->save();
                    app()->make(UserBillRepository::class)->decBill((int)$user->uid, 'integral', 'deduction', [
                        'link_id' => $groupOrder->group_order_id,
                        'status' => 1,
                        'title' => '惠买单买单',
                        'number' => $integral,
                        'mark' => '惠买单订单使用积分抵扣' . floatval($integralPrice) . '元',
                        'balance' => $lockedUser->integral,
                    ]);
                }
                $orderData['group_order_id'] = $groupOrder->group_order_id;
                $order = $this->storeOrderRepository->create($orderData);

                app()->make(StoreOrderStatusRepository::class)->batchCreateLog([[
                    'order_id' => $order->order_id,
                    'order_sn' => $order->order_sn,
                    'type' => StoreOrderStatusRepository::TYPE_ORDER,
                    'change_message' => '惠买单订单生成',
                    'change_type' => StoreOrderStatusRepository::ORDER_STATUS_CREATE,
                    'uid' => $user->uid,
                    'nickname' => $user->nickname ?? '',
                    'user_type' => StoreOrderStatusRepository::U_TYPE_USER,
                ]]);

                return [
                    'group_order_id' => $groupOrder->group_order_id,
                    'order_id' => $order->order_id,
                    'order_sn' => $order->order_sn,
                    'pay_price' => $groupOrder->pay_price,
                    'discount' => $this->publicDiscountSnapshot(json_decode($order->discount_snapshot, true) ?: []),
                ];
            });
        });
    }

    public function assertNoUnsupportedMiniProgramOrderFields(array $params): void
    {
        $fields = $this->unsupportedMiniProgramOrderFields($params);
        if ($fields) {
            throw new ValidateException('惠买单订单暂未支持以下抵扣参数：' . implode('、', $fields));
        }
    }

    public function assertNoUnsupportedMiniProgramPrepareFields(array $params): void
    {
        $fields = $this->unsupportedMiniProgramPrepareFields($params);
        if ($fields) {
            throw new ValidateException('惠买单待支付订单不支持以下商品、购物车、支付或抵扣参数：' . implode('、', $fields));
        }
    }

    protected function unsupportedMiniProgramOrderFields(array $params): array
    {
        $unsupported = ['pointsAmount', 'points_amount', 'discountType', 'discount_type'];
        return array_values(array_filter($unsupported, function (string $field) use ($params) {
            return array_key_exists($field, $params);
        }));
    }

    protected function unsupportedMiniProgramPrepareFields(array $params): array
    {
        $unsupported = [
            'product_id', 'productId', 'spu_id', 'spuId', 'sku', 'sku_id', 'skuId',
            'cart_id', 'cartId', 'cartIds', 'cart_ids', 'productAttr', 'product_attr',
            'pay_type', 'payType',
            'couponId', 'coupon_id', 'usePoints', 'use_points', 'pointsAmount', 'points_amount',
            'discountType', 'discount_type',
        ];
        return array_values(array_filter($unsupported, function (string $field) use ($params) {
            return array_key_exists($field, $params);
        }));
    }

    public function pay(User $user, int $groupOrderId, string $type, string $returnUrl = '', bool $isApp = false)
    {
        $typeId = $this->payTypeId($type);
        if (in_array($type, ['weixin', 'alipay'], true) && $isApp) {
            $type .= 'App';
        }

        $groupOrder = StoreGroupOrder::getDB()->where('group_order_id', $groupOrderId)
            ->where('uid', $user->uid)->where('is_del', 0)->with(['orderList'])->find();
        if (!$groupOrder) {
            throw new ValidateException('订单不存在');
        }
        if ((int)$groupOrder->paid === 1) {
            return app('json')->status('success', '订单已支付', ['order_id' => $groupOrder->group_order_id]);
        }
        $this->assertHuimaidanGroup($groupOrder);
        $this->changePayType($groupOrder, $typeId);
        $groupOrder->pay_type = $typeId;

        if ($type === 'balance') {
            return $this->payBalance($user, $groupOrder);
        }

        $body = [
            'order_sn' => $groupOrder->group_order_sn,
            'pay_price' => $groupOrder->pay_price,
            'attach' => self::PAY_ATTACH,
            'body' => '惠买单到店买单',
        ];
        if ($returnUrl && strpos($type, 'alipay') === 0) {
            $body['return_url'] = $returnUrl;
        }

        $config = (new PayService($type, $body, self::PAY_ATTACH))->pay($user);
        return app('json')->status($type, $config + ['order_id' => $groupOrder->group_order_id]);
    }

    public function paySuccessByOrderSn(string $orderSn, array $payData = [])
    {
        $groupOrder = StoreGroupOrder::getDB()->where('group_order_sn', $orderSn)->with(['orderList'])->find();
        if (!$groupOrder) {
            throw new ValidateException('惠买单订单不存在');
        }
        return $this->paySuccess($groupOrder, $payData);
    }

    public function paySuccess(StoreGroupOrder $groupOrder, array $payData = [])
    {
        return app()->make(LockService::class)->exec('huimaidan.order.pay.' . $groupOrder->group_order_id, function () use ($groupOrder, $payData) {
            $paidNow = false;
            $paidGroupOrder = Db::transaction(function () use ($groupOrder, $payData, &$paidNow) {
                $groupOrder = StoreGroupOrder::getDB()->where('group_order_id', $groupOrder->group_order_id)
                    ->lock(true)->with(['orderList'])->find();
                if (!$groupOrder) {
                    throw new ValidateException('惠买单订单不存在');
                }
                if ((int)$groupOrder->paid === 1) {
                    return $groupOrder;
                }
                $this->assertHuimaidanGroup($groupOrder);
                $this->assertCallbackAmount($groupOrder->pay_price, $payData);

                $order = $groupOrder->orderList[0];
                $transactionId = $this->transactionId($payData);
                $poolTransactionId = $order->pool_transaction_id;
                $settlementMode = (int)($order->settlement_mode ?? MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL);
                if ($settlementMode === MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL) {
                    $poolId = $this->poolRulePolicy->poolId($order->pool_id);
                    if (!$poolTransactionId) {
                        $transaction = $this->poolRepository->deduct($poolId, $order->merchant_cost_amount, (int)$order->order_id);
                        $poolTransactionId = $transaction->transaction_id;
                    }
                } elseif ($settlementMode === MerchantRepository::HUIMAIDAN_SETTLEMENT_WITHDRAW) {
                    if ((int)($order->huimaidan_income_status ?? 0) !== 1) {
                        $this->creditWithdrawOrderIncome($order);
                    }
                } else {
                    throw new ValidateException('惠买单订单结算模式有误');
                }

                $now = date('Y-m-d H:i:s');
                $groupOrder->paid = 1;
                $groupOrder->pay_time = $now;
                $groupOrder->save();

                $order->paid = 1;
                $order->pay_time = $now;
                $order->status = 3;
                $order->verify_time = $now;
                $order->transaction_id = $transactionId ?: $order->transaction_id;
                $order->pool_transaction_id = $poolTransactionId;
                if ($settlementMode === MerchantRepository::HUIMAIDAN_SETTLEMENT_WITHDRAW) {
                    $order->huimaidan_income_status = 1;
                }
                $order->save();

                // 惠买单订单支付成功后，累加商户真实销量，便于详情页销量展示与实际交易联动
                $merId = (int)$order->mer_id;
                if ($merId > 0) {
                    Merchant::getDB()->where('mer_id', $merId)->inc('sales', max(1, (int)$order->total_num))->update();
                }

                app()->make(StoreOrderStatusRepository::class)->batchCreateLog([[
                    'order_id' => $order->order_id,
                    'order_sn' => $order->order_sn,
                    'type' => StoreOrderStatusRepository::TYPE_ORDER,
                    'change_message' => '惠买单支付成功',
                    'change_type' => StoreOrderStatusRepository::ORDER_STATUS_PAY_SUCCCESS,
                    'uid' => $order->uid,
                    'nickname' => '用户',
                    'user_type' => StoreOrderStatusRepository::U_TYPE_USER,
                ]]);

                Log::info('HuimaidanOrderPaySuccess:支付成功' . var_export(['group_order_id' => $groupOrder->group_order_id, 'order_id' => $order->order_id], true));
                $paidNow = true;
                return $groupOrder;
            });
            if ($paidNow) {
                $this->syncPaySuccessRewards($paidGroupOrder);
                $this->broadcastVoice($paidGroupOrder);
            }
            return $paidGroupOrder;
        });
    }

    public function getList(int $uid, array $where, $page, $limit)
    {
        $query = StoreOrder::getDB()->where('uid', $uid)->where('is_del', 0)->where('order_scene', self::ORDER_SCENE)
            ->when(isset($where['paid']) && $where['paid'] !== '', function ($query) use ($where) {
                $query->where('paid', (int)$where['paid']);
            })
            ->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
                getModelTime($query, $where['date'], 'create_time');
            })
            ->with($this->userOrderRelations())
            ->order('order_id DESC');
        $count = $query->count();
        $list = $query->page($page, $limit)->select()->each(function ($order) {
            return $this->publicOrder($order);
        });
        return compact('count', 'list');
    }

    public function detail(int $uid, int $orderId)
    {
        $order = StoreOrder::getDB()->where('uid', $uid)->where('order_id', $orderId)
            ->where('order_scene', self::ORDER_SCENE)->with($this->userOrderRelations(['groupOrder']))->find();
        if (!$order) {
            throw new ValidateException('订单不存在');
        }
        return $this->publicOrder($order);
    }

    public function statistics(int $uid): array
    {
        $unpaid = $this->userHuimaidanOrderQuery($uid)->where('paid', 0)->count();
        $completed = $this->userHuimaidanOrderQuery($uid)->where('paid', 1)->where('status', 3)->count();
        $orderIds = $this->userHuimaidanOrderQuery($uid)->column('order_id');
        $refund = $orderIds ? StoreRefundOrder::getDB()
            ->where('uid', $uid)
            ->whereIn('order_id', $orderIds)
            ->where('is_del', 0)
            ->whereIn('status', [0, 1, 2, 3])
            ->count() : 0;

        return $this->orderStatisticsPayload((int)$unpaid, (int)$completed, (int)$refund);
    }

    public function payResult(int $uid, int $id): array
    {
        $order = $this->userHuimaidanOrderQuery($uid)
            ->where(function ($query) use ($id) {
                $query->where('order_id', $id)->whereOr('group_order_id', $id);
            })
            ->find();
        if (!$order) {
            throw new ValidateException('订单不存在');
        }

        $order = $this->syncPayResultByQuery($order);

        return $this->payResultPayload($order);
    }

    protected function payBalance(User $user, StoreGroupOrder $groupOrder)
    {
        if (!systemConfig('yue_pay_status')) {
            throw new ValidateException('未开启余额支付');
        }

        Db::transaction(function () use ($user, $groupOrder) {
            $user = User::where('uid', $user->uid)->lock(true)->find();
            if (!$user || bccomp($user->now_money, $groupOrder->pay_price, 2) < 0) {
                throw new ValidateException('余额不足，请更换支付方式');
            }
            $user->now_money = bcsub($user->now_money, $groupOrder->pay_price, 2);
            $user->save();
            app()->make(UserBillRepository::class)->decBill($user->uid, 'now_money', 'pay_product', [
                'link_id' => $groupOrder->group_order_id,
                'status' => 1,
                'title' => '惠买单买单',
                'number' => $groupOrder->pay_price,
                'mark' => '余额支付' . floatval($groupOrder->pay_price) . '元惠买单订单',
                'balance' => $user->now_money,
            ]);
            $this->paySuccess($groupOrder, ['transaction_id' => 'balance_' . $groupOrder->group_order_sn]);
        });

        return app('json')->status('success', '余额支付成功', ['order_id' => $groupOrder->group_order_id]);
    }

    protected function userOrderRelations(array $relations = []): array
    {
        $relations['merchant'] = function ($query) {
            $query->field('mer_id,mer_name,mer_avatar,mer_address');
        };
        if (in_array('groupOrder', $relations, true)) {
            unset($relations[array_search('groupOrder', $relations, true)]);
            $relations['groupOrder'] = function ($query) {
                $query->field('group_order_id,group_order_sn,paid,pay_type,pay_price,create_time,pay_time');
            };
        }
        return $relations;
    }

    protected function userHuimaidanOrderQuery(int $uid)
    {
        return StoreOrder::getDB()
            ->where('uid', $uid)
            ->where('is_del', 0)
            ->where('order_scene', self::ORDER_SCENE);
    }

    protected function orderStatisticsPayload(int $unpaid, int $completed, int $refund): array
    {
        return compact('unpaid', 'completed', 'refund');
    }

    protected function payResultPayload($order): array
    {
        $paid = (int)$this->rowValue($order, 'paid', 0) === 1;
        return [
            'paid' => $paid,
            'orderId' => (int)$this->rowValue($order, 'group_order_id', 0),
            'storeOrderId' => (int)$this->rowValue($order, 'order_id', 0),
            'payTime' => $paid ? (string)$this->rowValue($order, 'pay_time', '') : '',
        ];
    }

    protected function syncPayResultByQuery($order)
    {
        if ((int)$this->rowValue($order, 'paid', 0) === 1) {
            return $order;
        }

        $payType = $this->payTypeName((int)$this->rowValue($order, 'pay_type', -1));
        if (!$this->supportsPayStatusQuery($payType)) {
            return $order;
        }

        $groupOrderId = (int)$this->rowValue($order, 'group_order_id', 0);
        if (!$groupOrderId) {
            return $order;
        }

        $groupOrder = StoreGroupOrder::getDB()->where('group_order_id', $groupOrderId)->with(['orderList'])->find();
        if (!$groupOrder) {
            return $order;
        }

        if ((int)$groupOrder->paid === 1) {
            return $this->reloadUserHuimaidanOrder((int)$this->rowValue($order, 'uid', 0), (int)$this->rowValue($order, 'order_id', 0)) ?: $order;
        }

        try {
            $payData = (new PayStatusService($payType, ['order_sn' => $groupOrder->group_order_sn]))->query();
            if ($payData) {
                $this->paySuccess($groupOrder, $payData);
                return $this->reloadUserHuimaidanOrder((int)$this->rowValue($order, 'uid', 0), (int)$this->rowValue($order, 'order_id', 0)) ?: $order;
            }
        } catch (\Throwable $e) {
            Log::warning('HuimaidanPayResultQueryFailed:' . var_export([
                'group_order_id' => $groupOrderId,
                'group_order_sn' => $groupOrder->group_order_sn,
                'pay_type' => $payType,
                'message' => $e->getMessage(),
            ], true));
        }

        return $order;
    }

    protected function reloadUserHuimaidanOrder(int $uid, int $orderId)
    {
        if (!$uid || !$orderId) {
            return null;
        }

        return $this->userHuimaidanOrderQuery($uid)->where('order_id', $orderId)->find();
    }

    protected function preparePayload(array $order): array
    {
        return [
            'group_order_id' => (int)$order['group_order_id'],
            'order_id' => (int)$order['order_id'],
            'order_sn' => (string)$order['order_sn'],
            'pay_price' => $this->money($order['pay_price']),
            'discount' => $order['discount'] ?? [],
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

    protected function publicOrder($order)
    {
        $order['discount'] = $this->publicDiscountSnapshot(json_decode((string)$order->discount_snapshot, true) ?: []);
        return $order->hidden([
            'pool_id', 'merchant_cost_amount', 'platform_profit', 'pool_transaction_id',
            'discount_snapshot', 'cost',
        ]);
    }

    protected function publicDiscountSnapshot(array $snapshot): array
    {
        return array_intersect_key($snapshot, array_flip([
            'rule_id', 'rule_type', 'rule_type_label', 'title', 'platform_discount',
            'coupon_amount', 'point_ratio', 'member_level', 'member_level_name',
            'member_discount', 'original_amount', 'pay_amount', 'saved_amount',
            'member_discount_enabled',
            'coupon_user_id', 'coupon_id', 'coupon_deduction_amount',
            'integral', 'integral_deduction_amount',
            'platform_bear_coupon_amount', 'platform_bear_integral_amount',
        ]));
    }

    protected function discountInputs(array $data): array
    {
        $value = true;
        if (isset($data['useMemberDiscount']) && $data['useMemberDiscount'] !== '') {
            $value = $data['useMemberDiscount'];
        } elseif (isset($data['use_member_discount']) && $data['use_member_discount'] !== '') {
            $value = $data['use_member_discount'];
        }
        return [
            'use_member_discount' => $this->truthy($value),
        ];
    }

    protected function buildDeductionAdjustment(User $user, int $merId, string $amount, array $discount, array $data): array
    {
        $inputs = $this->deductionInputs($data);
        $empty = [
            'discount' => $discount,
            'coupon_user_id' => 0,
            'coupon_id' => 0,
            'coupon_deduction_amount' => '0.00',
            'integral' => 0,
            'integral_price' => '0.00',
        ];
        if ($inputs['coupon_user_id'] <= 0 && !$inputs['use_points']) {
            return $empty;
        }

        $config = $this->normalizeDeductionConfig(systemConfig([
            self::CONFIG_DISCOUNT_STACK,
            'integral_status',
            'integral_money',
        ]));
        $coupon = [];
        if ($inputs['coupon_user_id'] > 0) {
            $coupon = app()->make(StoreCouponUserRepository::class)->usableHuimaidanCouponById(
                (int)$user->uid,
                $merId,
                $amount,
                $inputs['coupon_user_id']
            );
            if (!$coupon) {
                throw new ValidateException('优惠券不可用');
            }
        }

        $couponAmount = isset($coupon['coupon_price']) ? $this->clampDeduction($coupon['coupon_price'], $discount['pay_amount']) : '0.00';
        $points = ['integral' => 0, 'integral_price' => '0.00'];
        if ($inputs['use_points']) {
            if (!$config['integral_enabled']) {
                throw new ValidateException('积分抵扣未开启');
            }
            $points = $this->pointDeduction((int)$user->integral, $config['integral_money'], bcsub($discount['pay_amount'], $couponAmount, 2));
        }

        $discount = $this->applyDeductionAmounts($discount, $coupon ?: [], $points, $config);

        return [
            'discount' => $discount,
            'coupon_user_id' => (int)($discount['snapshot']['coupon_user_id'] ?? 0),
            'coupon_id' => (int)($discount['snapshot']['coupon_id'] ?? 0),
            'coupon_deduction_amount' => $this->money($discount['snapshot']['coupon_deduction_amount'] ?? 0),
            'integral' => (int)($discount['snapshot']['integral'] ?? 0),
            'integral_price' => $this->money($discount['snapshot']['integral_deduction_amount'] ?? 0),
        ];
    }

    protected function deductionInputs(array $data): array
    {
        return [
            'coupon_user_id' => (int)($data['couponId'] ?? ($data['coupon_id'] ?? 0)),
            'use_points' => $this->truthy($data['usePoints'] ?? ($data['use_points'] ?? false)),
        ];
    }

    protected function normalizeDeductionConfig(array $config): array
    {
        $stackEnabled = $config[self::CONFIG_DISCOUNT_STACK] ?? '1';
        if ($stackEnabled === '' || $stackEnabled === null) {
            $stackEnabled = '1';
        }

        return [
            'stack_enabled' => $this->truthy($stackEnabled),
            'integral_enabled' => $this->truthy($config['integral_status'] ?? false),
            'integral_money' => $this->money($config['integral_money'] ?? 0),
        ];
    }

    protected function pointDeduction(int $userIntegral, string $integralMoney, string $payAmount): array
    {
        if ($userIntegral <= 0 || bccomp($payAmount, '0.01', 2) <= 0) {
            return ['integral' => 0, 'integral_price' => '0.00'];
        }
        if (bccomp($integralMoney, '0.00', 2) <= 0) {
            throw new ValidateException('请先配置积分抵扣比例');
        }

        $maxAmount = bcsub($payAmount, '0.01', 2);
        $maxIntegral = (int)floor((float)bcdiv($maxAmount, $integralMoney, 4));
        $integral = min($userIntegral, $maxIntegral);
        if ($integral <= 0) {
            return ['integral' => 0, 'integral_price' => '0.00'];
        }

        return [
            'integral' => $integral,
            'integral_price' => $this->money(bcmul((string)$integral, $integralMoney, 2)),
        ];
    }

    protected function applyDeductionAmounts(array $discount, array $coupon, array $points, array $config): array
    {
        $baseSaved = $this->money($discount['saved_amount'] ?? 0);
        $couponAmount = isset($coupon['coupon_price']) ? $this->clampDeduction($coupon['coupon_price'], $discount['pay_amount']) : '0.00';
        $payAfterCoupon = bcsub($discount['pay_amount'], $couponAmount, 2);
        $pointAmount = $this->clampDeduction($points['integral_price'] ?? 0, $payAfterCoupon);
        $hasExtraDeduction = bccomp(bcadd($couponAmount, $pointAmount, 2), '0.00', 2) > 0;
        if (!$config['stack_enabled'] && $hasExtraDeduction && bccomp($baseSaved, '0.00', 2) > 0) {
            throw new ValidateException('当前配置不支持优惠叠加');
        }
        if (!$config['stack_enabled'] && bccomp($couponAmount, '0.00', 2) > 0 && bccomp($pointAmount, '0.00', 2) > 0) {
            throw new ValidateException('当前配置不支持优惠叠加');
        }

        $extraSaved = bcadd($couponAmount, $pointAmount, 2);
        $discount['pay_amount'] = $this->money(bcsub($discount['pay_amount'], $extraSaved, 2));
        $discount['saved_amount'] = $this->money(bcadd($discount['saved_amount'], $extraSaved, 2));
        $discount['platform_profit'] = $this->money(bcsub($discount['platform_profit'] ?? 0, $extraSaved, 2));

        $snapshot = $discount['snapshot'] ?? [];
        $snapshot['pay_amount'] = $discount['pay_amount'];
        $snapshot['saved_amount'] = $discount['saved_amount'];
        $snapshot['platform_profit'] = $discount['platform_profit'];
        $snapshot['coupon_user_id'] = (int)($coupon['coupon_user_id'] ?? 0);
        $snapshot['coupon_id'] = (int)($coupon['coupon_id'] ?? 0);
        $snapshot['coupon_deduction_amount'] = $couponAmount;
        $snapshot['integral'] = $pointAmount === '0.00' ? 0 : (int)($points['integral'] ?? 0);
        $snapshot['integral_deduction_amount'] = $pointAmount;
        $snapshot['platform_bear_coupon_amount'] = $couponAmount;
        $snapshot['platform_bear_integral_amount'] = $pointAmount;
        $discount['snapshot'] = $snapshot;

        return $discount;
    }

    protected function clampDeduction($amount, $payAmount): string
    {
        $amount = $this->money($amount);
        $max = bccomp($payAmount, '0.01', 2) > 0 ? bcsub($payAmount, '0.01', 2) : '0.00';
        if (bccomp($amount, $max, 2) > 0) {
            return $this->money($max);
        }
        return $amount;
    }

    protected function truthy($value): bool
    {
        return in_array($value, [1, '1', true, 'true', 'on'], true);
    }

    protected function syncPaySuccessRewards(StoreGroupOrder $groupOrder): void
    {
        if (!$groupOrder->uid) {
            return;
        }
        $user = app()->make(UserRepository::class)->get((int)$groupOrder->uid);
        $payloads = $this->paySuccessRewardPayloads(
            (int)$groupOrder->uid,
            (int)($user->spread_uid ?? 0),
            $groupOrder->pay_price,
            (int)$groupOrder->group_order_id
        );
        foreach ($payloads['jobs'] as $payload) {
            Queue::push(UserBrokerageLevelJob::class, $payload);
        }
        app()->make(UserBrokerageRepository::class)->incMemberValue(
            $payloads['member_value']['uid'],
            $payloads['member_value']['type'],
            $payloads['member_value']['link_id'],
            $payloads['member_value']['money']
        );
    }

    protected function paySuccessRewardPayloads(int $uid, int $spreadUid, $payPrice, int $groupOrderId): array
    {
        $payPrice = $this->money($payPrice);
        $jobs = [];
        if ($spreadUid > 0) {
            $jobs[] = ['uid' => $spreadUid, 'type' => 'spread_pay_num', 'inc' => 1];
            $jobs[] = ['uid' => $spreadUid, 'type' => 'spread_money', 'inc' => $payPrice];
        }
        $jobs[] = ['uid' => $uid, 'type' => 'pay_money', 'inc' => $payPrice];
        $jobs[] = ['uid' => $uid, 'type' => 'pay_num', 'inc' => 1];

        return [
            'jobs' => $jobs,
            'member_value' => [
                'uid' => $uid,
                'type' => 'member_pay_num',
                'link_id' => $groupOrderId,
                'money' => $payPrice,
            ],
        ];
    }

    protected function changePayType(StoreGroupOrder $groupOrder, int $payType)
    {
        Db::transaction(function () use ($groupOrder, $payType) {
            $groupOrder->pay_type = $payType;
            $groupOrder->save();
            foreach ($groupOrder->orderList as $order) {
                $order->pay_type = $payType;
                $order->save();
            }
        });
    }

    protected function assertHuimaidanGroup(StoreGroupOrder $groupOrder)
    {
        if (!$groupOrder->orderList || !count($groupOrder->orderList)) {
            throw new ValidateException('惠买单订单明细不存在');
        }
        if (count($groupOrder->orderList) !== 1 || (int)$groupOrder->orderList[0]->order_scene !== self::ORDER_SCENE) {
            throw new ValidateException('订单类型不支持惠买单支付');
        }
    }

    protected function assertCallbackAmount($payPrice, array $payData)
    {
        $amount = $this->callbackAmount($payData);
        if ($amount !== null && bccomp($this->money($payPrice), $amount, 2) !== 0) {
            throw new ValidateException('支付回调金额与订单金额不一致');
        }
    }

    protected function callbackAmount(array $payData)
    {
        if (isset($payData['amount']['total'])) {
            return $this->money(bcdiv((string)$payData['amount']['total'], '100', 2));
        }
        if (isset($payData['total_fee'])) {
            return $this->money(bcdiv((string)$payData['total_fee'], '100', 2));
        }
        if (isset($payData['total_amount'])) {
            return $this->money($payData['total_amount']);
        }
        if (isset($payData['buyer_pay_amount'])) {
            return $this->money($payData['buyer_pay_amount']);
        }
        return null;
    }

    protected function transactionId(array $payData): string
    {
        return (string)($payData['transaction_id'] ?? ($payData['trade_no'] ?? ''));
    }

    protected function payTypeName(int $type): string
    {
        return CrmebStoreOrderRepository::PAY_TYPE[$type] ?? '';
    }

    protected function supportsPayStatusQuery(string $type): bool
    {
        return in_array($type, ['routine', 'weixin', 'h5', 'weixinQr'], true);
    }

    protected function payTypeId(string $type): int
    {
        if (!in_array($type, CrmebStoreOrderRepository::PAY_TYPE, true)) {
            throw new ValidateException('请选择正确的支付方式');
        }
        return (int)array_search($type, CrmebStoreOrderRepository::PAY_TYPE, true);
    }

    protected function settlementFields($merchant): array
    {
        if (is_array($merchant) || $merchant instanceof \ArrayAccess) {
            $mode = (int)($merchant['huimaidan_settlement_mode'] ?? MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL);
        } else {
            $mode = (int)($merchant->huimaidan_settlement_mode ?? MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL);
        }
        if (!in_array($mode, [MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL, MerchantRepository::HUIMAIDAN_SETTLEMENT_WITHDRAW], true)) {
            throw new ValidateException('惠买单合作模式有误');
        }
        return [
            'settlement_mode' => $mode,
            'huimaidan_income_status' => 0,
        ];
    }

    protected function creditWithdrawOrderIncome($order): void
    {
        $merchant = app()->make(MerchantRepository::class)->get((int)$order->mer_id);
        if (!$merchant) {
            throw new ValidateException('商户不存在');
        }
        $merchant->mer_money = bcadd((string)$merchant->mer_money, $this->money($order->merchant_cost_amount), 2);
        $merchant->save();
        app()->make(FinancialRecordRepository::class)->inc($this->huimaidanIncomeRecord($order), (int)$order->mer_id);
    }

    protected function huimaidanIncomeRecord($order): array
    {
        return [
            'order_id' => (int)$order->order_id,
            'order_sn' => (string)$order->order_sn,
            'user_info' => (string)($order->real_name ?: '惠买单用户'),
            'user_id' => (int)$order->uid,
            'financial_type' => 'huimaidan_order_income',
            'number' => $this->money($order->merchant_cost_amount),
            'type' => 0,
            'mer_id' => (int)$order->mer_id,
            'pay_type' => (int)$order->pay_type,
        ];
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }

    /**
     * 触发收款语音播报
     */
    protected function broadcastVoice(StoreGroupOrder $groupOrder): void
    {
        try {
            $order = $groupOrder->orderList[0] ?? null;
            if (!$order) {
                return;
            }
            $voicePushRepo = app()->make(VoicePushRepository::class);
            $voicePushRepo->createPayBroadcast(
                (int)$order->mer_id,
                (int)$order->order_id,
                (string)$order->order_sn,
                (float)($order->pay_price ?? $order->total_price)
            );
        } catch (\Throwable $e) {
            Log::error('语音播报任务创建失败: ' . $e->getMessage());
        }
    }
}
