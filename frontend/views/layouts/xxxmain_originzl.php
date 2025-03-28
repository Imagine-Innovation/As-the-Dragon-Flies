<?php
/** @var \yii\web\View $this */

/** @var string $content */
use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);

$webRoot = Url::base();
$user = Yii::$app->user->identity;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="dark">
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
        <link rel="stylesheet" href="css/dragon.css">
        <!-- link rel="stylesheet" href="css/dnd.css" -->


        <!-- script src="js/dndtools.js"></script -->
        <!-- script src="js/notification.js"></script -->
        <!--script src="js/websocket.js"></script-->

        <script src="js/atdf-core-library.js"></script>
    </head>
    <body>
        <?php $this->beginBody(); ?>

        <main role="main" class="main">
            <!--Main Navigation-->
            <?= $this->renderFile('@app/views/layouts/_navbar.php', ['webRoot' => $webRoot]) ?>
            <!-- End Main Navigation-->

            <section class="content">
                <a id="top"></a>
                <?=
                Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ?
                            $this->params['breadcrumbs'] : [],
                ])
                ?>
                <div class="content__inner">
                    <?= Alert::widget() ?>

                    <?= $content ?>
                </div>
                <!-- Toast Markup -->
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                    <div class="toast-wrapper">
                        <div id="toastContainer"></div>
                    </div>
                </div>

                <footer class="footer d-none d-sm-block">
                    <ul class="footer__nav">
                        <a href="<?= Url::toRoute(['site/index']) ?>">Homepage</a>
                        <a href="<?= Url::toRoute(['site/about']) ?>">About</a>
                        <a href="#">Support</a>
                        <a href="#">News</a>
                        <a href="#">Contacts</a>
                    </ul>
                </footer>
            </section>
        </main>
        <script src="js/atdf-notification-handler.js"></script>
        <script type="text/javascript">
            // Initialize the broker
            const userId = <?= $user->id ?? 'null' ?>;
            const playerId = <?= $user->current_player_id ?? 'null' ?>;
            const questId = <?= Yii::$app->session->get('questId') ?? 'null' ?>;

            $(document).ready(function () {
                //$.notificationHandler.init({
                NotificationHandler.init({
                    pollingInterval: 1000000,
                    userId: userId,
                    playerId: playerId,
                    questId: questId
                });
            });
        </script>
    </body>
</html>
