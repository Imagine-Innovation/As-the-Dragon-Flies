<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\db\ActiveRecord $model */
/** @var common\models\Mission $mission */
/** @var string $type */
/** @var string $snippet */
$displayName = match ($type) {
    'Prerequisite' => $model->previousAction->name,
    'Trigger' => $model->nextAction->name,
    default => $model->name,
};

$this->title = "Update “{$type}”: “{$displayName}”";
$chapter = $mission->chapter;
$story = $chapter->story;

$parentId = match ($type) {
    'Item' => $model->decor_id,
    'Trap' => $model->decor_id,
    'Prerequisite' => $model->next_action_id,
    'Trigger' => $model->previous_action_id,
    'Outcome' => $model->action_id,
    default => $model->mission_id,
};

$breadcrumbs = [
    ['label' => 'Stories', 'url' => ['story/index']],
    ['label' => $story->name, 'url' => ['story/view', 'id' => $story->id]],
    ['label' => $chapter->name, 'url' => ['chapter/view', 'id' => $chapter->id]],
    ['label' => $mission->name, 'url' => ['mission/view', 'id' => $mission->id]],
    ['label' => $this->title],
];

// Set breadcrumbs for the view
$this->params['breadcrumbs'] = $breadcrumbs;
?>
<div class="container">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render("snippets/{$snippet}", [
        'model' => $model,
        'storyId' => $story->id,
        'chapterId' => $chapter->id,
        'missionId' => $mission->id,
        'parentId' => $parentId,
    ])
    ?>

</div>
