<?php
require __DIR__ . '/../vendor/autoload.php';
use think\App;
$app = new App();
$app->initialize();

$repo = app()->make(\app\common\repositories\huimaidan\AiRecommendRepository::class);
$location = ['latitude' => 32.123456, 'longitude' => 110.654321, 'city_id' => 4737, 'city_name' => '呼和浩特'];
$intent = ['category' => ['火锅']];
$recall = $repo->wideRecall($intent, $location, 0);
echo "wideRecall count: " . $recall['count'] . "\n";
echo "wideRecall city_relaxed: " . ($recall['city_relaxed'] ? 'yes' : 'no') . "\n";
foreach ($recall['list'] as $m) {
    echo $m['mer_id'] . " " . $m['mer_name'] . " dist=" . ($m['distance_km'] ?? 'null') . " discount=" . ($m['has_discount'] ? 'yes' : 'no') . "\n";
}
