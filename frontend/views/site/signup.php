<?php

use common\helpers\WebResourcesHelper;
use frontend\helpers\Caligraphy;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\SignupForm $model */
$imgPath = WebResourcesHelper::imagePath();
$this->title = Yii::t('guest', 'Signup');
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
                                <img src="<?= $imgPath ?>/Dragonfly32White.png" alt="Logo">
                                <?= Caligraphy::appName() ?>
                            </h2>
                            <p class="mb-5"><?= Yii::t('guest', 'Define your login and password to signup!') ?></p>

                            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
                            <div class="form-group first">
                                <?=
                                        $form
                                        ->field($model, 'username', ['inputOptions' => [
                                                'autofocus' => 'autofocus',
                                                'class' => 'form-control',
                                    ]])
                                        ->textInput(['placeholder' => Yii::t('guest', 'The username you will use to connect')])
                                        ->label(Yii::t('guest', 'User name'))
                                ?>
                            </div>
                            <div class="form-group">
                                <?=
                                        $form
                                        ->field($model, 'fullname', ['inputOptions' => [
                                                'autofocus' => 'autofocus',
                                                'class' => 'form-control',
                                    ]])
                                        ->textInput(['placeholder' => Yii::t('guest', 'The name that will be displayed in the game')])
                                        ->label(Yii::t('guest', 'Full name'))
                                ?>
                            </div>
                            <div class="form-group">
                                <?=
                                        $form
                                        ->field($model, 'email', ['inputOptions' => [
                                                'class' => 'form-control',
                                    ]])
                                        ->textInput(['placeholder' => Yii::t('guest', 'Your email use to validate your login and reset your password')])
                                        ->label(Yii::t('guest', 'Email'))
                                ?>
                            </div>
                            <div class="form-group last">
                                <?=
                                        $form
                                        ->field($model, 'password', ['inputOptions' => [
                                                'autofocus' => 'autofocus',
                                                'class' => 'form-control',
                                    ]])
                                        ->passwordInput(['placeholder' => Yii::t('guest', 'Your new password')])
                                        ->label(Yii::t('guest', 'Password'))
                                ?>
                            </div>

                            <div class="form-group">
                                <?=
                                Html::submitButton('<img src="' . $imgPath . '/Dragonfly.svg" style="height:32px;" alt=""> ' . Yii::t('guest', 'Signup'), [
                                    'class' => 'form-control btn btn-lg btn-warning text-decoration',
                                    'name' => 'signup-button',
                                ])
                                ?>
                            </div>
                            <p class="mb-0">
                                <?= Yii::t('guest', 'Already have an account?') ?>
                                <a class="fw-bold" href="<?= Url::toRoute(['site/login']) ?>"><?= Yii::t('guest', 'Login') ?></a>
                            </p>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
