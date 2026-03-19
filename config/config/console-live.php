<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
     'timeZone' => 'America/Los_Angeles',
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
        'application.modules.rights.models.*',
        'application.modules.rights.components.*',

        'application.extensions.chat.*',

        'application.helpers.*',
        'ext.YiiMailer.YiiMailer',
        
        'ext.s3.*',
        
    ),
    // application components
    'components'=>array(

        // uncomment the following to use a MySQL database
        'db'=>array(
//            'connectionString' => 'mysql:host=dbserver;dbname=bucontra_propertyhookup',
            'connectionString' => 'mysql:host=live.cypu3wsnk6wt.us-west-2.rds.amazonaws.com;dbname=bucontra_propertyhookup',
            'emulatePrepare' => true,
            'username' => 'irradii',
            'password' => $_ENV['LEGACY_DB_PASSWORD'],
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
            'server' => 'sphinxserver',
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
                'property/details/<slug:[a-zA-Z0-9-]+>/'=>'property/details',
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
            'showScriptName'=>false,
        ),
	'image'=>array(
          'class'=>'application.extensions.image.CImageComponent',
          'driver'=>'GD',   
        ),
        's3' => array(
            'class' => 'ext.s3.ES3',
            'aKey'=>$_ENV['AWS_S3_KEY_LEGACY'], 
            'sKey'=>$_ENV['AWS_S3_SECRET_LEGACY'],
        ),
        'redisCache'=>array(
            'class'=>'CRedisCache',
            'hostname'=>'redis-server',
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