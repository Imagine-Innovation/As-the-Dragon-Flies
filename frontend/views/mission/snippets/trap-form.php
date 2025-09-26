<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Trap $model */
/** @var yii\widgets\ActiveForm $form */
$damageType = common\models\DamageType::find()
        ->select(['name'])
        ->indexBy('id')
        ->column();
?>

<?php $form = ActiveForm::begin(); ?>

<?php // $form->field($model, 'damage_type_id')->textInput()  ?>
<?= $form->field($model, 'damage_type_id')->dropdownList($damageType, ['prompt' => 'Select a damage type']) ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

<?= $form->field($model, 'image')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'damage')->textInput(['maxlength' => true]) ?>

<?php // $form->field($model, 'is_team_trap')->textInput()  ?>
<?=
$form->field($model, 'is_team_trap')->radioList([
    0 => 'Only the player is trapped',
    1 => 'The whole team is trapped'
])
?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

