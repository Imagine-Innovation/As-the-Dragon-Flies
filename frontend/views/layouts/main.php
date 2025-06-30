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

    <?= $this->renderFile('@app/views/layouts/_head.php') ?>

    <body>
        <?php $this->beginBody(); ?>

        <main role="main" class="main">
            <?= $this->renderFile("@app/views/layouts/_content-$snippet.php", ['content' => $content]) ?>
        </main>

        <?= $this->renderFile('@app/views/layouts/_footer.php') ?>

        <?php if (!Yii::$app->user->isGuest): ?>
            <!-- script src="js/atdf-notification-handler.js"></script -->
            <!-- script src="js/atdf-quest-events.js"></script -->
            <script type="text/javascript">
                console.log('loading main layout');
            </script>
        <?php endif; ?>

    </body>
</html>
