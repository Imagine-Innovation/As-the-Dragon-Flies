<?php

use common\components\NarrativeComponent;
use common\helpers\WebResourcesHelper;
use common\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Mission $model */
$this->title = $model->name;
$chapter = $model->chapter;
$story = $chapter->story;
$storyRoot = WebResourcesHelper::storyRootPath($story->id);

$breadcrumbs = [
    ['label' => 'Stories', 'url' => ['story/index']],
    ['label' => $story->name, 'url' => ['story/view', 'id' => $story->id]],
    ['label' => $chapter->name, 'url' => ['chapter/view', 'id' => $chapter->id]],
    ['label' => $model->name],
];

// Set breadcrumbs for the view
$this->params['breadcrumbs'] = $breadcrumbs;

$narrative = new NarrativeComponent(['mission' => $model, 'title' => false]);
$description = $narrative->renderDescription();
?>
<div class="container-fluid">
    <div class="card mb-3">
        <div class="actions">
            <?=
            Button::widget([
                'mode' => 'icon',
                'url' => Url::toRoute(['mission/update', 'id' => $model->id]),
                'icon' => 'dnd-spell',
                'tooltip' => 'Edit mission',
            ])
            ?>
        </div>
        <div class="card-header">
            <h3 class="card-title">Mission: <?= $model->name ?></h3>
        </div>
        <div class="card-body flex-grow-1"> <!-- Add flex-grow-1 -->
            <?php if ($model->image): ?>
                <div class="clearfix mb-3">
                    <img class="float-md-end mb-3 ms-md-4" src="<?= $storyRoot ?>/img/<?= $model->image ?>" alt="<?= $model->name ?>" style="max-width: 300px;">
                    <?= $description ?>
                </div>
            <?php else: ?>
                <?= $description ?>
            <?php endif; ?>
            <br>
            <section id="MissionEnvironment">
                <div class="row row-cols-1 row-cols-xl-3 g-4">
                    <?=
                    $this->renderFile('@app/views/mission/snippets/card.php', [
                        'properties' => $model->npcs,
                        'parentId' => $model->id,
                        'type' => 'NPC',
                    ])
                    ?>
                    <?=
                    $this->renderFile('@app/views/mission/snippets/card.php', [
                        'properties' => $model->decors,
                        'parentId' => $model->id,
                        'type' => 'Decor',
                    ])
                    ?>
                    <?=
                    $this->renderFile('@app/views/mission/snippets/card.php', [
                        'properties' => $model->monsters,
                        'parentId' => $model->id,
                        'type' => 'Monster',
                    ])
                    ?>
                </div>
            </section>
            <p />
            <section id="MissionActions">
                <div class="row row-cols-1">
                    <?=
                    $this->renderFile('@app/views/mission/snippets/card.php', [
                        'properties' => $model->actions,
                        'parentId' => $model->id,
                        'type' => 'Action',
                    ])
                    ?>
                </div>
            </section>
        </div>
    </div>
</div>
