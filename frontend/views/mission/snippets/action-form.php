<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Action $model */
/** @var yii\widgets\ActiveForm $form */
/** @var string $storyId */
/** @var string $chapterId */
/** @var string $missionId */
/** @var string $parentId */
?>
<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath">item</span>
    <span id="hiddenFormName">action</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

<?php if ($model->id): ?>
    <article>
        <p>Action:
            <?= $model->actionType->name ?> <?= $model->actionType->description ? "({$model->actionType->description})" : "" ?>
            <?= $model?->passage?->name ?>
            <?= $model?->trap?->name ?>
            <?= $model?->decorItem?->name ?>
            <?= $model->decor ? "in {$model->decor->name}" : "" ?>
            <?= $model->npc ? "with {$model->npc->name}" : "" ?>
            <?= $model->reply ? "saying “{$model->reply->text}”" : "" ?>
            <?= $model->requiredItem ? "with {$model->npc->name}" : "" ?>
        </p>
    </article>
    <div class="row row-cols-1 row-cols-lg-2 g-4">
        <?= $this->renderFile('@app/views/mission/snippets/card.php', ['properties' => $model->actionPrerequisites, 'parentId' => $model->id, 'type' => 'Prerequisite']) ?>
        <?= $this->renderFile('@app/views/mission/snippets/card.php', ['properties' => $model->actionTriggers, 'parentId' => $model->id, 'type' => 'Trigger']) ?>
    </div>
<?php endif; ?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

<?=
        $form->field($model, 'action_type_id')
        ->dropdownList(
                $model->action_type_id ? [$model->action_type_id => $model->actionType->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select an action type",
                ]
        )
        ->label('Action type')
?>

<?= $form->field($model, 'dc')->textInput() ?>

<?=
        $form->field($model, 'passage_id')
        ->dropdownList(
                $model->passage_id ? [$model->passage_id => $model->passage->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a passage",
                ]
        )
        ->label('Passage affected by the action')
?>
<?=
        $form->field($model, 'trap_id')
        ->dropdownList(
                $model->trap_id ? [$model->trap_id => $model->trap->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a trap",
                ]
        )
        ->label('Trap affected by the action')
?>
<?=
        $form->field($model, 'decor_item_id')
        ->dropdownList(
                $model->decor_item_id ? [$model->decor_item_id => $model->decorItem->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select an item",
                ]
        )
        ->label('Object affected by the action')
?>
<?=
        $form->field($model, 'decor_id')
        ->dropdownList(
                $model->decor_id ? [$model->decor_id => $model->decor->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a decor",
                ]
        )
        ->label('Decor affected by the action')
?>
<?=
        $form->field($model, 'npc_id')
        ->dropdownList(
                $model->npc_id ? [$model->npc_id => $model->npc->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a NPC",
                ]
        )
        ->label('NPC involved in the action')
?>
<?=
        $form->field($model, 'reply_id')
        ->dropdownList(
                $model->reply_id ? [$model->reply_id => $model->reply->text] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select a reply",
                ]
        )
        ->label('First reply of the player')
?>
<?=
        $form->field($model, 'required_item_id')
        ->dropdownList(
                $model->required_item_id ? [$model->required_item_id => $model->requiredItem->name] : [],
                [
                    'class' => 'select2-container w-100',
                    'data-minimum-results-for-search' => -1,
                    'data-placeholder' => "Select an item",
                ]
        )
        ->label('Object required to carry out the action')
?>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success bi-floppy']) ?>
</div>

<?php ActiveForm::end(); ?>
