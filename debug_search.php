<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
$app = new yii\web\Application($config);

use app\models\SavedSearch;
use app\models\SavedSearchCriteria;
use app\models\PropertyInfo;

$userId = 325276; // From user's previous error log
$savedSearches = SavedSearch::find()->where(['user_id' => $userId])->all();

foreach ($savedSearches as $ss) {
    echo "Search ID: " . $ss->id . " - Title: " . $ss->name . "\n";
    $criteria = $ss->savedSearchCriteria;
    foreach ($criteria as $c) {
        $val = @unserialize($c->attr_value);
        if ($val === false && $c->attr_value !== 'b:0;') {
            $val = $c->attr_value;
        }
        echo "  Crit: " . $c->attr_name . " = " . (is_array($val) ? json_encode($val) : $val) . "\n";
    }
    
    // Test the fallback logic manually for this search
    $results = $ss->makeSearch(['limit' => 5]);
    echo "  Results Found (IDs): " . implode(', ', $results) . "\n";
    echo "---------------------------------\n";
}

// Check some properties in the DB to see if they match 400k
$lowPrice = PropertyInfo::find()->where(['<=', 'property_price', 400000])->limit(3)->all();
echo "Sample properties <= 400k:\n";
foreach ($lowPrice as $p) {
    echo "  ID: " . $p->property_id . " - Price: " . $p->property_price . "\n";
}
