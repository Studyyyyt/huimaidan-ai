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

namespace app\common\repositories\circle;

use think\facade\Db;
use app\common\repositories\BaseRepository;
use app\common\dao\circle\CircleFinancialRecordDao;

class CircleFinancialRecordRepository extends BaseRepository
{
    public function __construct(CircleFinancialRecordDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(int $page = 1, int $limit = 10, array $where, array $with = [])
    {
        $query = $this->dao->search($where)
            ->when(!empty($with), function ($query) use ($with) {
                $query->with($with);
            });

        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        return compact('list', 'count');
    }

    public function insertAll($orderInfo)
    {
        if ($orderInfo->pay_price == 0) {
            return false;
        }
        // 商户信息
        $merchant = $orderInfo->merchant;
        if(!$merchant) {
            return false;
        }
        // 计算平台抽成
        $commissionRate = (float)($merchant->commission_switch ? bcdiv($merchant->commission_rate, 100, 2) : $merchant->merchantCategory->commission_rate);
        if (!$commissionRate) {
            return false;
        }
        $commissionPrice = bcmul($orderInfo->pay_price, $commissionRate, 2);
        // 商圈信息
        $circle = $merchant->merchantRegion;
        if (!$circle) {
            return false;
        }

        $data = [];
        $circleName = '';
        if ($circle->level == 1) { // 两层
            // 一级商圈
            $firstCircle = $circle->parent;
            $firstCircleAgent = $firstCircle->circleAgent;
            if ($firstCircleAgent && $firstCircle && $firstCircle->status) {
                $amount = bcmul($commissionPrice, bcdiv(bcsub($firstCircle->commission_rate, $circle->commission_rate), 100, 2), 2);
                if($firstCircle->type == 0) {
                    $data[] = [
                        'circle_id' => $firstCircle->circle_id,
                        'circle_name' => $firstCircle->name,
                        'mer_id' => $merchant->mer_id,
                        'mer_name' => $merchant->mer_name,
                        'agent_id' => $firstCircleAgent->circle_agent_id,
                        'agent_name' => $firstCircleAgent->name,
                        'record_sn' => $this->getSn(),
                        'order_id' => $orderInfo->order_id,
                        'order_sn' => $orderInfo->order_sn,
                        'user_id' => $orderInfo->uid,
                        'user_info' => $orderInfo->user->nickname ?? '游客',
                        'amount' => $amount
                    ];
                }
            }
            $circleName = $firstCircle->name . '/';
        }
        if ($circle->level == 2) { // 三层
            // 一级商圈
            $firstCircle = $circle->parent->parent;
            $firstCircleAgent = $firstCircle->circleAgent;
            if ($firstCircleAgent && $firstCircle && $firstCircle->status) {
                $amount = bcmul($commissionPrice, bcdiv(bcsub($firstCircle->commission_rate, $circle->parent->commission_rate), 100, 2), 2);
                if($firstCircle->type == 0) {
                    $data[] = [
                        'circle_id' => $firstCircle->circle_id,
                        'circle_name' => $firstCircle->name,
                        'mer_id' => $merchant->mer_id,
                        'mer_name' => $merchant->mer_name,
                        'agent_id' => $firstCircleAgent->circle_agent_id,
                        'agent_name' => $firstCircleAgent->name,
                        'record_sn' => $this->getSn(),
                        'order_id' => $orderInfo->order_id,
                        'order_sn' => $orderInfo->order_sn,
                        'user_id' => $orderInfo->uid,
                        'user_info' => $orderInfo->user->nickname ?? '游客',
                        'amount' => $amount
                    ];
                }
            }
            // 二级商圈
            $secondCircle = $circle->parent;
            $secondCircleAgent = $secondCircle->circleAgent;
            if ($secondCircleAgent && $secondCircle && $secondCircle->status) {
                $amount = bcmul($commissionPrice, bcdiv(bcsub($secondCircle->commission_rate, $circle->commission_rate), 100, 2), 2);
                if($secondCircle->type == 0) {
                    $data[] = [
                        'circle_id' => $secondCircle->circle_id,
                        'circle_name' => $firstCircle->name . '/' . $secondCircle->name,
                        'mer_id' => $merchant->mer_id,
                        'mer_name' => $merchant->mer_name,
                        'agent_id' => $secondCircleAgent->circle_agent_id,
                        'agent_name' => $secondCircleAgent->name,
                        'record_sn' => $this->getSn(),
                        'order_id' => $orderInfo->order_id,
                        'order_sn' => $orderInfo->order_sn,
                        'user_id' => $orderInfo->uid,
                        'user_info' => $orderInfo->user->nickname ?? '游客',
                        'amount' => $amount
                    ];
                }
            }
            $circleName = $firstCircle->name . '/' . $secondCircle->name . '/';
        }
        // 当前层级商圈
        $circleAgent = $circle->circleAgent;
        if ($circleAgent && $circle && $circle->status) {
            $amount = bcmul($commissionPrice, bcdiv($circle->commission_rate, 100, 2), 2);
            if($circle->type == 0) {
                $data[] = [
                    'circle_id' => $circle->circle_id,
                    'circle_name' => $circleName . $circle->name,
                    'mer_id' => $merchant->mer_id,
                    'mer_name' => $merchant->mer_name,
                    'agent_id' => $circleAgent->circle_agent_id,
                    'agent_name' => $circleAgent->name, 
                    'record_sn' => $this->getSn(),
                    'order_id' => $orderInfo->order_id,
                    'order_sn' => $orderInfo->order_sn,
                    'user_id' => $orderInfo->uid,
                    'user_info' => $orderInfo->user->nickname ?? '游客',
                    'amount' => $amount
                ];
            }
        }

        return $this->dao->insertAll($data);
    }

    public function defrost($order)
    {
        $recodes = $this->dao->getSearch(['order_id' => $order->order_id, 'status' => 0])->select();
        if ($recodes->isEmpty()) {
            return false;
        }

        Db::transaction(function () use ($recodes) {
            foreach ($recodes as $recod) {
                // 解冻流水
                $recod->status = 1;
                $recod->save();
                // 增加商圈代理商余额
                $circleAgent = $recod->circleAgent;
                if ($circleAgent) {
                    $circleAgent->balance += $recod->amount;
                    $circleAgent->update_time = date('Y-m-d H:i:s');
                    $circleAgent->save();
                }
            }
        });
    }

    public function invalid($order)
    {
        $recodes = $this->dao->getSearch(['order_id' => $order->order_id, 'status' => 0])->select();
        if ($recodes->isEmpty()) {
            return false;
        }

        Db::transaction(function () use ($recodes, $order) {
            foreach ($recodes as $recod) {
                // 作废流水
                $recod->status = -1;
                $recod->save();
            }
            // 部分退款重新计算商圈提成
            if ($order->status == 0) {
                $newOrder = $order;
                $orderProduct = $newOrder->orderProduct;
                $refundPrice = 0;
                foreach ($orderProduct as $product) {
                    if (in_array($product->is_refund, [2, 3])) {
                        $unitPrice = bcdiv($product->total_price, $product->product_num, 2); // 单价
                        // 已退款金额 = 单价 * （总数量 - 可退数量）
                        $refundPrice += bcmul($unitPrice, bcsub($product->product_num, $product->refund_num), 2);
                    }
                }
                // 更新订单总价，减去已退款金额
                $newOrder->total_price = bcsub($newOrder->total_price, $refundPrice, 2);
                // 重新计算商圈提成
                $this->insertAll($newOrder);
                unset($newOrder);
            }
        });
    }

    protected function getSn()
    {
        // 获取当前时间的微秒和秒部分
        list($msec, $sec) = explode(' ', microtime());

        // 将微秒和秒转换为毫秒，并格式化为整数
        $msectime = number_format((floatval($msec) + floatval($sec)) * 1000, 0, '', '');

        // 生成订单编号：前缀 + 毫秒时间戳 + 随机数
        // 保证随机数在特定范围内，避免生成重复的订单编号
        $orderId = 'cfr' . $msectime . mt_rand(10000, max(intval($msec * 10000) + 10000, 98369));

        return $orderId;
    }
}
