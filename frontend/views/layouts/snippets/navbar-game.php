<?php

use frontend\assets\AppAsset;
use yii\helpers\Url;
use frontend\widgets\ToolMenu;

/** @var \yii\web\View $this */
$currentUser = Yii::$app->user->identity;
$questName = Yii::$app->session->get('questName');
AppAsset::register($this);
?>
<header class="header d-none d-md-flex">

    <div class="logo d-none d-md-inline-flex">
        <a href="<?= Url::toRoute(['site/index']) ?>">
            <h5 class="text-decoration">
                <img src="img/Dragonfly32White.png" alt=""> <?= $questName ?>
            </h5>
        </a>
    </div>

    <ul class="top-nav">
        <?= ToolMenu::widget() ?>

        <li class="dropdown top-nav__notifications">
            <i class="bi bi-circle-fill blink" id="eventHandlerStatus"></i>
        </li>
        <li class="dropdown top-nav__notifications">
            <a href="<?= Url::toRoute(['site/index']) ?>" data-bs-toggle="tooltip" title="Back to lobby" data-placement="bottom">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </li>
    </ul>
</header>
