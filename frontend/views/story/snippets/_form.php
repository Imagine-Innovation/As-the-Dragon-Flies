<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Story $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="story-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'image')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'min_level')->textInput() ?>

    <?= $form->field($model, 'max_level')->textInput() ?>

    <?= $form->field($model, 'min_players')->textInput() ?>

    <?= $form->field($model, 'max_players')->textInput() ?>

    <?= $form->field($model, 'language')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
