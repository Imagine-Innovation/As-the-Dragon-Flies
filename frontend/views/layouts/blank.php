<?php

/** @var \yii\web\View $this */

/** @var string $content */
use common\widgets\Alert;
use frontend\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="dark">

    <?= $this->renderFile('@app/views/layouts/snippets/head.php') ?>

    <body style="background-image: url('img/carousel/car<?= random_int(1, 27) ?>.jpg');background-size: cover;background-position: center;background-repeat: no-repeat;">

        <?php $this->beginBody() ?>

        <main role="main" class="main">
            <div class="container">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </main>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
