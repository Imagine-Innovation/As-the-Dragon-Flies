<?php

use common\helpers\WebResourcesHelper;
use frontend\helpers\Caligraphy;
use yii\helpers\Url;

/** @var \yii\web\View $this */
$imgPath = WebResourcesHelper::imagePath();

$languages = \common\components\LanguageSelector::SUPPORTED_LANGUAGES;
$currentLanguageLabel = $languages[Yii::$app->language] ?? ($languages[\common\components\LanguageSelector::DEFAULT_LANGUAGE] ?? 'English');
?>
<header class="header">

    <div class="logo d-none d-md-inline-flex">
        <a href="<?= Url::toRoute(['site/index']) ?>">
            <img src="<?= $imgPath ?>/Dragonfly32White.png" alt="">
            <?= Caligraphy::appName() ?>
        </a>
    </div>

    <ul class="top-nav">
        <li class="dropdown">
            <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle text-white d-flex align-items-center">
                <i class="bi bi-translate me-2"></i> <?= $currentLanguageLabel ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <?php foreach ($languages as $code => $label): ?>
                    <a href="<?= Url::toRoute(['site/language', 'lang' => $code]) ?>" class="dropdown-item"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </li>
    </ul>
</header>
