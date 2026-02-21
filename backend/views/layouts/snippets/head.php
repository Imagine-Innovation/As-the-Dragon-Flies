<?php

/** @var \yii\web\View $this */
use yii\bootstrap5\Html;
use yii\helpers\Url;
?>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="favicon.png" rel="icon">
    <meta content="<?= Yii::$app->request->scriptUrl ?>" name="script-url">

    <?= $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>

    <?php $this->head() ?>
</head>
