<?php

use common\components\AppStatus;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Outcome $model */
/** @var yii\widgets\ActiveForm $form */
/** @var string $storyId */
/** @var string $chapterId */
/** @var string $missionId */
/** @var string $parentId */
?>

<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath">story/<?= $storyId ?></span>
    <span id="hiddenFormName">outcome</span>
    <span id="hiddenParentId"><?= $chapterId ?></span>
</div>

<?php $form = ActiveForm::begin(); ?>

<?=
        $form->field($model, 'status')
        ->radioList(AppStatus::getActionStatus())
        ->label('Select a status')
?>
<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
<?= $form->field($model, 'hp_loss') ?>
<?= $form->field($model, 'gained_gp') ?>
<?= $form->field($model, 'gained_xp') ?>

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
        ->label('Item gained')
?>

<?=
        $form->field($model, 'next_mission_id')
        ->dropdownList(
                $model->next_mission_id ? [$model->next_mission_id => $model->nextMission->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a mission",
                ]
        )
        ->label('Next mission')
?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
