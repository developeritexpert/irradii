<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/screen.css',
        'css/main.css',
        'css/form.css',
        ['css/print.css', 'media' => 'print'],
    ];
    
    public $js = [
        // Add the Migrate plugin and shim here
        'https://code.jquery.com/jquery-migrate-3.4.1.min.js',
        'js/jquery-shim.js',
        // Your other custom scripts
        // 'js/plugin/datatables/sorting/num-html.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset', // CRITICAL: This ensures jQuery loads before your JS
    ];
}