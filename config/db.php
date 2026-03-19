<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => $_ENV['DB_DSN'] ?? 'mysql:host=127.0.0.1;dbname=ippraisall',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? 'password',
    'charset' => 'utf8',
    'tablePrefix' => 'tbl_',
];