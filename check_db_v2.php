<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'config/web.php';
new yii\web\Application($config);
try {
    $db = Yii::$app->db;
    $db->open();
    echo "Connection OK\n";
    $tables = $db->createCommand('SHOW TABLES')->queryColumn();
    echo "Tables:\n";
    print_r($tables);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
