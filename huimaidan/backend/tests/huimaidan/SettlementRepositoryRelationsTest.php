<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\SettlementRepository;

$reflection = new ReflectionClass(SettlementRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$merchantOrderRelations = $reflection->getMethod('merchantOrderRelations');
$merchantOrderRelations->setAccessible(true);
$relations = $merchantOrderRelations->invoke($repository, ['groupOrder']);

if (!in_array('groupOrder', $relations, true) || !isset($relations['user']) || !is_callable($relations['user'])) {
    throw new RuntimeException('商户订单关联应保留 groupOrder 并配置用户白名单');
}

$query = new class {
    public $fields;

    public function field(string $fields): void
    {
        $this->fields = $fields;
    }
};
$relations['user']($query);
if ($query->fields !== 'uid,nickname,avatar') {
    throw new RuntimeException('商户订单用户白名单不正确: ' . $query->fields);
}

echo "SettlementRepositoryRelationsTest passed\n";
