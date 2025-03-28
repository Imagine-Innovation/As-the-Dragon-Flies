<?php
/** @var \yii\web\View $this */

/** @var string $content */
use frontend\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);

$webRoot = Url::base();
?>
<?php $this->beginPage() ?>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="img/favicon.png" rel="icon">
    <meta content="<?= Yii::$app->request->scriptUrl ?>" name="script-url">

    <?= Html::csrfMetaTags() ?>
    <?= $this->registerCsrfMetaTags() ?>

    <title><?= Html::encode($this->title) ?></title>

    <meta name="ajax-root-url" content="<?= $webRoot ?>">

    <link rel="stylesheet" href="css/bootstrap532.min.css">
    <link rel="stylesheet" href="css/bootstrap-icons.css">
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="css/dnd-icons.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/dragon.css">

    <script src="js/atdf-core-library.js"></script>
</head>
