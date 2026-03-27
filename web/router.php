<?php

/**
 * PHP built-in web server router script for Yii2.
 * 
 * This script handles the issue where URLs with dots (like .com) are 
 * treated as files instead of being routed to index.php.
 */

$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $path;

if ($path !== '/' && file_exists($file)) {
    return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/index.php';
