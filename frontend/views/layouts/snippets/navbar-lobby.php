<?php

use frontend\assets\AppAsset;
use frontend\helpers\Caligraphy;
use frontend\widgets\Button;
use frontend\widgets\CurrentPlayer;
use frontend\widgets\ToolMenu;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var \yii\web\View $this */
$currentUser = Yii::$app->user->identity;
AppAsset::register($this);
$isAdmin = $currentUser->is_admin;
?>
<header class="header">

    <div class="logo d-none d-md-inline-flex">
        <a href="<?= Url::toRoute(['site/index']) ?>">
            <img src="img/Dragonfly32White.png" alt="">
            <?= Caligraphy::appName() ?>
            (<?= $currentUser->username ?>)
        </a>
    </div>

    <ul class="top-nav">
        <?=
    CurrentPlayer::widget([
        'user' => $currentUser,
        'mode' => 'navbar',
    ])
?>

        <li class="dropdown top-nav__notifications">
            <a class="top-nav position-relative" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-envelope"></i>
                <!--
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    1
                </span>
                -->
            </a>


            <div class="dropdown-menu dropdown-menu-right dropdown-menu--block">
                <div class="dropdown-header">
                    Messages

                    <div class="actions">
                        <a href="<?= Url::toRoute(['site/index']) ?>" role="button" class="actions__item bi-envelope"></a>
                    </div>
                </div>

                <div class="listview listview--hover">
                    <!--
                    <a href="#" class="listview__item">
                        <img src="demo/img/profile-pics/1.jpg" class="avatar-img" alt="">

                        <div class="listview__content">
                            <div class="listview__heading">
                                David Belle <small>12:01 PM</small>
                            </div>
                            <p>Cum sociis natoque penatibus et magnis dis parturient montes</p>
                        </div>
                    </a>

                    <a href="#" class="listview__item">
                        <img src="demo/img/profile-pics/2.jpg" class="avatar-img" alt="">

                        <div class="listview__content">
                            <div class="listview__heading">
                                Jonathan Morris
                                <small>02:45 PM</small>
                            </div>
                            <p>Nunc quis diam diamurabitur at dolor elementum, dictum turpis vel</p>
                        </div>
                    </a>
                    -->
                    <?= Html::a('View all messages', ['site/index'], ['class' => 'view-more']) ?>
                </div>
            </div>
        </li>

        <li id="notificationDropdown" class="dropdown top-nav__notifications">
            <a href="#" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span id="notificationCounter" class="d-none position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-right dropdown-menu--block">
                <div class="dropdown-header">
                    Notifications

                    <div class="actions">
                        <a href="<?= Url::toRoute(['site/index']) ?>" role="button" class="actions__item bi-bell" data-sa-action="notifications-clear"></a>
                    </div>
                </div>

                <div class="listview listview--hover">
                    <div id="notificationList" class="listview__scroll scrollbar">
                    </div>
                    <div class="p-1"></div>
                </div>
            </div>
        </li>

        <?= ToolMenu::widget(['isAdmin' => $isAdmin]) ?>

        <li class="dropdown top-nav__notifications">
            <a href="<?= Url::toRoute(['site/about']) ?>"
               data-bs-toggle="tooltip" title="<?= $currentUser->username ?> user profile" data-placement="bottom">
                <i class="bi bi-person-circle"></i>
            </a>
        </li>
        <li class="dropdown top-nav__notifications">
            <?=
    Button::widget([
        'isPost' => true,
        'url' => Url::toRoute(['site/logout']),
        'icon' => 'dnd-power-off',
        'tooltip' => 'logout',
    ])
?>
        </li>
    </ul>
</header>
