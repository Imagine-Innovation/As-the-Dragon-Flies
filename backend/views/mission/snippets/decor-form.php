<?php

use common\helpers\WebResourcesHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Decor $model */
/** @var yii\widgets\ActiveForm $form */
/** @var int $storyId */
/** @var int $chapterId */
/** @var int $missionId */
/** @var int $parentId */
$storyRoot = WebResourcesHelper::storyRootPath($storyId);
?>

<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath"><?= $storyRoot ?>\img</span>
    <span id="hiddenFormName">decor</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

<?php if ($model->image): ?>
    <div class="row">
        <div class="col-md-2 mb-3">
            <img src="<?= $storyRoot ?>/img/<?= $model->image ?>" alt="<?= $model->name ?>" class="w-100 h-100" style="object-fit: cover;" />
        </div>
        <div class="col-md-10">
        <?php endif; ?>

        <?=
                $form->field($model, 'image')->dropdownList(
                        $model->image ? [$model->image => $model->image] : [],
                        [
                            'class' => 'select2-container w-100 select2-hidden-accessible',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select an image',
                            'maxlength' => true,
                        ],
                )
                ->label('Decor image')
        ?>

        <?php if ($model->image): ?>
        </div>
    </div>
<?php endif; ?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php if ($model->id): ?>
    <div class="row row-cols-1 row-cols-lg-2 g-4">
        <?=
        $this->renderFile('@app/views/mission/snippets/card.php', [
            'properties' => $model->decorItems,
            'parentId' => $model->id,
            'type' => 'Item',
        ])
        ?>
        <?=
        $this->renderFile('@app/views/mission/snippets/card.php', [
            'properties' => $model->traps,
            'parentId' => $model->id,
            'type' => 'Trap',
        ])
        ?>
    </div>
<?php endif; ?>
