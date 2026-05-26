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
