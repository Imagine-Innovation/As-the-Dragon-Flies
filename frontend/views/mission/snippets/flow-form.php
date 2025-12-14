<?php

use common\components\AppStatus;
use frontend\widgets\MissionElement;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\ActionFlow $model */
/** @var yii\widgets\ActiveForm $form */
/** @var string $storyId */
/** @var string $chapterId */
/** @var string $missionId */
/** @var string $parentId */
?>

<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath">resources\story-<?= $storyId ?>\img</span>
    <span id="hiddenFormName">actionflow</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

<?php
$form = ActiveForm::begin(
        [
            'options' => [
                'class' => 'row g-3',
            ],
        ]
);
?>

<div class="col-12 col-md-4">
    <div class="card p-4 h-100">
        <?=
                $form->field($model, 'previous_action_id')
                ->dropdownList(
                        $model->previous_action_id ? [$model->previous_action_id => $model->previousAction->name] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => "Select an action",
                        ]
                )
                ->label('Previous action')
        ?>

        <?php
        if ($model->previous_action_id) {
            $properties = $model->previousAction;
            $propertyNames = $properties->attributes();
            echo MissionElement::widget([
                'properties' => [$properties],
                'propertyNames' => $propertyNames,
                'type' => 'Action',
            ]);
        }
        ?>
    </div>
</div>

<div class="col-12 col-md-4">
    <div class="card p-4 h-100">
        <?=
                $form->field($model, 'status')
                ->radioList(AppStatus::getActionStatus())
                ->label('Select a status')
        ?>
    </div>
</div>

<div class="col-12 col-md-4">
    <div class="card p-4 h-100">
        <?=
                $form->field($model, 'next_action_id')
                ->dropdownList(
                        $model->next_action_id ? [$model->next_action_id => $model->nextAction->name] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => "Select an action",
                        ]
                )
                ->label('Next action')
        ?>

        <?php
        if ($model->next_action_id) {
            $properties = $model->nextAction;
            $propertyNames = $properties->attributes();
            echo MissionElement::widget([
                'properties' => [$properties],
                'propertyNames' => $propertyNames,
                'type' => 'Action',
            ]);
        }
        ?>
    </div>
</div>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
