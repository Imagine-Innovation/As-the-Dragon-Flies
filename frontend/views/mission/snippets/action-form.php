<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Action $model */
/** @var yii\widgets\ActiveForm $form */
/** @var string $storyId */
/** @var string $chapterId */
/** @var string $missionId */
?>
<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath">item</span>
    <span id="hiddenFormName">action</span>
    <span id="hiddenMissionId"><?= $missionId ?></span>
</div>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'icon')->textInput(['maxlength' => true])->label($model->icon ? 'Icon (<i class="bi ' . $model->icon . '"></i>)' : 'Icon') ?>

<?= $form->field($model, 'action_type')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'dc')->textInput() ?>

<?=
        $form->field($model, 'passage_id')
        ->dropdownList(
                $model->passage_id ? [$model->passage_id => $model->passage->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a passage",
                ]
        )
        ->label('Passage affected by the action')
?>
<?=
        $form->field($model, 'trap_id')
        ->dropdownList(
                $model->passage_id ? [$model->passage_id => $model->passage->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a trap",
                ]
        )
        ->label('Trap affected by the action')
?>
<?=
        $form->field($model, 'item_id')
        ->dropdownList(
                $model->item_id ? [$model->item_id => $model->item->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select an item",
                ]
        )
        ->label('Object affected by the action')
?>
<?=
        $form->field($model, 'decor_id')
        ->dropdownList(
                $model->decor_id ? [$model->decor_id => $model->decor->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a decor",
                ]
        )
        ->label('Decor affected by the action')
?>
<?=
        $form->field($model, 'npc_id')
        ->dropdownList(
                $model->npc_id ? [$model->npc_id => $model->npc->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a NPC",
                ]
        )
        ->label('NPC involved in the action')
?>
<?=
        $form->field($model, 'reply_id')
        ->dropdownList(
                $model->reply_id ? [$model->reply_id => $model->reply->text] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a reply",
                ]
        )
        ->label('First reply of the player')
?>
<?=
        $form->field($model, 'required_item_id')
        ->dropdownList(
                $model->item_id ? [$model->item_id => $model->item->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select an item",
                ]
        )
        ->label('Object required to carry out the action')
?>
<?=
        $form->field($model, 'skill_id')
        ->dropdownList(
                $model->item_id ? [$model->item_id => $model->item->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a skill",
                ]
        )
        ->label('skill required to carry out the action')
?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success bi-floppy']) ?>
</div>

<?php ActiveForm::end(); ?>
