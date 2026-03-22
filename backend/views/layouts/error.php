<?php

/** @var yii\web\View $this */
/** @var string $content */

use backend\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="dark">

    <?= $this->renderFile('@app/views/layouts/snippets/head.php') ?>

    <body>
        <?php $this->beginBody(); ?>

        <main role="main">
            <?= $content ?>
        </main>

        <?php $this->endBody(); ?>
    </body>
</html>
<?php $this->endPage() ?>
