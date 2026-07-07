<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\PoolRepository;
use app\validate\admin\HuimaidanPoolValidate;
use think\exception\ValidateException;

$reflection = new ReflectionClass(PoolRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$assertAlarmConfig = $reflection->getMethod('assertAlarmConfig');
$assertAlarmConfig->setAccessible(true);

$assertThrows = function (array $data, string $message) use ($assertAlarmConfig, $repository): void {
    try {
        $assertAlarmConfig->invoke($repository, $data);
    } catch (ValidateException $e) {
        return;
    }
    throw new RuntimeException($message);
};

$assertAlarmConfig->invoke($repository, ['alarm_enabled' => 1, 'alarm_balance' => '0.00']);
$assertThrows(['alarm_enabled' => 2, 'alarm_balance' => '100.00'], '非法预警开关应失败');
$assertThrows(['alarm_enabled' => 1, 'alarm_balance' => '-0.01'], '负预警金额应失败');

$validateReflection = new ReflectionClass(HuimaidanPoolValidate::class);
$rule = $validateReflection->getProperty('rule');
$rule->setAccessible(true);
$rules = $rule->getValue(new HuimaidanPoolValidate());
foreach (['alarm_balance|预警金额', 'alarm_enabled|预警开关'] as $field) {
    if (strpos($rules[$field] ?? '', 'require') === false) {
        throw new RuntimeException($field . ' 应声明 require 校验');
    }
}

echo "PoolRepositoryAlarmConfigTest passed\n";
