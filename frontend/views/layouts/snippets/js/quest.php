<?php
/** @var \yii\web\View $this */
/** @var string $controllerId */
/** @var string $actionId */
// Only load real-time gameplay components for active gaming/tavern routes.
// Other routes (like summary) return early with an empty JS response.
$activeRoutes = [
    'game/view',
    'quest/tavern',
];
$currentRoute = "$controllerId/$actionId";

if (!in_array($currentRoute, $activeRoutes)) {
    return;
}

$playerId = Yii::$app->session->get('playerId');
$playerName = Yii::$app->session->get('playerName');
$avatar = Yii::$app->session->get('avatar');
$questId = Yii::$app->session->get('questId');
$questName = Yii::$app->session->get('questName');

// A finished quest has no more QuestProgress in progress: the game page would
// render with an empty mission context. Send the browser to the summary instead.
if ($controllerId === 'game' && $questId) {
    $questStatus = \common\models\Quest::findOne($questId)->status ?? null;
    if (in_array($questStatus, [\common\components\AppStatus::COMPLETED->value, \common\components\AppStatus::ABORTED->value], true)) {
        echo "window.location.href = " . json_encode(\yii\helpers\Url::to(['quest/summarize', 'id' => $questId])) . ";";
        return;
    }
}
?>
// Create and initialize the notification client instance for r=<?= $controllerId ?>/<?= $actionId ?>.
let notificationClient;
let equipmentHandler;
let vtt;
let playerId;
let avatar;
let playerName;
let questId;
let questName;

$(document).ready(function () {
// Long polling endpoint: the wait loop lives server-side in QuestController,
// no external WebSocket daemon is needed anymore.
const url = 'quest/ajax-wait-events';
playerId = <?= json_encode($playerId) ?>;
avatar = <?= json_encode($avatar) ?>;
playerName = <?= json_encode($playerName) ?>;
questId = <?= json_encode($questId) ?>;
questName = <?= json_encode($questName) ?>;

vtt = new VirtualTableTop(); // defined in "quest-<?= $controllerId === 'game' ? 'game' : 'tavern' ?>.js"
vtt.init();

notificationClient = new NotificationClient(url, playerId, playerName, avatar, questId, questName, vtt);
notificationClient.init();

<?php if ($controllerId === 'game'): ?>
    equipmentHandler = new EquipmentHandler();
    const svg = document.getElementById('equipmentSvg');
    equipmentHandler.init(playerId, svg);
<?php endif; ?>
});
