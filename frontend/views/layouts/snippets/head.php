<?php
/** @var \yii\web\View $this */

/** @var string $content */
use yii\bootstrap5\Html;
use yii\helpers\Url;

$webRoot = Url::base();
?>
<?php $this->beginPage() ?>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="img/favicon.png" rel="icon">
    <meta content="<?= Yii::$app->request->scriptUrl ?>" name="script-url">

    <?= $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>

    <meta name="ajax-root-url" content="<?= $webRoot ?>">
    <?php $this->head() ?>
</head>
