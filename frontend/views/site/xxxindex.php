<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

if (Yii::$app->user->isGuest) {
    $this->title = Html::encode(Yii::$app->name);
    $render = $this->renderFile('@app/views/site/snippets/guest.php');
} else {
    $this->title = 'Game lobby';
    $render = $this->renderFile('@app/views/site/snippets/lobby.php');
}
echo $render;
