<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\db\ActiveRecord $model */
/** @var common\models\Mission $mission */
/** @var string $type */
/** @var string $snippet */
$this->title = "Update {$model->name} {$type}";
$chapter = $mission->chapter;
$story = $chapter->story;

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
    ])
    ?>

</div>
