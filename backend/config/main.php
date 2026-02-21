<?php
$params = array_merge(
        require __DIR__ . '/../../common/config/params.php',
        require __DIR__ . '/../../common/config/params-local.php',
        require __DIR__ . '/params.php',
        require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'name' => 'Admin console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'backend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'eventHandler' => [
            'class' => 'common\extensions\EventHandler\EventHandler',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/app.log',
                ],
            ],
        ],
        'errorHandler' => [
            'class' => 'yii\web\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'assetManager' => [
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
      'urlManager' => [
      'enablePrettyUrl' => true,
      'showScriptName' => false,
      'rules' => [
      ],
      ],
     */
    ],
    'params' => $params,
];
