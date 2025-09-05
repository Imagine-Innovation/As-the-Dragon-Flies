<?php
/** @var \yii\web\View $this */
/** @var string $controllerId */
/** @var string $actionId */
$route = "{$controllerId}/{$actionId}";
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
