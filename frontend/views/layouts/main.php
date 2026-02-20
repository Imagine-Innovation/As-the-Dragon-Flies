<?php
/** @var \yii\web\View $this */

/** @var string $content */
use frontend\assets\AppAsset;

AppAsset::register($this);

$user = Yii::$app->user->identity;
$snippet = Yii::$app->user->isGuest ? 'guest' : 'lobby';
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="dark">

    <?= $this->renderFile('@app/views/layouts/snippets/head.php') ?>

    <body>
        <?php $this->beginBody(); ?>

        <main role="main" class="main">
            <?= $this->renderFile("@app/views/layouts/contents/$snippet.php", ['content' => $content]) ?>
        </main>

        <?= $this->renderFile('@app/views/layouts/snippets/footer.php') ?>

        <?php $this->endBody(); ?>
        <?= $this->renderFile('@app/views/layouts/snippets/javascript.php') ?>
    </body>
</html>
<?php $this->endPage() ?>
