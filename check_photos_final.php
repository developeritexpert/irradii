<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'config/console.php';
$app = new yii\console\Application($config);

$db = Yii::$app->db;
$tables = $db->createCommand('SHOW TABLES')->queryColumn();
print_r($tables);

foreach ($tables as $table) {
    if (strpos($table, 'photo') !== false) {
        echo "\nStructure of $table:\n";
        $cols = $db->createCommand("DESCRIBE `$table`")->queryAll();
        print_r($cols);
    }
}
