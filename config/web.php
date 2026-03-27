<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                    'clientId' => 'YOUR_GOOGLE_CLIENT_ID',
                    'clientSecret' => 'YOUR_GOOGLE_CLIENT_SECRET',
                ],
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => 'YOUR_FACEBOOK_CLIENT_ID',
                    'clientSecret' => 'YOUR_FACEBOOK_CLIENT_SECRET',
                ],
                // Add other social clients as needed
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'jV8DXBLf09CUpwS5suKm_DLtMVJmSn5T',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => 'smtps',
                'host' => $params['smtp']['host'],
                'username' => $params['smtp']['username'],
                'password' => $params['smtp']['password'],
                'port' => $params['smtp']['port'],
                'options' => [],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'search' => [
            'class' => 'app\components\search\DGSphinxSearch',
            'server' => '127.0.0.1',
            'port' => 9312,
            'maxQueryTime' => 3000,
            'fieldWeights' => [
                'name' => 10000,
                'keywords' => 100,
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'user/login' => 'login/login',
                'landing/post/<id:\d+>/<title:.*>' => 'post/view',
                'landing/post/<id:\d+>' => 'post/view',
                'blog' => 'post/index',
                'searches/alerts' => 'searches/alerts',
                'searches/delete' => 'searches/delete',
                'searches/editable' => 'searches/editable',
                'searches/unsubscribe/<email>' => 'searches/unsubscribe',
                'stat-info/uploadalertsmessages' => 'stat-info/upload-alerts-messages',
                '<slug:[a-zA-Z0-9\-]+>' => 'landing/landing',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
