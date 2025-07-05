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
            <script type="text/javascript">
                console.log('loading main layout');
            </script>
        <?php endif; ?>

        <?php $this->endBody(); ?>
    </body>


    <?php if (!Yii::$app->user->isGuest): ?>
        <script type="text/javascript">
            var currentPlayerId = <?= Yii::$app->session->get('playerId') ?? 'null' ?>;
            PlayerSelector.initializeFromDOM();
            LayoutInitializer.initNavbarLobby();
            LayoutInitializer.initAjaxPage();
        </script>
    <?php endif; ?>

</html>
<?php $this->endPage() ?>
