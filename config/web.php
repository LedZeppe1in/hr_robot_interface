<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'hrrobot',
    'name' => 'HR Robot',
    'defaultRoute' => 'main/default/index',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    // all site modules
    'modules' => [
        'main' => [
            'class' => 'app\modules\main\Module',
        ],
    ],

    'language' => 'ru_RU',

    'components' => [
        'language' => 'ru-RU',
        'request' => [
            // site root directory
            'baseUrl' => '',
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'EGP0tfP_8uLB0S45rX87hIbTCv_OCYa4',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            //'class' => 'app\components\LangUrlManager',
            'rules' => [
                '/' => 'main/default/index',
                '/respondent/<_res:(list|create)>' => 'main/respondent/<_res>',
                '/respondent/<_res:(view|update|delete)>/<id:\d+>' => 'main/respondent/<_res>',
                '/customer/<_cus:(list|create)>' => 'main/customer/<_cus>',
                '/customer/<_cus:(view|update|delete)>/<id:\d+>' => 'main/customer/<_cus>',
                '/video-interview/<_vi:(list|upload)>' => 'main/video-interview/<_vi>',
                '/video-interview/<_vi:(view|delete|video-download|landmark-download)>/<id:\d+>' => 'main/video-interview/<_vi>',
                '/analysis-result/<_ar:(list)>' => 'main/analysis-result/<_ar>',
                '/analysis-result/<_ar:(view|detection|detection-file-download|delete)>/<id:\d+>' => 'main/analysis-result/<_ar>',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\modules\main\models\User',
            'enableAutoLogin' => true,
            //'loginUrl' => ['main/default/sing-in'],
        ],
        'errorHandler' => [
            'errorAction' => 'main/default/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
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
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'forceTranslation' => true,
                    'sourceLanguage' => 'en-US',
                ],
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