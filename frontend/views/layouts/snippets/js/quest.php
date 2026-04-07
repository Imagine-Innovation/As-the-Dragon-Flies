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
// Create and initialize the notification client instance for r=<?= $controllerId ?>/<?= $actionId ?>.
const currentHost = window.location.hostname;
const url = `ws://${currentHost}:8082`;
const sessionId = `<?= $sessionId ?>`;
const playerId = <?= $playerId ?>;
const avatar = `<?= $avatar ?>`;
const playerName = `<?= $playerName ?>`;
const questId = <?= $questId ?>;
const questName = `<?= $questName ?>`;

const vtt = new VirtualTableTop(); // defined in "atdf-quest-<?= $controllerId === 'game' ? 'game' : 'tavern' ?>.js"
vtt.init();

const notificationClient = new NotificationClient(url, sessionId, playerId, playerName, avatar, questId, questName, vtt);

notificationClient.init();

<?php if ($controllerId === 'game'): ?>
    const equipmentHandler = new EquipmentHandler();
    const svg = document.getElementById('equipmentSvg');
    equipmentHandler.init(playerId, svg);
<?php endif; ?>
