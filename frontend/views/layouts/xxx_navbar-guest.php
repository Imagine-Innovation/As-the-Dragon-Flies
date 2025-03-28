<?php

use frontend\helpers\Caligraphy;
use yii\helpers\Url;

/** @var \yii\web\View $this */
$currentUser = Yii::$app->user->identity;
?>
<?php if (false): ?>
    <header class="header">

        <div class="logo d-none d-sm-inline-flex">
            <a href="<?= Url::toRoute(['site/index']) ?>">
                <img src="img/Dragonfly32White.png" alt="Logo">
                <?= Caligraphy::appName() ?>
            </a>
        </div>
        <ul class="top-nav">
            <li class="dropdown top-nav__notifications">
                <a href="<?= Url::toRoute(['site/about']) ?>"
                   data-toggle="tooltip"
                   title="About us"
                   data-placement="bottom">
                    <i class="bi bi-info-square"></i>
                </a>
            </li>
            <li class="dropdown top-nav__notifications">
                <a href="<?= Url::toRoute(['site/contact']) ?>"
                   data-toggle="tooltip"
                   title="Contact us"
                   data-placement="bottom">
                    <i class="bi bi-pencil-square"></i>
                </a>
            </li>
            <li class="dropdown top-nav__notifications">
                <a class="col-4" href="<?= Url::toRoute(['site/login']) ?>"
                   data-toggle="tooltip"
                   title="Enter the game"
                   data-placement="bottom">
                    <i class="bi bi-box-arrow-in-right"></i>
                </a>
            </li>
        </ul>
    </header>
<?php endif; ?>
