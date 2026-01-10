<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'Qazaq Education CRM',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'urlManager'],
    'language' => 'ru-RU',
    'timeZone' => 'Asia/Almaty',
    'defaultRoute' => 'landing/index',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'XIbu0x9zT3z7DofDUWOk_gO06sjiYHgu',
            // Парсеры для JSON запросов
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
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
        'assetManager' => [
            'appendTimestamp' => true,
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
        'impersonate' => [
            'class' => 'app\components\ImpersonateManager',
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
            'datetimeFormat' => 'dd.MM.yyyy HH:mm',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Webhook endpoints (без авторизации)
                'webhook/whatsapp' => 'webhook/whatsapp',

                // Публичные страницы
                '' => 'landing/index',
                'pricing' => 'landing/pricing',
                'features' => 'landing/features',
                'contact' => 'landing/contact',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'register' => 'registration/index',

                // Публичная документация
                'docs' => 'docs/index',
                'docs/search' => 'docs/search',
                'docs/<slug:[\w-]+>' => 'docs/chapter',
                'docs/<chapter:[\w-]+>/<slug:[\w-]+>' => 'docs/section',

                // Legacy routes с organization id (должны быть первыми для правильной генерации URL с oid)
                '<oid:\d+>/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => 'crm/<controller>/<action>',
                '<oid:\d+>/<controller:[\w-]+>/<action:[\w-]+>' => 'crm/<controller>/<action>',
                '<oid:\d+>/<controller:[\w-]+>' => 'crm/<controller>/index',
                '<oid:\d+>/<module:[\w-]+>/<controller:[\w-]+>/<action:[\w-]+>' => '<module>/<controller>/<action>',

                // CRM модуль (без oid)
                'crm' => 'crm/default/index',
                'crm/<controller:[\w-]+>' => 'crm/<controller>/index',
                'crm/<controller:[\w-]+>/<action:[\w-]+>' => 'crm/<controller>/<action>',
                'crm/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => 'crm/<controller>/<action>',

                // Superadmin модуль
                'superadmin' => 'superadmin/default/index',
                'superadmin/<controller:[\w-]+>' => 'superadmin/<controller>/index',
                'superadmin/<controller:[\w-]+>/<action:[\w-]+>' => 'superadmin/<controller>/<action>',
                'superadmin/<controller:[\w-]+>/<action:[\w-]+>/<id:\d+>' => 'superadmin/<controller>/<action>',

                // Общие правила
                '<controller:\w+-\w+|\w+>' => '<controller>',
                '<controller:\w+-\w+|\w+>/<action:\w+-\w+|\w+>' => '<controller>/<action>',
            ],
        ],

    ],
    'modules' => [
        'crm' => [
            'class' => 'app\modules\crm\Module',
        ],
        'superadmin' => [
            'class' => 'app\modules\superadmin\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
//    $config['bootstrap'][] = 'debug';
//    $config['modules']['debug'] = [
//        'class' => 'yii\debug\Module',
//        // uncomment the following to add your IP if you are not connecting from localhost.
//        //'allowedIPs' => ['127.0.0.1', '::1'],
//    ];
//
//    $config['bootstrap'][] = 'gii';
//    $config['modules']['gii'] = [
//        'class' => 'yii\gii\Module',
//        // uncomment the following to add your IP if you are not connecting from localhost.
//        //'allowedIPs' => ['127.0.0.1', '::1'],
//    ];
}

return $config;
