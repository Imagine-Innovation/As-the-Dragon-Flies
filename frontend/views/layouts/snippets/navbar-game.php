<?php

use common\helpers\WebResourcesHelper;
use yii\helpers\Url;

/** @var \yii\web\View $this */
$imgPath = WebResourcesHelper::imagePath();
$currentUser = Yii::$app->user->identity;
$questName = Yii::$app->session->get('questName');
?>
<header class="header d-none d-md-flex">

    <div class="logo d-none d-md-inline-flex">
        <a href="<?= Url::toRoute(['site/index']) ?>">
            <h5 class="text-decoration">
                <img src="<?= $imgPath ?>/Dragonfly32White.png" alt=""> <?= $questName ?>
            </h5>
        </a>
    </div>

    <ul class="top-nav">
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
