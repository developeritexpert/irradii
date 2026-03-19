<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'config/web.php';
new yii\web\Application($config);
try {
    $db = Yii::$app->db;
    $schema = $db->getTableSchema('tbl_profiles');
    if ($schema) {
        echo "Columns in tbl_profiles:\n";
        print_r($schema->columnNames);
    } else {
        echo "Table tbl_profiles not found!\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
