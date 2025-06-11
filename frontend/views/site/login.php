<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var \common\models\LoginForm $model */
use frontend\helpers\Caligraphy;
use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

$v1 = false;
?>
<section class="vh-100">
    <div class="container py-5 h-100"> <!-- vh-100, py-5, h-100 should be covered -->
        <div class="row d-flex justify-content-center h-100"> <!-- row, d-flex, justify-content-center, h-100 should be covered -->
            <div class="col-12 col-md-8 col-lg-6 col-xl-5"> <!-- col-* classes should be covered -->
                <div class="card" style="border-radius: 2rem;background-color: rgba(20,20,20,.85);"> <!-- card should be covered -->
                    <div class="card-body p-5"> <!-- card-body, p-5 should be covered -->

                        <div class="mb-5 mt-4 pb-5"> <!-- mb-md-5 -> mb-5, mt-md-4 -> mt-4. pb-5 covered -->

                            <h2 class="mb-2"> <!-- mb-2 covered -->
                                <img src="img/Dragonfly32White.png" alt="Logo">
                                <?= Caligraphy::appName() ?>
                            </h2>
                            <p class="mb-5">Please enter your login and password!</p>

                            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                            <div class="form-group first">
                                <?=
                                        $form->field($model, 'username',
                                                ['inputOptions' => [
                                                        'autofocus' => 'autofocus',
                                                        'class' => 'form-control',
                                                        'autocomplete' => 'username',
                                                    ]
                                                ])->textInput(['placeholder' => 'Your user name',])
                                        ->label('Username')
                                ?>
                            </div>
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

                            <div class="d-flex mb-5 align-items-center">
                                <label class="custom-control custom-checkbox mb-0"><span class="caption">Remember me </span>
                                    <input type="checkbox" checked="checked"/>
                                </label>
                                <span style="margin-left: auto;">
                                    <a class="fw-bold" href="<?= Url::toRoute(['site/request-password-reset']) ?>">Forgot Password</a>
                                </span>
                            </div>

                            <div class="form-group">
                                <?=
                                Html::submitButton('<img src="img/Dragonfly.svg" style="height:32px;" alt=""> Log In', [
                                    'class' => 'form-control btn btn-lg btn-warning text-decoration', // btn, btn-lg, btn-warning, text-decoration (dragon.css)
                                    'name' => 'login-button'
                                ])
                                ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                            <p class="mb-0"> <!-- mb-0 covered -->
                                Don't have an account?
                                <a class="fw-bold" href="<?= Url::toRoute(['site/signup']) ?>">Sign Up</a> <!-- fw-bold covered -->
                            </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
