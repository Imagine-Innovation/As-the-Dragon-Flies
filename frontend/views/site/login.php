<?php

use common\helpers\WebResourcesHelper;
use common\models\LoginForm;
use frontend\helpers\Caligraphy;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var ActiveForm $form */
/** @var LoginForm $model */
$imgPath = WebResourcesHelper::imagePath();
$this->title = Yii::t('app/guest', 'Login');
$this->params['breadcrumbs'][] = $this->title;

$v1 = false;
?>
<section class="vh-100">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card" style="border-radius: 2rem;background-color: rgba(20,20,20,.85);">
                    <div class="card-body p-5">

                        <div class="mb-md-5 mt-md-4 pb-5">

                            <h2 class="mb-2">
                                <img src="<?= $imgPath ?>/Dragonfly32White.png" alt="Logo">
                                <?= Caligraphy::appName() ?>
                            </h2>
                            <p class="mb-5"><?= Yii::t('app/guest', 'Please enter your login and password!') ?></p>

                            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                            <div class="form-group first">
                                <?=
                                        $form
                                        ->field($model, 'username', ['inputOptions' => [
                                                'autofocus' => 'autofocus',
                                                'class' => 'form-control',
                                                'autocomplete' => 'username',
                                    ]])
                                        ->textInput(['placeholder' => Yii::t('app/guest', 'Your user name')])
                                        ->label(Yii::t('app/guest', 'Username'))
                                ?>
                            </div>
                            <div class="form-group last mb-3">
                                <?=
                                        $form
                                        ->field($model, 'password', ['inputOptions' => [
                                                'autofocus' => 'autofocus',
                                                'class' => 'form-control',
                                                'autocomplete' => 'current-password',
                                    ]])
                                        ->passwordInput(['placeholder' => Yii::t('app/guest', 'Your password')])
                                        ->label(Yii::t('app/guest', 'Password'))
                                ?>
                            </div>

                            <div class="d-flex mb-5 align-items-center">
                                <label class="custom-control custom-checkbox mb-0"><span class="caption"><?= Yii::t('app/guest', 'Remember me') ?> </span>
                                    <input type="checkbox" checked="checked"/>
                                </label>
                                <span style="margin-left: auto;">
                                    <a class="fw-bold" href="<?= Url::toRoute(['site/request-password-reset']) ?>"><?= Yii::t('app/guest', 'Forgot Password') ?></a>
                                </span>
                            </div>

                            <div class="form-group">
                                <?=
                                Html::submitButton('<img src="' . $imgPath . '/Dragonfly.svg" style="height:32px;" alt=""> ' . Yii::t('app/guest', 'Log In'), [
                                    'class' => 'form-control btn btn-lg btn-warning text-decoration',
                                    'name' => 'login-button',
                                ])
                                ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                            <p class="mb-0">
                                <?= Yii::t('app/guest', "Don't have an account?") ?>
                                <a class="fw-bold" href="<?= Url::toRoute(['site/signup']) ?>"><?= Yii::t('app/guest', 'Sign Up') ?></a>
                            </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
