<?php
/** @var \yii\web\View $this */
/** @var string $controllerId */
/** @var string $actionId */
$sessionId = Yii::$app->session->get('sessionId');
$playerId = Yii::$app->session->get('playerId');
$playerName = Yii::$app->session->get('playerName');
$avatar = Yii::$app->session->get('avatar');
$questId = Yii::$app->session->get('questId');
$questName = Yii::$app->session->get('questName');
?>
// Create and initialize the notification client instance
const currentHost = window.location.hostname;
const url = `ws://${currentHost}:8082`;
const sessionId = `<?= $sessionId ?>`;
const playerId = <?= $playerId ?>;
const avatar = `<?= $avatar ?>`;
const playerName = `<?= $playerName ?>`;
const questId = <?= $questId ?>;
const questName = `<?= $questName ?>`;

const notificationClient = new NotificationClient(url, sessionId, playerId, playerName, avatar, questId, questName);

notificationClient.init();

<?php
/**
 * Game specific local script
 */
if ($controllerId === "game"):
    ?>
    const equipmentHandler = new EquipmentHandler();
    const svg = document.getElementById('equipmentSvg');
    equipmentHandler.init(playerId, svg);

    const vtt = new VirtualTableTop();
    vtt.init(questId);
<?php endif; ?>
