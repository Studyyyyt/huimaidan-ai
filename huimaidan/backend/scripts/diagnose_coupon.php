<?php
/**
 * 优惠券诊断脚本
 * 用于排查惠买单优惠券不可用的问题
 */

// 加载框架
require __DIR__ . '/../vendor/autoload.php';
$app = new \think\App();
$app->initialize();

use app\common\dao\store\coupon\StoreCouponUserDao;
use app\common\repositories\store\coupon\StoreCouponUserRepository;

// ============ 配置参数 ============
$uid = 1;           // 用户ID
$merId = 1;         // 商户ID
$amount = '100.00'; // 订单金额
// ================================

echo "=== 惠买单优惠券诊断工具 ===\n\n";

// 1. 查询用户所有有效优惠券
echo "1. 查询用户有效优惠券（未过滤类型）...\n";
$dao = app()->make(StoreCouponUserDao::class);
$allCoupons = $dao->validUserCoupon($uid, $merId)->toArray();
echo "   找到 " . count($allCoupons) . " 张有效优惠券\n\n";

if (empty($allCoupons)) {
    echo "⚠️  用户没有有效优惠券，请检查：\n";
    echo "   - 优惠券是否已过期\n";
    echo "   - 优惠券状态是否为未使用（status=0）\n";
    echo "   - 优惠券是否已失效（is_fail=0）\n";
    exit;
}

// 2. 显示每张优惠券的详情和过滤原因
echo "2. 分析每张优惠券的可用性...\n\n";

foreach ($allCoupons as $index => $coupon) {
    $couponData = (array)($coupon['coupon'] ?? []);
    $couponType = (int)($couponData['type'] ?? -1);
    $couponMerId = (int)($coupon['mer_id'] ?? 0);
    $useMinPrice = $coupon['use_min_price'] ?? '0.00';
    $status = (int)($couponData['status'] ?? 1);
    $isDel = (int)($couponData['is_del'] ?? 0);

    echo "优惠券 #" . ($index + 1) . ":\n";
    echo "   ID: " . ($coupon['coupon_user_id'] ?? 'N/A') . "\n";
    echo "   名称: " . ($couponData['title'] ?? 'N/A') . "\n";
    echo "   类型: " . $this->getCouponTypeName($couponType) . " (type={$couponType})\n";
    echo "   商户ID: {$couponMerId}\n";
    echo "   最低消费: {$useMinPrice} 元\n";
    echo "   优惠金额: " . ($coupon['coupon_price'] ?? '0.00') . " 元\n";
    echo "   有效期: " . ($coupon['start_time'] ?? 'N/A') . " ~ " . ($coupon['end_time'] ?? 'N/A') . "\n";

    // 检查是否可用
    $canUse = true;
    $reason = '';

    // 检查优惠券状态
    if ($status !== 1) {
        $canUse = false;
        $reason = "优惠券状态异常 (status={$status})";
    }

    // 检查是否删除
    if ($isDel !== 0) {
        $canUse = false;
        $reason = "优惠券已删除";
    }

    // 检查金额门槛
    if (bccomp($amount, $useMinPrice, 2) < 0) {
        $canUse = false;
        $reason = "订单金额不足 (需要满{$useMinPrice}元)";
    }

    // 检查优惠券类型和商户匹配
    if ($canUse) {
        if ($couponMerId === $merId && $couponType === 0) {
            $reason = "✓ 商户专属券，可用于当前商户";
        } elseif ($couponMerId === 0 && $couponType === 10) {
            $reason = "✓ 平台通用券，可用于任意商户";
        } elseif ($couponMerId === 0 && $couponType === 12) {
            // 检查商品是否包含当前商户
            $productIds = array_column((array)($coupon['product'] ?? []), 'product_id');
            if (in_array($merId, $productIds, true)) {
                $reason = "✓ 平台跨店券，包含当前商户商品";
            } else {
                $canUse = false;
                $reason = "✗ 平台跨店券，但不包含当前商户商品";
            }
        } else {
            $canUse = false;
            $reason = "✗ 优惠券类型不支持用于惠买单";
        }
    }

    echo "   状态: " . ($canUse ? "✅ 可用" : "❌ 不可用") . "\n";
    echo "   原因: {$reason}\n\n";
}

// 3. 调用实际方法测试
echo "3. 调用 usableForHuimaidan 方法测试...\n\n";
$repository = app()->make(StoreCouponUserRepository::class);
$result = $repository->usableForHuimaidan($uid, $merId, $amount, 1, 100);

echo "返回结果:\n";
echo "   count: " . ($result['count'] ?? 0) . "\n";
echo "   total: " . ($result['total'] ?? 0) . "\n";
echo "   list: " . json_encode($result['list'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

if (empty($result['list'])) {
    echo "⚠️  没有找到可用的惠买单优惠券\n";
    echo "   可能原因：\n";
    echo "   1. 优惠券类型不是商户专属券(type=0)、平台通用券(type=10)或平台跨店券(type=12)\n";
    echo "   2. 商户专属券的商户ID与当前商户不匹配\n";
    echo "   3. 平台跨店券的商品不包含当前商户\n";
    echo "   4. 订单金额未达到优惠券使用门槛\n";
} else {
    echo "✅ 找到 " . count($result['list']) . " 张可用优惠券\n";
}

/**
 * 获取优惠券类型名称
 */
function getCouponTypeName(int $type): string
{
    $types = [
        0 => '商户专属券',
        10 => '平台通用券',
        12 => '平台跨店券',
    ];
    return $types[$type] ?? '未知类型';
}
