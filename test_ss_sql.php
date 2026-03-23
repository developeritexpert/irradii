<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
// Override DB for CLI
$config['components']['db']['dsn'] = 'mysql:host=127.0.0.1;dbname=irradi_db';
$config['components']['db']['username'] = 'root';
$config['components']['db']['password'] = '';

new yii\web\Application($config);

// Test query directly
$address = "Las Vegas, NV, USA";
$q = \app\models\PropertyInfo::find()->joinWith(['city', 'state']);

if (preg_match('/^([^,]+),\s*([A-Z]{2}),\s*USA$/i', $address, $matches)) {
    $cityName = trim($matches[1]);
    $stateCode = strtoupper(trim($matches[2]));
    
    echo "Pattern matched: '$cityName', '$stateCode'\n";
    
    // Test 1: Exact match (current logic)
    $q1 = clone $q;
    $q1->andWhere(['city.city_name' => $cityName]);
    $q1->andWhere(['state.state_code' => $stateCode]);
    $q1->andWhere(['property_info.visible' => 1]); 
    echo "SQL 1 (Exact): " . $q1->createCommand()->rawSql . "\n";
    echo "Count 1: " . $q1->count() . "\n";

    // Test 2: Case-insensitive/LIKE
    $q2 = clone $q;
    $q2->andWhere(['like', 'city.city_name', $cityName]);
    $q2->andWhere(['state.state_code' => $stateCode]);
    $q2->andWhere(['property_info.visible' => '1']); 
    echo "SQL 2 (LIKE + string visible): " . $q2->createCommand()->rawSql . "\n";
    echo "Count 2: " . $q2->count() . "\n";
    
    // Test 3: Uppercase
    $q3 = clone $q;
    $q3->andWhere(['city.city_name' => strtoupper($cityName)]);
    $q3->andWhere(['state.state_code' => $stateCode]);
    $q3->andWhere(['property_info.visible' => '1']);
    echo "SQL 3 (Uppercase): " . $q3->createCommand()->rawSql . "\n";
    echo "Count 3: " . $q3->count() . "\n";

    // Test 4: Check column collation/case sensitivity
    $res = Yii::$app->db->createCommand("SELECT city_name FROM city WHERE city_name = '$cityName'")->queryOne();
    echo "Direct match for '$cityName': " . ($res ? "FOUND" : "NOT FOUND") . "\n";
    $resU = Yii::$app->db->createCommand("SELECT city_name FROM city WHERE city_name = '" . strtoupper($cityName) . "'")->queryOne();
    echo "Direct match for '" . strtoupper($cityName) . "': " . ($resU ? "FOUND" : "NOT FOUND") . "\n";
}
