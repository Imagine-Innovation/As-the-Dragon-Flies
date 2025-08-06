<?php

/* @var $this yii\web\View */

use yii\bootstrap5\Html;
use yii\helpers\Url;

if (Yii::$app->user->isGuest) {
    $this->title = Html::encode(Yii::$app->name);
    $render = $this->renderFile('@app/views/site/snippets/guest.php');
} else {

    $this->title = 'Game lobby';

    $user = Yii::$app->user->identity;
    $userName = $user->fullname ?? $user->username;
    //$playerName = $user->currentPlayer ? $user->currentPlayer->name : $userName;
    $playerName = Yii::$app->session->get('playerName') ?? $userName;

    $render = $this->renderFile('@app/views/site/snippets/lobby.php', [
        'userName' => $userName,
        'playerName' => $playerName,
    ]);
}
echo $render;
