<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/dev-icons.css',
        'css/dnd-icons.css',
        'css/fonts.css',
        'css/dragon.css',
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
