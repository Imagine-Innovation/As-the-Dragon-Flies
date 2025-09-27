<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Mission $model */
$this->title = 'Create Mission';
$chapter = $model->chapter;
$story = $chapter->story;

$breadcrumbs = [
    ['label' => 'Stories', 'url' => ['story/index']],
    ['label' => $story->name, 'url' => ['story/view', 'id' => $story->id]],
    ['label' => $chapter->name, 'url' => ['chapter/view', 'id' => $chapter->id]],
    ['label' => $this->title],
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
