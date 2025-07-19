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
    <?php if (1 === 2): ?>)
        <link href="/frontend/web/css/bootstrap532.min.css" rel="stylesheet">
        <link href="/frontend/web/css/bootstrap-icons.css" rel="stylesheet">
    <?php endif; ?>
    <?php $this->head() ?>
</head>
