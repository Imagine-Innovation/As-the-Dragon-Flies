<?php
// Specific javascript libraries to load according to controller name
$controllerCustomJavascriptLib = [
    'player-builder' => ['atdf-player-builder', 'atdf-chart-drawer'],
    'player-cart' => ['atdf-shop-manager'],
    'quest' => ['atdf-quest-events'],
    'game' => ['atdf-quest-events'],
    'item' => ['atdf-item-manager'],
    'player-item' => ['atdf-item-manager'],
    'image' => ['atdf-image-manager'],
];

$controllerId = Yii::$app->controller->id;
$actionId = Yii::$app->controller->action->id;
$route = "{$controllerId}/{$actionId}";

if (array_key_exists($controllerId, $controllerCustomJavascriptLib)) {
    $javascriptLibraries = $controllerCustomJavascriptLib[$controllerId];

    foreach ($javascriptLibraries as $javascriptLibrary) {
        echo('<script src="js/' . $javascriptLibrary . '.js"></script>');
    }
}
?>

<script type="text/javascript">
    var currentPlayerId = <?= Yii::$app->session->get('playerId') ?? 'null' ?>;
    PlayerSelector.initializeFromDOM();
    LayoutInitializer.initNavbarLobby();

    if (DOMUtils.exists('#ajaxHiddenParams')) {
        LayoutInitializer.initAjaxPage();
    }

<?php
/**
 * Player-builder specific local script
 */
if ($controllerId === "player-builder"):
    ?>
    <?php if ($route === "player-builder/create"): ?>
            PlayerBuilder.initCreatePage();
    <?php elseif ($route === "player-builder/update"): ?>
            PlayerBuilder.initUpdatePage();

            const gender = $('#playerbuilder-gender').val();
            const alignmentId = $('#playerbuilder-alignment_id').val();
            const age = $('#playerbuilder-age').val();

            PlayerBuilder.initDescriptionTab(gender, alignmentId, age);
            PlayerBuilder.initAbilitiesTab();
            PlayerBuilder.initAvatarTab();
            PlayerBuilder.initSkillsTab();
    <?php endif; ?>
<?php endif; ?>
<?php
/**
 * Player-cart specific local script
 */
if ($controllerId === "player-cart"):
    ?>
        $(document).ready(function () {
            ShopManager.getCartInfo();
        });
<?php endif; ?>
<?php
/**
 * Quest specific local script
 */
if ($controllerId === "quest" || $controllerId === "game"):
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
<?php endif; ?>

</script>
