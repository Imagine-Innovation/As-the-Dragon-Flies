<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\MissionItem $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

<?=
        $form->field($model, 'item_id')
        ->dropdownList(
                $model->item_id ? [$model->item_id => $model->item->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select an item present during the mission",
                ]
        )
        ->label('Item present during the mission')
?>

<?= $form->field($model, 'image')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'found')->textInput() ?>

<?= $form->field($model, 'identified')->textInput() ?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
