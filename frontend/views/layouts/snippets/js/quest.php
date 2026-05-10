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
let notificationClient;
let equipmentHandler;
let vtt;
let sessionId;
let playerId;
let avatar;
let playerName;
let questId;
let questName;

$(document).ready(function () {
    const currentHost = window.location.hostname;
    const protocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
    const url = `${protocol}://${currentHost}:8082`;
    sessionId = <?= json_encode($sessionId) ?>;
    playerId = <?= json_encode($playerId) ?>;
    avatar = <?= json_encode($avatar) ?>;
    playerName = <?= json_encode($playerName) ?>;
    questId = <?= json_encode($questId) ?>;
    questName = <?= json_encode($questName) ?>;

    vtt = new VirtualTableTop(); // defined in "atdf-quest-<?= $controllerId === 'game' ? 'game' : 'tavern' ?>.js"
    vtt.init();

    notificationClient = new NotificationClient(url, sessionId, playerId, playerName, avatar, questId, questName, vtt);
    notificationClient.init();

<?php if ($controllerId === 'game'): ?>
    equipmentHandler = new EquipmentHandler();
    const svg = document.getElementById('equipmentSvg');
    equipmentHandler.init(playerId, svg);
<?php endif; ?>
});
