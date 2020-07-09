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
                'interview' => 'main/default/interview',
                'gerchikov-test-conclusion-view/<id:\d+>' => 'main/default/gerchikov-test-conclusion-view',
                'interview-analysis/<id:\d+>' => 'main/default/interview-analysis',
                'upload' => 'main/default/upload',
                'record' => 'main/default/record',
                'analysis' => 'main/default/analysis',
                '/knowledge-base/<_kb:(list|upload)>' => 'main/knowledge-base/<_kb>',
                '/knowledge-base/<_kb:(view|update|delete|knowledge-base-download)>/<id:\d+>' => 'main/knowledge-base/<_kb>',
                '/respondent/<_res:(list|create)>' => 'main/respondent/<_res>',
                '/respondent/<_res:(view|update|delete)>/<id:\d+>' => 'main/respondent/<_res>',
                '/customer/<_cus:(list|create)>' => 'main/customer/<_cus>',
                '/customer/<_cus:(view|update|delete)>/<id:\d+>' => 'main/customer/<_cus>',
                '/question/<_ques:(list|create)>' => 'main/question/<_ques>',
                '/question/<_ques:(view|update|delete|audio-file-download)>/<id:\d+>' => 'main/question/<_ques>',
                '/video-interview/<_vi:(list|upload)>' => 'main/video-interview/<_vi>',
                '/video-interview/<_vi:(view|update|delete|video-download|get-landmarks)>/<id:\d+>' =>
                    'main/video-interview/<_vi>',
                '/landmark/<_lm:(list|upload)>' => 'main/landmark/<_lm>',
                '/landmark/<_lm:(view|update|delete|landmark-file-download)>/<id:\d+>' => 'main/landmark/<_lm>',
                '/analysis-result/<_ar:(list)>' => 'main/analysis-result/<_ar>',
                '/analysis-result/<_ar:(detection)>/<id:\d+>/<processingType:\d+>' => 'main/analysis-result/<_ar>',
                '/analysis-result/<_ar:(view|update|delete|detection-file-download|facts-download|interpretation-file-download|interpretation-facts-download)>/<id:\d+>' =>
                    'main/analysis-result/<_ar>',
                '/detection-result/<_dr:(list)>' => 'main/detection-result/<_dr>',
                '/detection-result/<_dr:(view|update|delete|file-download|facts-download)>/<id:\d+>' =>
                    'main/detection-result/<_dr>',
                '/interpretation-result/<_ir:(list)>' => 'main/interpretation-result/<_ir>',
                '/interpretation-result/<_ir:(view|update|delete|file-download)>/<id:\d+>' =>
                    'main/interpretation-result/<_ir>',
                '/gerchikov-test-conclusion/<_gtc:(list)>' => 'main/gerchikov-test-conclusion/<_gtc>',
                '/gerchikov-test-conclusion/<_gtc:(view|delete)>/<id:\d+>' => 'main/gerchikov-test-conclusion/<_gtc>',
                '/final-conclusion/<_fc:(list)>' => 'main/final-conclusion/<_fc>',
                '/final-conclusion/<_fc:(view|delete)>/<id:\d+>' => 'main/final-conclusion/<_fc>',
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