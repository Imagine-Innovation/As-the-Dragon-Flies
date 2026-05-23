<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var common\models\User $model */

$this->title = Yii::t('app', 'Profile');
?>
<div class="user-profile">
    <div class="row d-flex justify-content-center">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-yellow text-decoration"><?= Html::encode($this->title) ?></h3>

                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'username')->textInput(['disabled' => true]) ?>
                    <?= $form->field($model, 'fullname')->textInput() ?>
                    <?= $form->field($model, 'email')->textInput(['disabled' => true]) ?>

                    <?= $form->field($model, 'language')->dropDownList([
                        'en' => 'English',
                        'fr' => 'Français',
                    ]) ?>

                    <div class="form-group d-flex justify-content-end mt-3">
                        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary text-decoration']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
