<?php

use common\helpers\WebResourcesHelper;
use common\widgets\SimpleRichText;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\DecorItem $model */
/** @var yii\widgets\ActiveForm $form */
/** @var int $storyId */
/** @var int $chapterId */
/** @var int $missionId */
/** @var int $parentId */
$imgPath = WebResourcesHelper::imagePath();
$storyRoot = WebResourcesHelper::storyRootPath($storyId);
?>
<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath"><?= $storyRoot ?>/img</span>
    <span id="hiddenFormName">decoritem</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->widget(SimpleRichText::class) ?>

<?php if ($model->item_id): ?>
    <div class="row">
        <div class="col-2">
            <img src="<?= $model->image ? "{$storyRoot}/img/{$model->image}" : "{$imgPath}/item/{$model->item->image}" ?>" alt="<?= $model->name ?>" class="w-100 h-100" style="object-fit: cover;" />
        </div>
        <div class="col-10">
        <?php endif; ?>

        <?=
                $form->field($model, 'item_id')->dropdownList(
                        $model->item_id ? [$model->item_id => $model->item->name] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select an item',
                        ],
                )
                ->label('Item present in the decor')
        ?>
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

        <?= $form->field($model, 'found')->textInput() ?>

        <?= $form->field($model, 'identified')->textInput() ?>

        <?php if ($model->image): ?>
        </div>
    </div>
<?php endif; ?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
