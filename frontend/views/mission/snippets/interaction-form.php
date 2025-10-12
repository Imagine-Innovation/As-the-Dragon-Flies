<?php

use common\components\AppStatus;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\ActionInteraction $model */
/** @var yii\widgets\ActiveForm $form */
/** @var string $storyId */
/** @var string $chapterId */
/** @var string $missionId */
/** @var string $parentId */
?>

<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath">story/<?= $storyId ?></span>
    <span id="hiddenFormName">actioninteraction</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

<?php $form = ActiveForm::begin(); ?>

<?=
        $form->field($model, 'previous_action_id')
        ->dropdownList(
                $model->previous_action_id ? [$model->previous_action_id => $model->previousAction->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select an action",
                ]
        )
        ->label('Previous action')
?>

<?=
        $form->field($model, 'next_action_id')
        ->dropdownList(
                $model->next_action_id ? [$model->next_action_id => $model->nextAction->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select an action",
                ]
        )
        ->label('Next action')
?>

<?=
        $form->field($model, 'status')
        ->radioList(AppStatus::getActionStatus())
        ->label('Select a status')
?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

