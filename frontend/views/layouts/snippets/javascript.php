<?php
/** @var \yii\web\View $this */
// Specific javascript libraries to load according to controller name
$controllerCustomJavascriptLib = [
    'player-builder' => ['atdf-player-builder', 'atdf-chart-drawer'],
    'player-cart' => ['atdf-shop-manager'],
    'quest' => ['atdf-quest-tavern', 'atdf-quest-events'],
    'game' => ['atdf-quest-game', 'atdf-quest-events', 'atdf-equipment-manager'],
    'item' => ['atdf-item-manager'],
    'player-item' => ['atdf-item-manager'],
    'image' => ['atdf-image-manager'],
    'mission' => ['atdf-search-select'],
];

$controllerId = Yii::$app->controller->id;
$actionId = Yii::$app->controller->action->id;
$route = "{$controllerId}/{$actionId}";

if (array_key_exists($controllerId, $controllerCustomJavascriptLib)) {
    $javascriptLibraries = $controllerCustomJavascriptLib[$controllerId];

    foreach ($javascriptLibraries as $javascriptLibrary) {
        echo('<script src="js/' . $javascriptLibrary . '.js"></script>' . PHP_EOL);
    }
}

$jsSnippet = match ($controllerId) {
    'player-builder' => 'player-builder',
    'player-cart' => 'player-cart',
    'quest' => 'quest',
    'game' => 'quest',
    default => null,
};
?>

<script type="text/javascript">
    var currentPlayerId = <?= Yii::$app->session->get('playerId') ?? 'null' ?>;
    PlayerSelector.initializeFromDOM();
    LayoutInitializer.initNavbarLobby();
    ActionButtonManager.initActionButton();

    if (DOMUtils.exists('#ajaxHiddenParams')) {
        LayoutInitializer.initAjaxPage();
    }


<?= $jsSnippet ? $this->renderFile("@app/views/layouts/snippets/js/{$jsSnippet}.php", ['controllerId' => $controllerId, 'actionId' => $actionId]) : "" ?>

</script>
