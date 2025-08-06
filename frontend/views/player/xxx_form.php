<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="player-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'level_id')->textInput() ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'race_id')->textInput() ?>

    <?= $form->field($model, 'class_id')->textInput() ?>

    <?= $form->field($model, 'alignment_id')->textInput() ?>

    <?= $form->field($model, 'background_id')->textInput() ?>

    <?= $form->field($model, 'history_id')->textInput() ?>

    <?= $form->field($model, 'image_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'gender')->dropDownList(['C' => 'C', 'F' => 'F', 'M' => 'M',], ['prompt' => '']) ?>

    <?= $form->field($model, 'age')->textInput() ?>

    <?= $form->field($model, 'experience_points')->textInput() ?>

    <?= $form->field($model, 'hit_points')->textInput() ?>

    <?= $form->field($model, 'max_hit_points')->textInput() ?>

    <?= $form->field($model, 'armor_class')->textInput() ?>

    <-- Custom fields -->
    <div class="form-group field-player-abilities">
        <label class="control-label" for="player-abilities">Abilities</label>
        <input type="text" id="player-abilities" class="form-control" name="abilities">
    </div>

    <div class="form-group field-player-skills">
        <label class="control-label" for="player-skills">Skills</label>
        <input type="text" id="player-skills" class="form-control" name="skills">
    </div>

    <div class="form-group field-player-items">
        <label class="control-label" for="player-items">Skills</label>
        <input type="text" id="player-items" class="form-control" name="items">
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'id' => 'save-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
