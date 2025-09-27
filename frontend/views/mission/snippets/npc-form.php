<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Npc $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

<?=
        $form->field($model, 'npc_type_id')
        ->dropdownList(
                $model->npc_type_id ? [$model->npc_type_id => $model->npcType->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select the type of NPC",
                ]
        )
        ->label('NPC type')
?>

<?= $form->field($model, 'image')->textInput(['maxlength' => true]) ?>

<?php
if ($model->firstDialog) {
    echo $form->field($model, 'first_dialog_id')
            ->dropdownList(
                    $model->first_dialog_id ? [$model->first_dialog_id => $model->firstDialog->text] : [],
                    [
                        'class' => 'select2-container w-100',
                        'data-minimum-results-for-search' => -1,
                        'data-placeholder' => "Select the first dialog of the NPC",
                    ]
            )
            ->label('First dialog');
}
?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
