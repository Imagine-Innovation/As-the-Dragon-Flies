<?php
declare(strict_types=1);

use common\components\AccessRightsManager;
use common\components\ContextManager;

$params = array_merge(
        require __DIR__ . '/../../common/config/params.php',
        require __DIR__ . '/../../common/config/params-local.php',
        require __DIR__ . '/params.php',
        require __DIR__ . '/params-local.php',
);
$offline = false;
if ($offline) {
    $assetManager = [
        'bundles' => [
            'yii\\bootstrap5\\BootstrapAsset' => [
                'sourcePath' => null, // do not publish the bundle from default path
                'css' => [
                    '/frontend/web/offline/css/bootstrap.min.css',
                    '/frontend/web/offline/css/bootstrap-icons.min.css',
                ],
                'js' => [
                    '/frontend/web/offline/js/bootstrap.bundle.min.js',
                ],
            ],
            'yii\\web\\JqueryAsset' => [
                'sourcePath' => null, // do not publish the bundle from default path
                'js' => [
                    '/frontend/web/offline/js/jquery.min.js',
                ],
            ],
        ],
    ];
} else {
    $assetManager = [
        'bundles' => [
            'yii\\bootstrap5\\BootstrapAsset' => [
                'sourcePath' => null, // do not publish the bundle from default path
                'css' => [
                    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
                    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css',
                ],
                'js' => [
                    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js',
                ],
            // Optional: Add integrity and crossorigin attributes if needed
            // 'cssOptions' => [
            //    'integrity' => 'sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN',
            //    'crossorigin' => 'anonymous',
            // ],
            // 'jsOptions' => [
            //    'integrity' => 'sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL',
            //    'crossorigin' => 'anonymous',
            // ],
            ],
            'yii\\web\\JqueryAsset' => [
                'sourcePath' => null, // do not publish the bundle from default path
                'js' => [
                    // JQuery 4.0 is not compatible with yii.validation.js
                    //'https://cdn.jsdelivr.net/npm/jquery@4.0.0/dist/jquery.min.js',
                    'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js',
                ],
            // Optional: Add integrity and crossorigin attributes if needed
            // 'jsOptions' => [
            //    'integrity' => 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=',
            //    'crossorigin' => 'anonymous',
            // ],
            ],
        ],
    ];
}
return [
    'id' => AccessRightsManager::APP_FRONTEND,
    'name' => 'As the Dragon Flies',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'common\components\LanguageSelector'],
    'language' => 'en',
    'sourceLanguage' => 'en',
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'eventHandler' => [
            'class' => 'common\extensions\EventHandler\EventHandler',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
            'on afterLogin' => function ($event) {
                ContextManager::initContext($event->identity);
            },
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/app.log',
                ],
            // You can add a DbTarget to log to database
            // or other targets as needed
            ],
        ],
        'errorHandler' => [
            'class' => 'yii\web\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'assetManager' => $assetManager,
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'xx', // if set to 'en', il8n uses keys, not translated values
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/guest' => 'guest.php',
                        'app/lobby' => 'lobby.php',
                        'app/error' => 'error.php',
                    ],
                ],
                'game*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'xx',
                    'fileMap' => [
                        'game' => 'game.php',
                    ],
                ],
            ],
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
    /*
     * 'urlManager' => [
     * 'enablePrettyUrl' => true,
     * 'showScriptName' => false,
     * 'rules' => [
     * '<controller:\w+>' => '<controller>',
     * '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
     * '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>/<id>',
     * ],
     * ],
     *
     */
    ],
    'params' => $params,
];
