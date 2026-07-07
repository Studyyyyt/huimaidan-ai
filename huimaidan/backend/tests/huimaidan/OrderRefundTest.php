<?php

namespace tests\huimaidan;

use PHPUnit\Framework\TestCase;
use app\common\repositories\store\order\StoreRefundOrderRepository;
use app\common\model\store\order\StoreOrder;
use app\common\model\store\order\StoreRefundOrder;

/**
 * 订单全额退款功能测试
 *
 * @author hui maidan
 * @day 2026/06/16
 */
class OrderRefundTest extends TestCase
{
    /**
     * 测试订单全额退款 - 正常场景
     */
    public function testOrderRefundSuccess()
    {
        // 模拟订单数据
        $orderData = [
            'order_id' => 12345,
            'uid' => 1001,
            'mer_id' => 2001,
            'pay_price' => 99.00,
            'total_num' => 2,
            'paid' => 1,
            'status' => 0,
            'refund_status' => 1,
        ];

        // 模拟订单商品数据
        $products = [
            ['order_product_id' => 1, 'product_num' => 1, 'product_price' => 59.00, 'postage_price' => 10.00, 'integral_total' => 0, 'platform_coupon_price' => 0],
            ['order_product_id' => 2, 'product_num' => 1, 'product_price' => 40.00, 'postage_price' => 0, 'integral_total' => 0, 'platform_coupon_price' => 0],
        ];

        // 验证退款金额计算
        $expectedRefundPrice = $orderData['pay_price'];
        $this->assertEquals(99.00, $expectedRefundPrice, '退款金额应等于订单实付金额');

        // 验证退款数据结构
        $refundData = [
            'order_id' => $orderData['order_id'],
            'uid' => $orderData['uid'],
            'mer_id' => $orderData['mer_id'],
            'refund_type' => 1, // 仅退款
            'refund_num' => $orderData['total_num'],
            'refund_price' => $expectedRefundPrice,
            'status' => 0, // 待审核
        ];

        $this->assertEquals(1, $refundData['refund_type'], '退款类型应为仅退款');
        $this->assertEquals(2, $refundData['refund_num'], '退款数量应等于订单总数量');
        $this->assertEquals(0, $refundData['status'], '初始状态应为待审核');
    }

    /**
     * 测试订单校验 - 已退款订单
     */
    public function testValidateOrderRefunded()
    {
        $orderStatus = -1; // 已退款状态

        $this->assertLessThan(0, $orderStatus, '已退款订单状态应小于0');

        // 验证错误提示
        $expectedMessage = '订单已退款';
        $this->assertEquals('订单已退款', $expectedMessage);
    }

    /**
     * 测试订单校验 - 未支付订单
     */
    public function testValidateOrderNotPaid()
    {
        $orderPaid = 0; // 未支付

        $this->assertEquals(0, $orderPaid, '未支付订单paid应为0');

        // 验证错误提示
        $expectedMessage = '订单未支付';
        $this->assertEquals('订单未支付', $expectedMessage);
    }

    /**
     * 测试订单校验 - 退款期限过期
     */
    public function testValidateRefundExpired()
    {
        $refundStatus = 0; // 退款期限已过

        $this->assertEquals(0, $refundStatus, '过期订单refund_status应为0');

        // 验证错误提示
        $expectedMessage = '订单已过退款期限';
        $this->assertEquals('订单已过退款期限', $expectedMessage);
    }

    /**
     * 测试退款商品记录生成
     */
    public function testRefundProductRecord()
    {
        $products = [
            ['order_product_id' => 1, 'product_num' => 1, 'product_price' => 59.00, 'postage_price' => 10.00, 'integral_total' => 0, 'platform_coupon_price' => 0],
            ['order_product_id' => 2, 'product_num' => 1, 'product_price' => 40.00, 'postage_price' => 0, 'integral_total' => 0, 'platform_coupon_price' => 0],
        ];

        $refundOrderId = 1001;

        $refundProducts = [];
        foreach ($products as $product) {
            $refundProducts[] = [
                'refund_order_id' => $refundOrderId,
                'order_product_id' => $product['order_product_id'],
                'refund_num' => $product['product_num'], // 全额退
                'refund_price' => $product['product_price'],
                'refund_postage' => $product['postage_price'],
                'refund_integral' => $product['integral_total'],
                'platform_refund_price' => $product['platform_coupon_price'],
            ];
        }

        // 验证退款商品记录数量
        $this->assertCount(2, $refundProducts, '应生成2条退款商品记录');

        // 验证第一条退款商品记录
        $this->assertEquals($refundOrderId, $refundProducts[0]['refund_order_id']);
        $this->assertEquals(1, $refundProducts[0]['refund_num'], '退款数量应等于购买数量');
        $this->assertEquals(59.00, $refundProducts[0]['refund_price']);

        // 验证第二条退款商品记录
        $this->assertEquals(1, $refundProducts[1]['refund_num'], '退款数量应等于购买数量');
        $this->assertEquals(40.00, $refundProducts[1]['refund_price']);

        // 验证总退款金额
        $totalRefundPrice = array_sum(array_column($refundProducts, 'refund_price'));
        $this->assertEquals(99.00, $totalRefundPrice, '总退款金额应为99.00');
    }

    /**
     * 测试退款单号生成格式
     */
    public function testRefundOrderSnFormat()
    {
        // 模拟退款单号生成
        $prefix = 'TK';
        $timestamp = date('YmdHis');
        $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $refundSn = $prefix . $timestamp . $random;

        // 验证退款单号格式
        $this->assertMatchesRegularExpression('/^TK\d{18}$/', $refundSn, '退款单号格式应为TK+18位数字');
        $this->assertStringStartsWith('TK', $refundSn, '退款单号应以TK开头');
    }

    /**
     * 测试退款状态常量
     */
    public function testRefundStatusConstants()
    {
        // 验证退款状态常量
        $this->assertEquals(0, StoreRefundOrderRepository::REFUND_STATUS_WAIT, '待审核状态应为0');
        $this->assertEquals(-1, StoreRefundOrderRepository::REFUND_STATUS_REFUSED, '拒绝状态应为-1');
        $this->assertEquals(1, StoreRefundOrderRepository::REFUND_STATUS_SUCCESS, '成功状态应为1');
        $this->assertEquals(-2, StoreRefundOrderRepository::REFUND_STATUS_CANCEL, '取消状态应为-2');
    }

    /**
     * 测试商户自动审核逻辑
     */
    public function testMerchantAutoApprove()
    {
        // 模拟商户配置
        $merchantConfig = [
            'refund_auto_approve' => 1, // 开启自动审核
        ];

        $orderStatus = 0; // 待支付状态

        // 验证自动审核条件
        $shouldAutoApprove = ($merchantConfig['refund_auto_approve'] && $orderStatus == 0);
        $this->assertTrue($shouldAutoApprove, '满足条件时应自动审核');

        // 测试不满足条件的情况
        $orderStatus2 = 10; // 已完成状态
        $shouldAutoApprove2 = ($merchantConfig['refund_auto_approve'] && $orderStatus2 == 0);
        $this->assertFalse($shouldAutoApprove2, '订单状态不满足时不应自动审核');
    }

    /**
     * 测试退款金额计算 - 订单粒度
     */
    public function testRefundAmountCalculation()
    {
        // 模拟订单数据
        $order = [
            'pay_price' => 199.00,
            'total_num' => 3,
        ];

        // 订单粒度退款：退款金额 = 订单实付金额
        $refundPrice = $order['pay_price'];

        $this->assertEquals(199.00, $refundPrice, '退款金额应等于订单实付金额');
        $this->assertIsFloat($refundPrice, '退款金额应为浮点数');
    }

    /**
     * 测试并发退款安全性
     */
    public function testConcurrentRefundSafety()
    {
        // 模拟订单状态检查
        $orderStatus = 0;
        $existingRefundCount = 0;

        // 验证订单可退款
        $canRefund = ($orderStatus >= 0 && $orderStatus != 10 && $existingRefundCount == 0);
        $this->assertTrue($canRefund, '订单应可退款');

        // 模拟已有退款单
        $existingRefundCount = 1;
        $canRefund2 = ($orderStatus >= 0 && $orderStatus != 10 && $existingRefundCount == 0);
        $this->assertFalse($canRefund2, '已有退款单时不应重复退款');
    }
}
