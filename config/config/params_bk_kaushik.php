<?php

return array(
    // this is used in contact page
    'adminEmail'=>'ippraisall.com <admin@ippraisall.com>',
    'chatMessage'=>'ippraisall.com',
    
                'cdnCdn'=>'//cdn.ippraisall.com',
                'cdnImg'=>'//css.ippraisall.com/assets',
                'cdnJs'=>'//js1.ippraisall.com/assets',
                'cdnCss'=>'//css.ippraisall.com',
                'cdnImages'=>'//img1.ippraisall.com',
                'cdnPhotos'=>'//img1.ippraisall.com',
    
    // for FB and Twitter
    'hrefPage' => 'http://ippraisall.com/user/login/?fb1',

    'underValueDeals' => 5,
    'maxCalcStages' => 100,
    'maxCyclesResearch' => 5,
    'maxPrecisionResearch' => 0.1, // %
    'minTcountResearch' => 3,
    'maxEstimatedPriceRecalc'=>2, // need recalculate older than days
    
    'percentageDepreciationValueMin' => -100,
    'percentageDepreciationValueMax' => 85,

    'YiiMailer' => array(
        'viewPath' => '/themes/propertyhookup/mail-templates',
        'layoutPath' => 'application.views.layouts',
        'baseDirPath' => 'webroot.images.mail', //note: 'webroot' alias in console apps may not be the same as in web apps
        'savePath' => 'webroot.assets.mail',
        'testMode' => false,
        'layout' => 'mail',
        'CharSet' => 'UTF-8',
        'AltBody' => Yii::t('YiiMailer', 'You need an HTML capable viewer to read this message.'),
        'language' => array(
            'authenticate' => Yii::t('YiiMailer', 'SMTP Error: Could not authenticate.'),
            'connect_host' => Yii::t('YiiMailer', 'SMTP Error: Could not connect to SMTP host.'),
            'data_not_accepted' => Yii::t('YiiMailer', 'SMTP Error: Data not accepted.'),
            'empty_message' => Yii::t('YiiMailer', 'Message body empty'),
            'encoding' => Yii::t('YiiMailer', 'Unknown encoding: '),
            'execute' => Yii::t('YiiMailer', 'Could not execute: '),
            'file_access' => Yii::t('YiiMailer', 'Could not access file: '),
            'file_open' => Yii::t('YiiMailer', 'File Error: Could not open file: '),
            'from_failed' => Yii::t('YiiMailer', 'The following From address failed: '),
            'instantiate' => Yii::t('YiiMailer', 'Could not instantiate mail function.'),
            'invalid_address' => Yii::t('YiiMailer', 'Invalid address'),
            'mailer_not_supported' => Yii::t('YiiMailer', ' mailer is not supported.'),
            'provide_address' => Yii::t('YiiMailer', 'You must provide at least one recipient email address.'),
            'recipients_failed' => Yii::t('YiiMailer', 'SMTP Error: The following recipients failed: '),
            'signing' => Yii::t('YiiMailer', 'Signing Error: '),
            'smtp_connect_failed' => Yii::t('YiiMailer', 'SMTP Connect() failed.'),
            'smtp_error' => Yii::t('YiiMailer', 'SMTP server error: '),
            'variable_set' => Yii::t('YiiMailer', 'Cannot set or reset variable: ')
        ),
// if you want to use SMTP, uncomment and configure lines below to your needs
	'Mailer' => 'smtp',
//	'Host' => 'smtp.mailgun.org',
	'Host' => 'email-smtp.us-east-1.amazonaws.com',
//	'Port' => 465,
//	'SMTPSecure' => 'ssl',
	'Port' => 587,
	'SMTPSecure' => 'tls',
    'SMTPAuth' => true,
    'SMTPDebug' => 2,
	'Username' => $_ENV['SMTP_USERNAME'],
	'Password' => $_ENV['SMTP_PASSWORD'],
//	'Username' => 'ict.girin@gmail.com',
//	'Password' => '23sk1k9hze',
//	'Username' => 'clircrazy@gmail.com',
//	'Password' => '3rjatljj,tlf',
    ),

    'rets_login_url' => "http://rets.las.mlsmatrix.com/rets/login.ashx",
    'rets_username' => "prop",
    'rets_password' => "glvaridx",
    
    'googleAdSenseClient' => 'ca-pub-6090587812421587',
    'googleAdSenseEnable' => true,

    'seoControllerList' => array(
        'property/details' => array(
            'PropertyInfo'
        )
    ),
    'seoModelList' => array(
        'PropertyInfo',
        'PropertyInfoAdditionalBrokerageDetails',
        'PropertyInfoAdditionalDetails',
        'PropertyInfoDetails',
//        'PropertyInfoPhoto',
    ),
    
    'sitemapLimit' => 45000,
    'sitemapRedisId' => 'siteMapCron',
    'sitemap' => array(
        'actions' => array(
                'site/index',
                'property/search',
                array(
                    'route' => 'property/details',
//                            'condition' => 'return !Yii::app()->user->getIsGuest();', //only if user is not guest
//                            'prefs' => array( //specify lastmod, changefreq and priority for this URL only
//                                    'lastmod' => '2012-07-01',
//                                    'changefreq' => 'daily',
//                                    'priority' => 0.7,
//                            ),  
                    'params' => array( //specify action parameters
//                                    'array' => array( //parameters provided in an array
//                                            array('postId' => 50, 'postName' => 'Welcome'),
//                                    ),
                        'model' => array(
                                'class' => 'PropertyInfoSlug',
                                'criteria' => array(
//                                    'condition' => " property_id <=  5000000",
                                    ),
                                'map' => array(
                                        'slug' => 'slug',
                                ),
                        ),				
                    ),
                ),
            ),
        'protectedControllers' => array('collection','debug','statinfo','registration','saved','statInfo','searches'),
        'protectedActions' =>array('site/error'),
    ),

    // Blog
    // this is displayed in the header section
    'title'=>'Irradii Blog',
    // number of posts displayed per page
    'postsPerPage'=>10,
    // maximum number of comments that can be displayed in recent comments portlet
    'recentCommentCount'=>10,
    // maximum number of tags that can be displayed in tag cloud portlet
    'tagCloudCount'=>20,
    // whether post comments need to be approved before published
    'commentNeedApproval'=>true,

    //Local Vendors
    'adminEmailForLocalVendorActivities' => 'kallen@bucontractors.com',

    //Links to subscription purchasing page
    'linkToBuyingSubscr' => '/user/profile',
//    'linkToBuyingSubscrFreeTrial30days' => '/user/profile',
//    'linkToBuyingSubscr' => 'http://irradii.com/post/42/Full+Access+Membership',
//    'linkToBuyingSubscrFreeTrial30days' => 'http://irradii.com/post/55/Irradii.com+FREE+30+Day+Trial+',
	'linkToBuyingSubscr' => 'http://ippraisall.com',
	'linkToBuyingSubscrFreeTrial30days' => 'http://ippraisall.com',

    //key for https://www.zipcodeapi.com
    'zipCodeApiKey' => 'js-jE3Mv88QJ0tuH6KIVufnFRRmu0CHN9K6NNTjV19F1D9JJUsFoH33zUq4U2LHcvO0',

);
