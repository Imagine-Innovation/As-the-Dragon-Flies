<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var \common\models\LoginForm $model */
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="login">
    <div class="login__block active">
        <div class="login__block active" id="l-login">

            <div class="login__block__header">
                <i class="bi bi-person-circle"></i>

                <div class="actions actions--inverse login__block__actions">
                    <div class="dropdown">
                        <i data-toggle="dropdown" class="bi bi-three-dots-vertical actions__item"></i>

                        <div class="dropdown-menu dropdown-menu-right">
                            <?=
                            Html::a('Create an account', ['site/signup'], [
                                'class' => 'dropdown-item', 'data-sa-action' => 'login-switch',
                            ])
                            ?>
                            <?=
                            Html::a('Forgot password?', ['site/request-password-reset'], [
                                'class' => 'dropdown-item', 'data-sa-action' => 'login-switch',
                            ])
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="login__block__body">
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <div class="form-group">
                    <?=
                            $form->field($model, 'username',
                                    ['inputOptions' => [
                                            'autofocus' => 'autofocus',
                                            'class' => 'form-control text-center',
                                            'autocomplete' => 'username',
                                        ]
                                    ])->textInput(['placeholder' => 'User name',])
                            ->label(false)
                    ?>
                </div>

                <div class="form-group">
                    <?=
                            $form->field($model, 'password',
                                    ['inputOptions' => [
                                            'autofocus' => 'autofocus',
                                            'class' => 'form-control text-center',
                                            'autocomplete' => 'current-password',
                                        ]
                                    ])->passwordInput(['placeholder' => 'Password',])
                            ->label(false)
                    ?>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="LoginForm[rememberMe]" value="0">
                        <input type="checkbox" id="loginform-rememberme" class="custom-control-input" name="LoginForm[rememberMe]" value="1" checked="">
                        <label class="custom-control-label" for="loginform-rememberme">Remember Me</label>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <?= Html::submitButton('<i class="bi bi-check"></i>', ['class' => 'btn btn-theme btn--icon', 'name' => 'login-button']) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
