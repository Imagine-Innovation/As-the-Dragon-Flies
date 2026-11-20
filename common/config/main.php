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
        'db' => [
            // Long polling makes each game page issue a request every few hundred
            // milliseconds per player. Without schema cache, every single request
            // pays a full schema introspection (SHOW FULL COLUMNS / SHOW CREATE
            // TABLE / FK lookups over every table), which delays session
            // initialization by seconds and lengthens the time the session file
            // lock is held.
            // After a database structure change (migrations), flush with:
            // php yii cache/flush-all
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 3600,
            // Reuse the PDO connection across requests in the same worker: with
            // non-persistent connections every request pays the full MariaDB
            // handshake, which can take seconds if localhost first tries an
            // unreachable IPv6 loopback before falling back to 127.0.0.1.
            'attributes' => [
                \PDO::ATTR_PERSISTENT => true,
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/console.log',
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'xx', // if set to 'en', i18n uses keys, not translated values
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/guest' => 'guest.php',
                        'app/lobby' => 'lobby.php',
                        'app/error' => 'error.php',
                        'app/game' => 'game.php',
                        'app/item' => 'item.php',
                    ],
                ],
            ],
        ],
    ],
];
