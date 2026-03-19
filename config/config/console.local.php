<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'Irradii',

    // preloading 'log' component
    'preload'=>array('log'),
    'import'=>array(
        'application.models.*',
        'application.components.*',

        'application.modules.user.models.*',
        'application.modules.user.components.*',

        'application.modules.rights.*',
        'application.modules.rights.components.*',

        'application.extensions.chat.*',

        'application.helpers.*',
        'ext.YiiMailer.YiiMailer',
    ),
    // application components
    'components'=>array(
        /*
    'db'=>array(
        'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
    ),
        */
        // uncomment the following to use a MySQL database
        'db'=>array(
            'connectionString' => 'mysql:host=localhost;dbname=bucontra_propertyhookup',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'tablePrefix' => '',
        ),

        'authManager'=>array(
            'class'=>'RDbAuthManager', //'CDbAuthManager',
            'connectionID'=>'db',
            'defaultRoles' => array('Guest'),
            'itemTable' => 'tbl_AuthItem',
            'itemChildTable' => 'tbl_AuthItemChild',
            'assignmentTable' => 'tbl_AuthAssignment',
        ),

        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                ),
            ),
        ),

        'search' => array(
            'class' => 'application.components.search.DGSphinxSearch',
            'server' => '127.0.0.1',
            'port' => 9312,
            'maxQueryTime' => 3000,
            'enableProfiling'=>0,
            'enableResultTrace'=>0,
            'fieldWeights' => array(
                'name' => 10000,
                'keywords' => 100,
            ),
        ),
        'request' => array(
            'hostInfo' => 'http://irradii.com',
            'baseUrl' => '',
            'scriptUrl' => '',
        ),
        'urlManager'=>array(
            'urlFormat'=>'path',
            'rules'=>array(
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
            'showScriptName'=>false,
        ),
        'redisCache'=>array(
            'class'=>'CRedisCache',
            'hostname'=>'localhost',
            'port'=>6379,
            'database'=>0,
        ),
    ),
    'modules'=>array(
        #...
        'user'=>array(
            # encrypting method (php hash function)
            'hash' => 'md5',

            # send activation email
            'sendActivationMail' => true,

            # allow access for non-activated users
            'loginNotActiv' => false,

            # activate user on registration (only sendActivationMail = false)
            'activeAfterRegister' => false,

            # automatically login from registration
            'autoLogin' => false,

            # registration path
            'registrationUrl' => array('/user/registration'),

            # recovery password path
            'recoveryUrl' => array('/user/recovery'),

            # login form path
            'loginUrl' => array('/user/login'),

            # page after login
            'returnUrl' => array('/user/profile'),

            # page after logout
            'returnLogoutUrl' => array('/user/login'),
        ),
        'rights'=>array(
            'install'=>false,
        ),
    ),
    'params' => require(dirname(__FILE__).'/params.php'),
);