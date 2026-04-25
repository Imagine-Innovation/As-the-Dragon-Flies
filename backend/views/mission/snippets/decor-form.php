<?php

use common\helpers\WebResourcesHelper;
use common\widgets\SimpleRichText;
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
    <span id="hiddenImagePath"><?= $storyRoot ?>/img</span>
    <span id="hiddenFormName">decor</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

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
    <div class="row row-cols-1 row-cols-xl-3 g-4">
        <?=
        $this->renderFile('@app/views/mission/snippets/card.php', [
            'properties' => $model->decorItems,
            'parentId' => $model->id,
            'type' => 'Item',
        ])
        ?>
        <?=
        $this->renderFile('@app/views/mission/snippets/card.php', [
            'properties' => $model->passages,
            'parentId' => $model->id,
            'type' => 'Passage',
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
