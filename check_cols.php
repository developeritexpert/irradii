<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'config/web.php';
new yii\web\Application($config);
try {
    $cols = Yii::$app->db->getTableSchema('tbl_users')->columnNames;
    print_r($cols);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
