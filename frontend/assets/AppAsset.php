<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '/common/web/css/icons.css',
        '/common/web/css/fonts.css',
        '/common/web/css/dragon.css',
    ];
    public $js = [
        'js/atdf-core-library.js',
        'js/atdf-action-button-manager.js',
    ];
    public $depends = [
        'yii\web\YiiAsset', // Provides jQuery
        'yii\bootstrap5\BootstrapAsset', // Provides Bootstrap CSS/JS
    ];
}
