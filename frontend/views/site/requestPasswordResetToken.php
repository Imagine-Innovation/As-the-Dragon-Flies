<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var \frontend\models\PasswordResetRequestForm $model */
use frontend\helpers\Caligraphy;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'Request password reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<section class="vh-100">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card" style="border-radius: 2rem;background-color: rgba(20,20,20,.85);">
                    <div class="card-body p-5">
                        <div class="mb-md-5 mt-md-4 pb-5">
                            <h2 class="mb-2">
                                <img src="img/Dragonfly32White.png" alt="Logo">
                                <?= Caligraphy::appName() ?>
                            </h2>
                            <p class="mb-5">Reset your password</p>

                            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                            <div class="form-group first">
                                <?=
                                        $form->field($model, 'email',
                                                ['inputOptions' => [
                                                        'autofocus' => 'autofocus',
                                                        'class' => 'form-control',
                                                    ]
                                                ])->textInput(['placeholder' => 'Your email',])
                                        ->label('Email')
                                ?>
                            </div>

                            <div class="form-group">
                                <?=
                                Html::submitButton('<img src="img/Dragonfly.svg" style="height:32px;" alt=""> Send request', [
                                    'class' => 'form-control btn btn-lg btn-warning text-decoration',
                                    'name' => 'Send'
                                ])
                                ?>
                            </div>
                            <p class="mb-0">
                                Back to <a class="fw-bold" href="<?= Url::toRoute(['site/login']) ?>">Login</a>
                            </p>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
