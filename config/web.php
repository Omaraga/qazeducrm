<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'urlManager'],
    'language' => 'ru-RU',
    'timeZone' => 'Asia/Almaty',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'XIbu0x9zT3z7DofDUWOk_gO06sjiYHgu',
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'db' => 'db',
                    'sourceLanguage' => 'ru-RU', // Developer language
                    'sourceMessageTable' => '{{%language_source}}',
                    'messageTable' => '{{%language_translate}}',
                    'cachingDuration' => 86400,
                    'enableCaching' => false,
                ],
            ],
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
            // 'useFileTransport' to false and configure transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'authManager' => [
            'class' => 'app\components\PhpManager',
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
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<oid:\d+>/<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>' => '<controller>/<action>',
                '<oid:\d+>/<module:\w+-\w+|\w+>/<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>' => '<module>/<controller>/<action>',
                '<oid:\d+>/<module:\w+-\w+|\w+>/<module2:\w+-\w+|\w+>/<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>' => '<module>/<module2>/<controller>/<action>',
                '<oid:\d+>/<module:\w+-\w+|\w+>/<module2:\w+-\w+|\w+>/<module3:\w+-\w+|\w+>/<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>' => '<module>/<module2>/<module3>/<controller>/<action>',
                '<oid:\d+>/<module:\w+-\w+|\w+>/<module2:\w+-\w+|\w+>/<module3:\w+-\w+|\w+>/<module4:\w+-\w+|\w+>/<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>' => '<module>/<module2>/<module3>/<module4>/<controller>/<action>',


                '<module:\w+-\w+|\w+>/<controller:\w+-\w+|\w+>'=>'<module>/<controller>',
                '<module:\w+-\w+|\w+>/<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>'=>'<module>/<controller>/<action>',
                '<module:\w+-\w+|\w+>/<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>/*'=>'<module>/<controller>/<action>',

                '<controller:\w+-\w+|\w+>'=>'<controller>',
                '<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>'=>'<controller>/<action>',
                '<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>/*'=>'<controller>/<action>',
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
