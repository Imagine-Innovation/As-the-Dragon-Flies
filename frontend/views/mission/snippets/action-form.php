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
$difficultyClass = [
    0 => 'Trivial',
    5 => 'Very Easy',
    10 => 'Easy',
    15 => 'Medium',
    20 => 'Hard',
    25 => 'Very Hard',
    30 => 'Nearly Impossible',
];

$actionType = $model->action_type_id ? $model->actionType : null;
?>
<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenImagePath">resources\story-<?= $storyId ?>\img</span>
    <span id="hiddenFormName">action</span>
    <span id="hiddenParentId"><?= $parentId ?></span>
</div>

<?php if ($model->id): ?>
    <article>
        <p>Action:
            <?php if ($actionType): ?>
                <?= $actionType->name ?> <?=
                $actionType->description ? "({$actionType->description})" : ''
                ?>
            <?php endif; ?>
            <?= $model->passage?->name ?>
            <?= $model->trap?->name ?>
            <?= $model->decorItem?->name ?>
            <?= $model->decor ? "in {$model->decor->name}" : '' ?>
            <?= $model->npc ? "with {$model->npc->name}" : '' ?>
            <?= $model->reply ? "saying “{$model->reply->text}”" : '' ?>
            <?=
            $model->requiredItem ? "will need {$model->requiredItem->name}" : ''
            ?>
        </p>
    </article>
<?php endif; ?>

<?php $form = ActiveForm::begin(); ?>

<div class="row row-cols-1 row-cols-sm-2 g-3">
    <div class="col">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col">
        <?=
                $form
                ->field($model, 'action_type_id')
                ->dropdownList(
                        $model->action_type_id ? [$model->action_type_id => $actionType?->name]
                                    : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select an action type',
                        ],
                )
                ->label('Action type')
        ?>
    </div>
</div>

<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-4 g-3">
    <div class="col">
        <article class="card mb-3 h-100">
            <div class="card-header">
                <h6 class="card-title">Short description</h6>
            </div>
            <div class="card-body">
                <?=
                $form->field($model, 'description', [
                    'labelOptions' => ['style' => 'display: none;'],
                ])->textarea(['rows' => 6])
                ?>
            </div>
        </article>
    </div>

    <?=
    $this->renderFile('@app/views/mission/snippets/card.php', [
        'properties' => $model->prerequisites,
        'parentId' => $model->id,
        'type' => 'Prerequisite',
    ])
    ?>
    <?=
    $this->renderFile('@app/views/mission/snippets/card.php', [
        'properties' => $model->triggers,
        'parentId' => $model->id,
        'type' => 'Trigger',
    ])
    ?>
    <?=
    $this->renderFile('@app/views/mission/snippets/card.php', [
        'properties' => $model->outcomes,
        'parentId' => $model->id,
        'type' => 'Outcome',
    ])
    ?>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-xxl-3 py-3 g-3">
    <div class="col">
        <?= $form->field($model, 'dc')->radioList($difficultyClass)->label('Select a Difficulty Class (DC)') ?>
    </div>
    <div class="col">
        <?=
                $form
                ->field($model, 'partial_dc')
                ->radioList($difficultyClass)
                ->label('Select a Difficulty Class (DC) for partial success')
        ?>
    </div>
    <div class="col">
        <?=
        $form->field($model, 'is_free')->radioList([0 => 'Consume an action', 1 => 'Free action'])->label('Turn economy')
        ?>
    </div>
</div>


<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 row-cols-3xl-6">
    <div class="col">
        <?=
                $form
                ->field($model, 'required_item_id')
                ->dropdownList(
                        $model->required_item_id ? [$model->required_item_id => $model->requiredItem?->name]
                                    : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select an item',
                        ],
                )
                ->label('Object required to carry out the action')
        ?>
    </div>
    <div class="col">
        <?=
                $form
                ->field($model, 'passage_id')
                ->dropdownList(
                        $model->passage_id ? [$model->passage_id => $model->passage?->name]
                                    : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select a passage',
                        ],
                )
                ->label('Passage affected by the action')
        ?>
    </div>
    <div class="col">
        <?=
                $form
                ->field($model, 'decor_id')
                ->dropdownList(
                        $model->decor_id ? [$model->decor_id => $model->decor?->name]
                                    : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select a decor',
                        ],
                )
                ->label('Decor affected by the action')
        ?>
    </div>
    <div class="col">
        <?=
                $form
                ->field($model, 'trap_id')
                ->dropdownList(
                        $model->trap_id ? [$model->trap_id => $model->trap?->name]
                                    : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select a trap',
                        ],
                )
                ->label('Trap affected by the action')
        ?>
    </div>
    <div class="col">
        <?=
                $form
                ->field($model, 'decor_item_id')
                ->dropdownList(
                        $model->decor_item_id ? [$model->decor_item_id => $model->decorItem?->name]
                                    : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select an item',
                        ],
                )
                ->label('Object affected by the action')
        ?>
    </div>
    <div class="col">
        <?=
                $form
                ->field($model, 'npc_id')
                ->dropdownList(
                        $model->npc_id ? [$model->npc_id => $model->npc?->name] : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select a NPC',
                        ],
                )
                ->label('NPC involved in the action')
        ?>
    </div>
    <div class="col">
        <?=
                $form
                ->field($model, 'reply_id')
                ->dropdownList(
                        $model->reply_id ? [$model->reply_id => $model->reply?->text]
                                    : [],
                        [
                            'class' => 'select2-container w-100',
                            'data-minimum-results-for-search' => -1,
                            'data-placeholder' => 'Select a reply',
                        ],
                )
                ->label('First reply of the player')
        ?>
    </div>
</div>

<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success bi-floppy']) ?>
</div>

<?php
ActiveForm::end();
