<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var \frontend\models\PasswordResetRequestForm $model */
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Request password reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="login">
    <div class="login__block active">
        <div class="login__block active" id="l-login">

            <div class="login__block__header">
                <i class="bi bi-question-circle-fill"></i>
                Reset your password

                <div class="actions actions--inverse login__block__actions">
                    <div class="dropdown">
                        <i data-toggle="dropdown" class="bi bi-three-dots-vertical actions__item"></i>

                        <div class="dropdown-menu dropdown-menu-right">
                            <?=
                            Html::a('Already have an account?', ['site/login'], [
                                'class' => 'dropdown-item', 'data-sa-action' => 'login-switch',
                            ])
                            ?>
                            <?=
                            Html::a('Create an account', ['site/signup'], [
                                'class' => 'dropdown-item', 'data-sa-action' => 'login-switch',
                            ])
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="login__block__body">
                <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                <div class="form-group">
                    <?=
                            $form->field($model, 'email',
                                    ['inputOptions' => [
                                            'autofocus' => 'autofocus',
                                            'class' => 'form-control text-center',
                                        ]
                                    ])->textInput(['placeholder' => 'email',])
                            ->label(false)
                    ?>
                </div>

                <div class="form-group">
                    <?= Html::submitButton('<i class="bi bi-check"></i>', ['class' => 'btn btn-theme btn--icon', 'name' => 'Send']) ?>
                </div>


                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
