<?php

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
                    'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.8/css/bootstrap.min.css',
                    'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css',
                    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
                ],
                'js' => [
                    'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.8/js/bootstrap.bundle.min.js',
                    'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
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
                    'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js',
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
    'id' => 'app-frontend',
    'name' => 'As the Dragon Flies',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
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
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['websocket'],
                    'logFile' => '@runtime/logs/websocket.log',
                    'logVars' => [],
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
