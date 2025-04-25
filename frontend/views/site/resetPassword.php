<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var \frontend\models\ResetPasswordForm $model */
use frontend\helpers\Caligraphy;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Reset password';
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
                            <p class="mb-5">Choose your new password!</p>

                            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                            <div class="form-group last mb-3">
                                <?=
                                        $form->field($model, 'password',
                                                ['inputOptions' => [
                                                        'autofocus' => 'autofocus',
                                                        'class' => 'form-control',
                                                        'autocomplete' => 'current-password',
                                                    ]
                                                ])->passwordInput(['placeholder' => 'Your password',])
                                        ->label('Password')
                                ?>
                            </div>


                            <div class="form-group">
                                <?=
                                Html::submitButton('<img src="img/Dragonfly.svg" style="height:32px;" alt=""> Set new password', [
                                    'class' => 'form-control btn btn-lg btn-warning text-decoration',
                                    'name' => 'reset-button'
                                ])
                                ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                            <p class="mb-0">
                                You've found your password ?
                                <a class="fw-bold" href="<?= Url::toRoute(['site/login']) ?>">Log in</a>
                            </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
