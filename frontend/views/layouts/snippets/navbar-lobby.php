<?php

use common\helpers\WebResourcesHelper;
use common\widgets\Button;
use frontend\assets\AppAsset;
use frontend\helpers\Caligraphy;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var \yii\web\View $this */
$imgPath = WebResourcesHelper::imagePath();
$currentUser = Yii::$app->user->identity;
AppAsset::register($this);

$languages = \common\components\LanguageSelector::SUPPORTED_LANGUAGES;
$currentLanguageLabel = $languages[Yii::$app->language] ?? ($languages[\common\components\LanguageSelector::DEFAULT_LANGUAGE]);
?>
<header class="header">

    <div class="logo d-none d-md-inline-flex">
        <a href="<?= Url::toRoute(['site/index']) ?>">
            <img src="<?= $imgPath ?>/Dragonfly32White.png" alt="">
            <?= Caligraphy::appName() ?>
            (<?= $currentUser->username ?>)
        </a>
    </div>

    <ul class="top-nav">
        <li class="dropdown">
            <a href="#" data-bs-toggle="dropdown" class="nav-link dropdown-toggle text-white d-flex align-items-center">
                <i class="bi bi-translate me-2"></i> <?= $currentLanguageLabel ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <?php foreach ($languages as $code => $label): ?>
                    <a href="#" class="dropdown-item" onclick="LanguageManager.setLanguage('<?= $code ?>'); return false;"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </li>
        <li class="dropdown top-nav__notifications">
            <a href="<?= Url::toRoute(['user/profile']) ?>"
               data-bs-toggle="tooltip" title="<?= Yii::t('app', '{username} user profile', ['username' => $currentUser->username]) ?>" data-placement="bottom">
                <i class="bi bi-person-circle"></i>
            </a>
        </li>
        <li class="dropdown top-nav__notifications">
            <?=
            Button::widget([
                'isPost' => true,
                'url' => Url::toRoute(['site/logout']),
                'icon' => 'dnd-power-off',
                'tooltip' => Yii::t('app', 'logout'),
            ])
            ?>
        </li>
    </ul>
</header>
