<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$dbConfig = require __DIR__ . '/config/db.php';
$appConfig = [
    'id' => 'debug-app',
    'basePath' => __DIR__,
    'components' => [
        'db' => $dbConfig,
    ],
];

// No need for a full web application for DB access
new yii\console\Application($appConfig);

$userId = 325276;

$results = Yii::$app->db->createCommand("
    SELECT ss.id, ss.name, sc.attr_name, sc.attr_value 
    FROM tbl_saved_searches ss 
    JOIN tbl_saved_search_criteria sc ON ss.id = sc.saved_search_id 
    WHERE ss.user_id = :uid
", [':uid' => $userId])->queryAll();

$searches = [];
foreach ($results as $row) {
    $searches[$row['id']]['name'] = $row['name'];
    $searches[$row['id']]['criteria'][] = [
        'name' => $row['attr_name'],
        'value' => $row['attr_value']
    ];
}

foreach ($searches as $id => $data) {
    echo "Search ID: $id - Title: {$data['name']}\n";
    foreach ($data['criteria'] as $c) {
        $val = @unserialize($c['value']);
        if ($val === false && $c['value'] !== 'b:0;') {
            $val = $c['value'];
        }
        echo "  Crit: {$c['name']} = " . (is_array($val) ? json_encode($val) : $val) . "\n";
    }
    echo "---------------------------------\n";
}

// Check some properties
$props = Yii::$app->db->createCommand("SELECT property_id, property_price, bedrooms, property_type FROM property_info LIMIT 5")->queryAll();
echo "Sample properties:\n";
foreach ($props as $p) {
    echo "  ID: {$p['property_id']} - Price: {$p['property_price']} - Beds: {$p['bedrooms']} - Type: {$p['property_type']}\n";
}
