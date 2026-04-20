<?php

use common\helpers\WebResourcesHelper;
use common\widgets\SimpleRichText;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Npc $model */
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
    <span id="hiddenFormName">npc</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->widget(SimpleRichText::class) ?>

<?php if ($model->image): ?>
    <div class="row row-cols-1 row-cols-md-2">
        <div class="col col-md-2 d-none d-md-block ">
            <img src="<?= $storyRoot ?>/img/<?= $model->image ?>" alt="<?= $model->name ?>" class="w-100 h-100" style="object-fit: cover;" />
        </div>
        <div class="col-12 col-md-10">
        <?php endif; ?>

        <?php
        echo $form->field($model, 'npc_type_id')->dropdownList(
                        $model->npc_type_id ? [$model->npc_type_id => $model->npcType->name] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select the type of NPC',
                        ],
                )
                ->label('NPC type');

        echo $form->field($model, 'image')->dropdownList(
                        $model->image ? [$model->image => $model->image] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select an image',
                            'maxlength' => true,
                        ],
                )
                ->label('NPC image');

        if ($model->id) {
            echo $form->field($model, 'first_dialog_id')->dropdownList(
                            $model->first_dialog_id ? [$model->first_dialog_id => $model->firstDialog->text] : [],
                            [
                                'class' => 'select2-container w-100',
                                'data-minimum-results-for-search' => -1,
                                'data-placeholder' => 'Select the first dialog of the NPC',
                            ],
                    )
                    ->label('First dialog')
            ;

            echo $form->field($model, 'language_id')->dropdownList(
                            $model->language_id ? [$model->language_id => $model->language->name] : [],
                            [
                                'class' => 'select2-container w-100',
                                'data-minimum-results-for-search' => -1,
                                'data-placeholder' => 'Select a language',
                            ],
                    )
                    ->label('Language spoken')
            ;
        }
        ?>

        <?php if ($model->image): ?>
        </div>
    </div>
<?php endif; ?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php if ($model->first_dialog_id): ?>
    <section id="NPCDialog" class="card g-4">
        <div class="card-header">
            <h3 class="card-title">Dialog with "<?= $model->name ?>"</h3>
        </div>
        <div class="card-body">
            <?= $this->renderFile('@app/views/mission/snippets/dialog.php', ['dialog' => $model->firstDialog]) ?>
        </div>
    </section>
<?php endif; ?>
