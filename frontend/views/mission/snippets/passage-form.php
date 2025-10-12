<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Passage $model */
/** @var yii\widgets\ActiveForm $form */
/** @var string $storyId */
/** @var string $chapterId */
/** @var string $missionId */
/** @var string $parentId */
?>

<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath">story/<?= $storyId ?></span>
    <span id="hiddenFormName">passage</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

<?php if ($model->image): ?>
    <div class="row">
        <div class="col-2">
            <img src="img/story/<?= $storyId ?>/<?= $model->image ?>" alt="<?= $model->name ?>" class="w-100 h-100" style="object-fit: cover;" />
        </div>
        <div class="col-10">
        <?php endif; ?>

        <?=
                $form->field($model, 'image')
                ->dropdownList(
                        $model->image ? [$model->image => $model->image] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => "Select an image",
                            'maxlength' => true,
                        ]
                )
                ->label('Passage image')
        ?>

        <?= $form->field($model, 'found')->textInput() ?>

        <?php if ($model->image): ?>
        </div>
    </div>
<?php endif; ?>


<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
