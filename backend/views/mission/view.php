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

$narrative = new NarrativeComponent(['mission' => $model]);
$missionDescription = $narrative->missionDecription();
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
        <?php if ($model->image): ?>
            <div class="row">
                <div class="col-2">
                    <img src="<?= $storyRoot ?>/img/<?= $model->image ?>" alt="<?= $model->name ?>" class="w-100 h-100" style="object-fit: cover;" />
                </div>
                <div class="col-10">
                <?php endif; ?>
                <!--
            <div class="row g-0 d-flex">
                <div class="col-0 col-md-4 d-flex align-items-stretch">
                    <img src="<?= $storyRoot ?>/img/<?= $model->image ?>" class="img-fluid object-fit-cover rounded-start w-100" alt="<?= $model->name ?>">
                </div>
                <div class="col-12 col-md-8 d-flex flex-column">
                -->
                <div class="card-header">
                    <h3 class="card-title">Mission: <?= $model->name ?></h3>
                </div>
                <div class="card-body flex-grow-1"> <!-- Add flex-grow-1 -->
                    <?php
                    foreach ($missionDescription as $description) {
                        echo $description;
                    }
                    ?>
                    <br>
                    <section id="MissionEnvironment">
                        <div class="row row-cols-1 row-cols-xl-2 row-cols-xxl-4 g-4">
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
                                'properties' => $model->passages,
                                'parentId' => $model->id,
                                'type' => 'Passage',
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
        <?php if ($model->image): ?>
        </div>
    </div>
<?php endif; ?>
