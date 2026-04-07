<?php
/** @var \yii\web\View $this */
$controllerId = Yii::$app->controller->id;
$actionId = Yii::$app->controller->action->id;

$javascriptLibraries = match ($controllerId) {
    'player-builder' => ['atdf-player-builder', 'atdf-chart-drawer'],
    'player-cart' => ['atdf-shop-manager'],
    'quest' => ['atdf-quest-tavern', 'atdf-quest-events'],
    'game' => ['atdf-quest-game', 'atdf-quest-events', 'atdf-equipment-manager'],
    'item' => ['atdf-item-manager'],
    'player-item' => ['atdf-player-item-manager'],
    'image' => ['atdf-image-manager'],
    'mission' => ['atdf-search-select'],
    default => []
};

$jsSnippet = match ($controllerId) {
    'player-builder' => 'player-builder',
    'player-cart' => 'player-cart',
    'quest' => 'quest',
    'game' => 'quest',
    default => null,
};
?>

<?php foreach ($javascriptLibraries as $javascriptLibrary): ?>
    <script src="js/<?= $javascriptLibrary ?>.js"></script>
<?php endforeach; ?>

<script type="text/javascript">
    var currentPlayerId = <?= Yii::$app->session->get('playerId') ?? 'null' ?>;
    PlayerSelector.initializeFromDOM();
    LayoutInitializer.initNavbarLobby();

    if (DOMUtils.exists('#ajaxHiddenParams')) {
        LayoutInitializer.initAjaxPage();
    }

<?=
$jsSnippet ? $this->renderFile("@app/views/layouts/snippets/js/{$jsSnippet}.php", [
            'controllerId' => $controllerId,
            'actionId' => $actionId,
        ]) : ''
?>
</script>
