<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\PlayerBuilder $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="player-builder-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'level_id')->textInput() ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'race_id')->textInput() ?>

    <?= $form->field($model, 'class_id')->textInput() ?>

    <?= $form->field($model, 'alignment_id')->textInput() ?>

    <?= $form->field($model, 'background_id')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

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
    <div class="form-group field-playerbuilder-languages">
        <label class="control-label" for="playerbuilder-languages">Languages</label>
        <input type="text" id="playerbuilder-languages" class="form-control" name="dummy-languages" value="<?= $model->playerLanguages ? "ok" : '' ?>">
    </div>

    <div class="form-group field-playerbuilder-abilities">
        <label class="control-label" for="playerbuilder-abilities">Abilities</label>
        <input type="text" id="playerbuilder-abilities" class="form-control" name="dummy-abilities" value="<?= $model->playerAbilities ? "ok" : '' ?>">
    </div>

    <div class="form-group field-playerbuilder-skills">
        <label class="control-label" for="playerbuilder-skills">Skills</label>
        <input type="text" id="playerbuilder-skills" class="form-control" name="dummy-skills" value="<?= $model->playerSkills ? "ok" : '' ?>">
    </div>

    <div class="form-group field-playerbuilder-items">
        <label class="control-label" for="playerbuilder-items">Initial equipment</label>
        <!-- Forced to null when editing a player -->
        <input type="text" id="playerbuilder-items" class="form-control" name="dummy-items">
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'id' => 'save-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
