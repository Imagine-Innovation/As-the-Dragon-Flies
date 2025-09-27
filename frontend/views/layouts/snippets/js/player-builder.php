<?php
/** @var \yii\web\View $this */
/** @var string $controllerId */
/** @var string $actionId */
?>

<?php if ($actionId === "create"): ?>
    PlayerBuilder.initCreatePage();
<?php elseif ($actionId === "update"): ?>
    PlayerBuilder.initUpdatePage();

    const gender = $('#playerbuilder-gender').val();
    const alignmentId = $('#playerbuilder-alignment_id').val();
    const age = $('#playerbuilder-age').val();

    PlayerBuilder.initDescriptionTab(gender, alignmentId, age);
    PlayerBuilder.initAbilitiesTab();
    PlayerBuilder.initAvatarTab();
    PlayerBuilder.initSkillsTab();
<?php endif; ?>
