<?php
/** @var \yii\web\View $this */
$controllerId = Yii::$app->controller->id;
$actionId = Yii::$app->controller->action->id;

$javascriptLibraries = match ($controllerId) {
    'player-builder' => ['player-builder', 'chart-drawer'],
    'player-cart' => ['shop-manager'],
    'quest' => ['quest-tavern', 'quest-events'],
    'game' => ['quest-game', 'quest-events', 'equipment-manager'],
    'player-item' => ['player-item-manager'],
    default => []
};

$jsSnippet = match ($controllerId) {
    'player-builder' => 'player-builder',
    'player-cart' => 'player-cart',
    'quest' => 'quest',
    'game' => 'quest',
    default => null
};
?>

<?php foreach ($javascriptLibraries as $javascriptLibrary): ?>
    <script src="js/<?= $javascriptLibrary ?>.js"></script>
<?php endforeach; ?>

<script type="text/javascript">
    var currentPlayerId = <?= Yii::$app->session->get('playerId') ?? 'null' ?>;

    $(document).ready(function () {
        PlayerSelector.initializeFromDOM();
        LayoutInitializer.initNavbarLobby();

        if (DOMUtils.exists('#ajaxHiddenParams')) {
            LayoutInitializer.initAjaxPage();
        }
    });

<?=
$jsSnippet ? $this->renderFile("@app/views/layouts/snippets/js/{$jsSnippet}.php", [
            'controllerId' => $controllerId,
            'actionId' => $actionId,
        ]) : ''
?>
</script>
