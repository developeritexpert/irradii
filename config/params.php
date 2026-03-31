<?php

return [
    'adminEmail' => 'ippraisall.com <admin@ippraisall.com>',
    'senderEmail' => 'noreply@ippraisall.com',
    'senderName' => 'ippraisall.com',
    'chatMessage' => 'ippraisall.com',
    
    // CDN Configuration
    'cdnCdn' => '//cdn.ippraisall.com',
    'cdnImg' => '//css.ippraisall.com/assets',
    'cdnJs' => '//js1.ippraisall.com/assets',
    'cdnCss' => '//css.ippraisall.com',
    'cdnImages' => '//img1.ippraisall.com',
    'cdnPhotos' => '//img1.ippraisall.com',
    
    // AWS S3 Credentials
    'awsKeys' => [
        'key'    => $_ENV['AWS_S3_KEY'] ?? '',
        'secret' => $_ENV['AWS_S3_SECRET'] ?? '',
        'region' => $_ENV['AWS_S3_REGION'] ?? 'us-west-2',
        'bucket' => $_ENV['AWS_S3_BUCKET'] ?? 'props3photos',
    ],

    // AWS SES SMTP Configuration
    'smtp' => [
        'host' => $_ENV['SMTP_HOST'] ?? 'email-smtp.us-east-1.amazonaws.com',
        'username' => $_ENV['SMTP_USERNAME'] ?? '',
        'password' => $_ENV['SMTP_PASSWORD'] ?? '',
        'port' => $_ENV['SMTP_PORT'] ?? 465,
        'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'ssl',
    ],

    'googleMapsKey' => $_ENV['GOOGLE_MAPS_KEY'] ?? '',
    'googleAdSenseClient' => 'ca-pub-6090587812421587',
    'googleAdSenseEnable' => true,
    
    'zipCodeApiKey' => $_ENV['ZIPCODE_API_KEY'] ?? '',
    
    'underValueDeals' => 5,
    'hrefPage' => 'http://ippraisall.com/user/login/?fb1',
    'linkToBuyingSubscr' => '/user/profile',
    'linkToBuyingSubscrFreeTrial30days' => '/user/profile',

    // RETS Configuration
    'rets_login_url' => $_ENV['RETS_LOGIN_URL'] ?? 'https://rets.las.mlsmatrix.com/rets/login.ashx',
    'rets_username' => $_ENV['RETS_USERNAME'] ?? 'prop',
    'rets_password' => $_ENV['RETS_PASSWORD'] ?? 'glvaridx',
];
