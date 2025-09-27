<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Mission $model */
$this->title = 'Update Mission: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Missions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';

$chapter = $model->chapter;
$story = $chapter->story;

$breadcrumbs = [
    ['label' => 'Stories', 'url' => ['story/index']],
    ['label' => $story->name, 'url' => ['story/view', 'id' => $story->id]],
    ['label' => $chapter->name, 'url' => ['chapter/view', 'id' => $chapter->id]],
    ['label' => $model->name, 'url' => ['view', 'id' => $model->id]],
    ['label' => 'Update'],
];

// Set breadcrumbs for the view
$this->params['breadcrumbs'] = $breadcrumbs;
?>
<div class="container">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('snippets/mission-form', [
        'model' => $model,
        'storyId' => $story->id,
        'chapterId' => $chapter->id,
    ])
    ?>

</div>
