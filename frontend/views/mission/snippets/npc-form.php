<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Npc $model */
/** @var yii\widgets\ActiveForm $form */
/** @var string $storyId */
/** @var string $chapterId */
/** @var string $missionId */
?>

<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath">story/<?= $storyId ?></span>
    <span id="hiddenFormName">npc</span>
    <span id="hiddenParentId"><?= $missionId ?></span>
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
                $form->field($model, 'npc_type_id')
                ->dropdownList(
                        $model->npc_type_id ? [$model->npc_type_id => $model->npcType->name] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => "Select the type of NPC",
                        ]
                )
                ->label('NPC type')
        ?>

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
                ->label('NPC image')
        ?>

        <?php
        if ($model->id) {
            echo $form->field($model, 'first_dialog_id')
                    ->dropdownList(
                            $model->first_dialog_id ? [$model->first_dialog_id => $model->firstDialog->text] : [],
                            [
                                'class' => 'select2-container w-100',
                                'data-minimum-results-for-search' => -1,
                                'data-placeholder' => "Select the first dialog of the NPC",
                            ]
                    )
                    ->label('First dialog');
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
