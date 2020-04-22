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
            // secret key (this is required by cookie validation)
            'cookieValidationKey' => 'EGP0tfP_8uLB0S45rX87hIbTCv_OCYa4',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            //'class' => 'app\components\LangUrlManager',
            'rules' => [
                '/' => 'main/default/index',
                'analysis' => 'main/default/analysis',
                '/default/knowledge-base' => 'main/default/knowledge-base',
                '/default/knowledge-base-upload' => 'main/default/knowledge-base-upload',
                '/default/knowledge-base-download' => 'main/default/knowledge-base-download',
                '/respondent/<_res:(list|create)>' => 'main/respondent/<_res>',
                '/respondent/<_res:(view|update|delete)>/<id:\d+>' => 'main/respondent/<_res>',
                '/customer/<_cus:(list|create)>' => 'main/customer/<_cus>',
                '/customer/<_cus:(view|update|delete)>/<id:\d+>' => 'main/customer/<_cus>',
                '/video-interview/<_vi:(list|upload)>' => 'main/video-interview/<_vi>',
                '/video-interview/<_vi:(view|update|delete|video-download)>/<id:\d+>' => 'main/video-interview/<_vi>',
                '/landmark/<_lm:(list|upload)>' => 'main/landmark/<_lm>',
                '/landmark/<_lm:(view|update|delete|landmark-file-download)>/<id:\d+>' => 'main/landmark/<_lm>',
                '/analysis-result/<_ar:(list)>' => 'main/analysis-result/<_ar>',
                '/analysis-result/<_ar:(view|update|delete|detection|detection-file-download|facts-download|interpretation-file-download)>/<id:\d+>' =>
                    'main/analysis-result/<_ar>',
                '/detection-result/<_dr:(list)>' => 'main/detection-result/<_dr>',
                '/detection-result/<_dr:(view|update|delete|file-download|facts-download)>/<id:\d+>' =>
                    'main/detection-result/<_dr>',
                '/interpretation-result/<_ir:(list)>' => 'main/interpretation-result/<_ir>',
                '/interpretation-result/<_ir:(view|update|delete|file-download)>/<id:\d+>' =>
                    'main/interpretation-result/<_ir>',
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
        // Подключение расширения для запуска консольных команд в фоновом режиме в среде Yii
        'consoleRunner' => [
            'class' => 'vova07\console\ConsoleRunner',
            'file' => '@app/yii'
        ]
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