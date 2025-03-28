<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\AccessRight $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="access-right-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'route')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'action')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_admin')->textInput() ?>

    <?= $form->field($model, 'is_designer')->textInput() ?>

    <?= $form->field($model, 'is_player')->textInput() ?>

    <?= $form->field($model, 'has_player')->textInput() ?>

    <?= $form->field($model, 'in_quest')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
