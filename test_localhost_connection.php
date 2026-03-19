<?php
$host = 'localhost';
$dbname = 'ippraisall';
$username = 'root';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "Connection to $host (via PDO) successful!\n";
} catch (PDOException $e) {
    echo "Connection to $host (via PDO) failed: " . $e->getMessage() . "\n";
}

$host_ip = '127.0.0.1';
try {
    $pdo = new PDO("mysql:host=$host_ip;dbname=$dbname", $username, $password);
    echo "Connection to $host_ip (via PDO) successful!\n";
} catch (PDOException $e) {
    echo "Connection to $host_ip (via PDO) failed: " . $e->getMessage() . "\n";
}
