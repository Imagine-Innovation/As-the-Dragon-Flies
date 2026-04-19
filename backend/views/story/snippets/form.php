<?php

use common\helpers\WebResourcesHelper;
use common\widgets\SimpleRichText;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Story $model */
/** @var yii\widgets\ActiveForm $form */
$storyRoot = WebResourcesHelper::storyRootPath($model->id);
$languages = [
    'en' => 'English',
    'fr' => 'Français',
];
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?php if ($model->image): ?>
    <div class="row">
        <div class="col-2">
            <img src="<?= $storyRoot ?>/img/<?= $model->image ?>" alt="<?= $model->name ?>" class="w-100 h-100" style="object-fit: cover;" />
        </div>
        <div class="col-10">
        <?php endif; ?>
        <?= $form->field($model, 'description')->widget(SimpleRichText::class) ?>

        <?=
                $form->field($model, 'image')->dropdownList(
                        $model->image ? [$model->image => $model->image] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select an image',
                            'maxlength' => true,
                        ],
                )
                ->label('Item image')
        ?>
        <div class="row row-cols-2 row-cols-xl-4 row-cols-xxl-5 g-3">
            <div class="col">
                <?= $form->field($model, 'min_level')->textInput() ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'max_level')->textInput() ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'min_players')->textInput() ?>
            </div>
            <div class="col">
                <?= $form->field($model, 'max_players')->textInput() ?>
            </div>
            <div class="col-6 col-xxl-12">
                <?= $form->field($model, 'language')->radioList($languages) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>

        <?php if ($model->image): ?>
        </div>
    </div>
<?php endif; ?>

<?php ActiveForm::end(); ?>
