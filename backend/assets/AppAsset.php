<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '/common/web/css/icons.css',
        '/common/web/css/dragon.css',
        'css/site.css',
    ];
    public $js = [
        '/common/web/js/core-library.js',
        'js/kpi.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
