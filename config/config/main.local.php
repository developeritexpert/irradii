<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'Irradii',
    'theme' => 'propertyhookup',
    //'defaultController' => '',

    // preloading 'log' component
    'preload'=>array('log'),

    'aliases' => array(
        'bootstrap' => 'application.modules.bootstrap',

    ),
    // autoloading model and component classes
    'import'=>array(
        'application.models.*',
        'application.components.*',

        'ext.YiiMailer.YiiMailer',
        //bootstrap
        'bootstrap.*',
        'bootstrap.components.*',
        'bootstrap.models.*',
        'bootstrap.behaviors.*',
        'bootstrap.controllers.*',
        'bootstrap.helpers.*',
        'bootstrap.widgets.*',
        'bootstrap.extensions.*',
        //user
        'application.modules.user.models.*',
        'application.modules.user.components.*',
        //rights
        'application.modules.rights.*',
        'application.modules.rights.models.*',
        'application.modules.rights.components.*',
        //image
        'application.helpers.*',

        //Chat
        'application.extensions.chat.*',

                // S3
                'ext.s3.*',

    ),

    'modules'=>array(
        // uncomment the following to enable the Gii tool
        /*
        'gii'=>array(
            'class'=>'system.gii.GiiModule',
            'password'=>'Enter Your Password Here',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            'ipFilters'=>array('127.0.0.1','::1'),
        ),
        */


        'bootstrap' => array(
            'class' => 'bootstrap.BootStrapModule',
        ),
        'gii' => array(
            'generatorPaths' => array('bootstrap.gii'),
            'class' => 'system.gii.GiiModule',
            'password' => '123',
            'ipFilters' => array('127.0.0.1','::1'),
        ),
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

    // application components
    'components'=>array(
        /*
                    'themeManager' => array(
                    'class'    => 'CThemeManager',
                    'basePath' => 'themes',
                    'baseUrl'  => '//cdn.irradii.com/themes'
                ),
        */
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

        'clientScript'=>array(
            'packages'=>array(
                'jquery'=>array(
                    'baseUrl'=>'//ajax.googleapis.com/ajax/libs/jquery/2.0.2/',
                    'js'=>array('jquery.min.js'),
                    'coreScriptPosition'=>  CClientScript::POS_HEAD
                ),
                'jquery.ui'=>array(
                    'baseUrl'=>'//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/',
                    'js'=>array('jquery-ui.min.js'),
                    'depends'=>array('jquery'),
                    'coreScriptPosition'=>  CClientScript::POS_BEGIN
                ),
            ),
        ),

        'image'=>array(
            'class'=>'application.extensions.image.CImageComponent',
            'driver'=>'GD',
        ),

        'BsHtml' => array(
            'class' => 'bootstrap.components.BsHtml',
        ),

        // uncomment the following to enable URLs in path-format

        'urlManager'=>array(
            'urlFormat'=>'path',
            'rules'=>array(
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),
            'showScriptName'=>false,
        ),
        /*
        'db'=>array(
            'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
        ),
        // uncomment the following to use a MySQL database
        */
        'db'=>array(
            'connectionString' => 'mysql:host=localhost;dbname=irradii',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'tablePrefix' => 'tbl_',
            'enableProfiling'=>true,
            'enableParamLogging' => true,
        ),

        'user'=>array(
            // enable cookie-based authentication
            'class' => 'RWebUser', // 'WebUser',
            'allowAutoLogin'=>true,
            'loginUrl' => array('/user/login'),
        ),

        'authManager'=>array(
            'class'=>'RDbAuthManager', //'CDbAuthManager',
            'connectionID'=>'db',
            'defaultRoles' => array('Guest'),
            'itemTable' => 'tbl_AuthItem',
            'itemChildTable' => 'tbl_AuthItemChild',
            'assignmentTable' => 'tbl_AuthAssignment',
            'rightsTable' => 'tbl_Rights',
        ),

        'errorHandler'=>array(
            // use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
//                                'class' => 'CFileLogRoute',
                    'levels' => 'trace, info, error, warning, vardump, profile',
                    'class'=>'CProfileLogRoute',
//                                'levels'=>'profile',
                    'enabled'=>true,
                ),
                // uncomment the following to show log messages on web pages
                /*
                array(
                                'class' => 'CWebLogRoute',
                                'enabled' => YII_DEBUG,
                                'levels' => 'error, warning, trace, notice',
                                'categories' => 'application',
                                'showInFireBug' => false,
                ),
                */
            ),
        ),
        'cache'=>array(
            'class'=>'CMemCache',
            'useMemcached'   => true,
            'servers'=>array(
                array(
                    'host'=>'localhost',
                    'port'=>11211,
                    'weight'=>60,
                ),
            ),
        ),
                'session' => array (
                    'class' => 'system.web.CDbHttpSession',
                    'connectionID' => 'db',
                    'sessionTableName' => 'tbl_session',
                ),
                's3' => array(
                    'class' => 'ext.s3.ES3',
                    'aKey'=>$_ENV['AWS_S3_KEY_LEGACY'], 
                    'sKey'=>$_ENV['AWS_S3_SECRET_LEGACY'],
                ),
                'assetManager' => array(
                    'class' => 'S3AssetManager',
                    'host' => 'css.irradii.com', //'props3assets.s3.amazonaws.com', // s3-us-west-2.amazonaws.com/props3assets/ // changing this you can point to your CloudFront hostname
                    'bucket' => 'props3assets',
                    'path' => 'assets', //or any other folder you want
                ),
        ),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params'=>require(dirname(__FILE__).'/params.php'),

);