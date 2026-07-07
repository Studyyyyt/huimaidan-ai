<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\SettlementCalculator;

function assertSettlementSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, 'Actual:   ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$calculator = new SettlementCalculator();

$summary = $calculator->summary([
    ['pay_price' => '180.00', 'merchant_cost_amount' => '150.00', 'platform_profit' => '30.00'],
    ['pay_price' => '0.01', 'merchant_cost_amount' => '10.00', 'platform_profit' => '-9.99'],
]);

assertSettlementSame(2, $summary['order_count'], '经营汇总应统计订单数量');
assertSettlementSame('180.01', $summary['pay_amount'], '经营汇总应统计用户实付金额');
assertSettlementSame('160.00', $summary['merchant_cost_amount'], '经营汇总应统计商家底价金额');
assertSettlementSame('20.01', $summary['platform_profit'], '经营汇总不得隐藏负差价');

$compare = $calculator->monthCompare('120.00', '100.00');
assertSettlementSame('20.00', $compare['change_amount'], '月度对比应返回差额');
assertSettlementSame('20.00', $compare['change_rate'], '月度对比应返回百分比');

$zeroCompare = $calculator->monthCompare('20.00', '0.00');
assertSettlementSame(null, $zeroCompare['change_rate'], '上月为0时不得伪造环比百分比');

echo "SettlementCalculatorTest passed\n";
