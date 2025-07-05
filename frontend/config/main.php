<?php

$params = array_merge(
        require __DIR__ . '/../../common/config/params.php',
        require __DIR__ . '/../../common/config/params-local.php',
        require __DIR__ . '/params.php',
        require __DIR__ . '/params-local.php'
);

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
            'on afterLogin' => function ($event) {
                $user = $event->identity;
                $login_at = time();
                $user->frontend_last_login_at = $login_at;
                if ($user->save()) {
                    $log = new \common\models\UserLogin([
                        'user_id' => $user->id,
                        'application' => 'frontend',
                        'login_at' => $login_at,
                        'ip_address' => Yii::$app->getRequest()->getUserIP()
                    ]);
                    $log->save();
                }
            },
            'on afterLogout' => function ($event) {
                $user = $event->identity;
                $curlog = \common\models\UserLogin::findOne([
                    'user_id' => $user->id,
                    'application' => 'frontend',
                    'login_at' => $user->frontend_last_login_at
                ]);
                if ($curlog !== null) {
                    $curlog->logout_at = time();
                    $curlog->save();
                }
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
            'errorAction' => 'site/error',
        ],
        'assetManager' => [
            'bundles' => [
                'yii\\bootstrap5\\BootstrapAsset' => [
                    'sourcePath' => null, // do not publish the bundle from default path
                    'css' => [
                        'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.7/css/bootstrap.min.css',
                        'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css',
                        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
                    ],
                    'js' => [
                        'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.7/js/bootstrap.bundle.min.js',
                        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js'
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
                    ]
                // Optional: Add integrity and crossorigin attributes if needed
                // 'jsOptions' => [
                //    'integrity' => 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=',
                //    'crossorigin' => 'anonymous',
                // ],
                ],
            ],
        ],
    /*
      'urlManager' => [
      'enablePrettyUrl' => true,
      'showScriptName' => false,
      'rules' => [
      '<controller:\w+>' => '<controller>',
      '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
      '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>/<id>',
      ],
      ],
     *
     */
    ],
    'params' => $params,
];
