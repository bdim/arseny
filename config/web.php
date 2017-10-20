<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'werretgteghtrhyt',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
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
        'telegram' => [
            'class' => 'aki\telegram\Telegram',
            'botToken' => '387788348:AAHBcxXi9NkxhJz0LIrQku39M1E70DdzIAY',
        ],
        'db' => $db,        

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
	        'enableStrictParsing' => true,
            'rules' => [
                '/' => 'site/index',
                //'<action:(login|logout|auth|authback|changepass|resetpass|sendpass|telegramset|telegramhook)>' => 'site/<action>',

                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>' => '<controller>/index',

                //'<action:\w+>' => 'site/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',

                '<module:\w+>/<controler:\w+>/<id:\d+>' => '<module>/<controller>/index',
                '<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',

            ],
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
        'Start' =>  [
            'class' => 'app\components\StartController',
        ],

        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/auth' => 'auth.php'
                    ]
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
        ],
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'EUR',
        ],

    ],
    'params' => $params,
    'sourceLanguage' => 'en-US',
    'language' => 'ru-RU',
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
