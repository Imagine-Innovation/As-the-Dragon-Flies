<?php
if (Yii::$app->user->isGuest) {
    // specific jquery initialization for a guest
    echo "";
}

$controllerId = Yii::$app->controller->id;
$actionId = Yii::$app->controller->action->id;
$route = "{$controllerId}/{$actionId}";
?>
<?php if ($route === "player-cart/shop"): ?>
    <script src="js/atdf-shop-manager.js"></script>
<?php endif; ?>

<script type="text/javascript">
    var currentPlayerId = <?= Yii::$app->session->get('playerId') ?? 'null' ?>;
    PlayerSelector.initializeFromDOM();
    LayoutInitializer.initNavbarLobby();

    if (DOMUtils.exists('#ajaxHiddenParams')) {
        LayoutInitializer.initAjaxPage();
    }

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
<?php elseif ($route === "player-cart/cart"): ?>
        $(document).ready(function () {
            ShopManager.getCartInfo();
        });
<?php elseif ($route === "player-cart/shop"): ?>
        $(document).ready(function () {
            ShopManager.getCartInfo();
        });
<?php elseif ($controllerId === "quest"): ?>
    <?php
    $player = Yii::$app->session->get('currentPlayer');
    $playerId = $player->id;
    $playerName = $player->name;
    $avatar = $player->image->file_name;
    $quest = Yii::$app->session->get('currentQuest');
    $questName = $quest->story->name;
    ?>
        $(document).ready(function () {
            // Create and initialize the notification client instance
            const currentHost = window.location.hostname;
            const url = `ws://${currentHost}:8082`;
            const playerId = <?= $playerId ?>;
            const avatar = `<?= $avatar ?>`;
            const playerName = `<?= $playerName ?>`;
            const questId = <?= $quest->id ?>;
            const questName = `<?= $questName ?>`;
            const chatInput = `questChatInput`;

            console.log(`NotificationClient(url=${url}, playerId=${playerId}, avatar=${avatar}, questId=${questId}, playerName=${playerName}, questName=${questName}, chatInput=${chatInput}`);
            const notificationClient = new NotificationClient(url, playerId, avatar, questId, playerName, questName, chatInput);

            notificationClient.init();

            let config = {
                route: 'quest/ajax-tavern',
                method: 'GET',
                placeholder: 'questTavernPlayersContainer',
                badge: false
            };
            notificationClient.executeRequest(config, '');
        });
<?php endif; ?>

</script>

</script>
