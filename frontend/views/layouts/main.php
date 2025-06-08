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
            <script src="js/atdf-quest-events.js"></script>
            <script type="text/javascript">
                // Initialize the broker
                /*
                 $(document).ready(function () {
                 NotificationHandler.init({
                 pollingInterval: 10000000,
                 userId: userId,
                 playerId: playerId,
                 questId: questId
                 });
                 });
                 *
                 */
                /*
                 document.addEventListener('DOMContentLoaded', function () {
                 // Configuration variables from your server-side template
                 const currentHost = window.location.hostname;
                 const websocketUrl = `ws://${currentHost}:8082`;
                 const playerId = <?= $user->current_player_id ?? 'null' ?>;
                 const questId = <?= Yii::$app->session->get('questId') ?? 'null' ?>;
                 // Create and initialize the notification client
                 var notificationClient;
                 if (!notificationClient) {
                 notificationClient = new NotificationClient(websocketUrl, playerId, questId).init();
                 }
                 });
                 */
                console.log('loading main layout');
            </script>
        <?php endif; ?>

    </body>
</html>
