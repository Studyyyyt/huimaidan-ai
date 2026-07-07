<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\PoolAlarmFormatter;

function assertPoolAlarmSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, 'Actual:   ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$formatter = new PoolAlarmFormatter();

assertPoolAlarmSame(true, $formatter->shouldNotify('99.99', '100.00', 1, null, strtotime('2026-05-31 10:00:00')), '余额低于阈值且未通知过时应触发预警');
assertPoolAlarmSame(true, $formatter->shouldNotify('100.00', '100.00', 1, null, strtotime('2026-05-31 10:00:00')), '余额等于阈值时应触发预警');
assertPoolAlarmSame(false, $formatter->shouldNotify('100.01', '100.00', 1, null, strtotime('2026-05-31 10:00:00')), '余额高于阈值时不应触发预警');
assertPoolAlarmSame(false, $formatter->shouldNotify('99.99', '100.00', 0, null, strtotime('2026-05-31 10:00:00')), '关闭预警开关时不应触发预警');
assertPoolAlarmSame(false, $formatter->shouldNotify('99.99', '100.00', 1, '2026-05-31 09:45:00', strtotime('2026-05-31 10:00:00')), '30分钟内重复预警应被抑制');
assertPoolAlarmSame(true, $formatter->shouldNotify('99.99', '100.00', 1, '2026-05-31 09:29:59', strtotime('2026-05-31 10:00:00')), '超过30分钟后应允许再次预警');

$record = $formatter->recordData(3, 9, '88.00', '100.00', PoolAlarmFormatter::SOURCE_DEDUCT);
assertPoolAlarmSame(3, $record['pool_id'], '预警记录应包含垫资池ID');
assertPoolAlarmSame(9, $record['mer_id'], '预警记录应包含商户ID');
assertPoolAlarmSame('88.00', $record['balance'], '预警记录应保留触发余额');
assertPoolAlarmSame(PoolAlarmFormatter::STATUS_PENDING, $record['notice_status'], '预警记录初始状态应为待通知');

echo "PoolAlarmFormatterTest passed\n";
