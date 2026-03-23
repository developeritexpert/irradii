<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/config/web.php';
new yii\web\Application($config);

$table = 'property_info_details';
$columns = Yii::$app->db->getTableSchema($table)->columnNames;
echo "Columns in $table:\n";
print_r($columns);

$table2 = 'property_info';
$columns2 = Yii::$app->db->getTableSchema($table2)->columnNames;
echo "\nColumns in $table2:\n";
print_r($columns2);
