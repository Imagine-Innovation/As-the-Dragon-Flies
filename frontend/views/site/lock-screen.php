<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use frontend\helpers\Caligraphy;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Session Locked';
?>
<section class="vh-100">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card" style="border-radius: 2rem; background-color: rgba(20,20,20,.85);">
                    <div class="card-body p-5 text-center">

                        <div class="mb-md-5 mt-md-4 pb-5">

                            <h2 class="fw-bold mb-2 text-uppercase">
                                <img src="<?= Url::to('@web/img/Dragonfly32White.png') ?>" alt="Logo">
                                Session Locked
                            </h2>
                            <p class="text-white-50 mb-5">
                                <?php if ($model->username): ?>
                                    Welcome back, <?= Html::encode($model->username) ?>!
                                <?php else: ?>
                                    Your session has timed out.
                                <?php endif; ?>
                                <br>Please enter your password to unlock.
                            </p>

                            <?php $form = ActiveForm::begin([
                                'id' => 'lock-screen-form',
                                'action' => Url::to(['site/unlock-session']),
                            ]); ?>

                            <?php if ($model->username): ?>
                                <?= $form->field($model, 'username')->hiddenInput()->label(false); ?>
                                <div class="form-outline form-white mb-4">
                                   <input type="text" id="staticUsername" class="form-control form-control-lg" value="<?= Html::encode($model->username) ?>" readonly disabled />
                                   <label class="form-label" for="staticUsername">Username</label>
                                </div>
                            <?php else: ?>
                                <?= $form->field($model, 'username', [
                                    'template' => "<div class='form-outline form-white mb-4'>{input}
{label}
{error}</div>",
                                    'inputOptions' => [
                                        'autofocus' => 'autofocus',
                                        'class' => 'form-control form-control-lg',
                                        'autocomplete' => 'username',
                                    ],
                                    'labelOptions' => ['class' => 'form-label']
                                ])->textInput(['placeholder' => 'Username']) ?>
                            <?php endif; ?>

                            <?= $form->field($model, 'password', [
                                'template' => "<div class='form-outline form-white mb-4'>{input}
{label}
{error}</div>",
                                'inputOptions' => [
                                    'class' => 'form-control form-control-lg',
                                    'autocomplete' => 'current-password',
                                    'placeholder' => 'Password'
                                ],
                                'labelOptions' => ['class' => 'form-label']
                            ])->passwordInput() ?>

                            <?= $form->field($model, 'rememberMe')->hiddenInput(['value' => '0'])->label(false) ?>

                            <button class="btn btn-outline-light btn-lg px-5 mt-3" type="submit" name="unlock-button">Unlock</button>

                            <?php ActiveForm::end(); ?>

                        </div>

                        <div>
                            <p class="mb-0">Not you? <a href="<?= Url::to(['site/login']) ?>" class="text-white-50 fw-bold">Login as different user</a></p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
