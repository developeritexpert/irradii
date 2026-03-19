<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'config/web.php';
new yii\web\Application($config);
$db = Yii::$app->db;
$cols = $db->createCommand('DESCRIBE tbl_users')->queryAll();
$users = $db->createCommand('SELECT * FROM tbl_users LIMIT 2')->queryAll();
echo "COLUMNS:\n";
print_r($cols);
echo "USERS SAMPLE:\n";
print_r($users);
