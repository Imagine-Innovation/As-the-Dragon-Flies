<?php

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/console.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['websocket'],
                    'logFile' => '@runtime/logs/websocket.log',
                    'logVars' => [], // Don't log context variables
                    'exportInterval' => 1, // Export logs immediately for real-time monitoring
                ],
            ],
        ],
        'eventHandler' => [
            'class' => 'common\extensions\EventHandler\EventHandler',
        ],
        'httpclient' => [
            'class' => 'yii\httpclient\Client',
            // Optional: Configure default options
            'requestConfig' => [
                'format' => yii\httpclient\Client::FORMAT_JSON,
            ],
            'responseConfig' => [
                'format' => yii\httpclient\Client::FORMAT_JSON,
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'xx', // if set to 'en', il8n uses keys, not translated values
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/guest' => 'guest.php',
                        'app/lobby' => 'lobby.php',
                        'app/error' => 'error.php',
                        'app/game' => 'game.php',
                    ],
                ],
            ],
        ],
    ],
];
