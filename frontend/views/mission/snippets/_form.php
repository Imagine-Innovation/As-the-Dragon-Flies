<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Mission $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="mission-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'chapter_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'low_bound')->textInput() ?>

    <?= $form->field($model, 'high_bound')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
