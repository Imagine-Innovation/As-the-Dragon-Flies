<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Quest $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="quest-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'story_id')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'started_at')->textInput() ?>

    <?= $form->field($model, 'local_time')->textInput() ?>

    <?= $form->field($model, 'elapsed_time')->textInput() ?>

    <?= $form->field($model, 'last_notification_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
