<?php

/* @var $this yii\web\View */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$webRoot = Url::base();
if (Yii::$app->user->isGuest) {
    $this->title = Html::encode(Yii::$app->name);
    $render = $this->renderFile('@app/views/site/_guest.php', ['webRoot' => $webRoot]);
} else {

    $this->title = 'Game lobby';

    $user = Yii::$app->user->identity;
    $userName = $user->fullname ? $user->fullname : $user->username;
    $playerName = $user->currentPlayer ? $user->currentPlayer->name : $userName;

    $render = $this->renderFile('@app/views/site/_lobby.php', [
        'userName' => $userName,
        'playerName' => $playerName,
        'webRoot' => $webRoot,
    ]);
}

echo $render;
