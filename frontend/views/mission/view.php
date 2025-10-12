<?php

use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Mission $model */
$this->title = $model->name;
$chapter = $model->chapter;
$story = $chapter->story;

$breadcrumbs = [
    ['label' => 'Stories', 'url' => ['story/index']],
    ['label' => $story->name, 'url' => ['story/view', 'id' => $story->id]],
    ['label' => $chapter->name, 'url' => ['chapter/view', 'id' => $chapter->id]],
    ['label' => $model->name],
];

// Set breadcrumbs for the view
$this->params['breadcrumbs'] = $breadcrumbs;

\yii\web\YiiAsset::register($this);
?>
<div class="container">
    <div class="card mb-3">
        <div class="actions">
            <?=
            Button::widget([
                'mode' => 'icon',
                'url' => Url::toRoute(['mission/update', 'id' => $model->id]),
                'icon' => 'dnd-spell',
                'tooltip' => "Edit mission"
            ])
            ?>
        </div>
        <div class="row g-0 d-flex"> <!-- Add d-flex to the row -->
            <div class="col-0 col-md-4 d-flex align-items-stretch"> <!-- Add d-flex and align-items-stretch -->
                <img src="img/story/<?= $story->id ?>/<?= $model->image ?>" class="img-fluid object-fit-cover rounded-start w-100" alt="<?= $model->name ?>">
            </div>
            <div class="col-12 col-md-8 d-flex flex-column"> <!-- Add d-flex and flex-column -->
                <div class="card-header">
                    <h3 class="card-title">Mission: <?= $model->name ?></h3>
                </div>
                <div class="card-body flex-grow-1"> <!-- Add flex-grow-1 -->
                    <p class="card-text text-decoration"><?= nl2br($model->description) ?></p>
                    <br>
                    <section id="MissionEnvironment">
                        <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 g-4">
                            <?= $this->renderFile('@app/views/mission/snippets/card.php', ['properties' => $model->npcs, 'parentId' => $model->id, 'type' => 'NPC']) ?>
                            <?= $this->renderFile('@app/views/mission/snippets/card.php', ['properties' => $model->decors, 'parentId' => $model->id, 'type' => 'Decor']) ?>
                            <?= $this->renderFile('@app/views/mission/snippets/card.php', ['properties' => $model->passages, 'parentId' => $model->id, 'type' => 'Passage']) ?>
                            <?= $this->renderFile('@app/views/mission/snippets/card.php', ['properties' => $model->monsters, 'parentId' => $model->id, 'type' => 'Monster']) ?>
                        </div>
                    </section>
                    <p />
                    <section id="MissionActions">
                        <div class="row row-cols-1">
                            <?= $this->renderFile('@app/views/mission/snippets/card.php', ['properties' => $model->actions, 'parentId' => $model->id, 'type' => 'Action']) ?>
                        </div>
                    </section>
                    <br>
                    <?php if (1 == 2): ?>
                        <?php foreach ($model->npcs as $Npc): ?>
                            <section id="NPCDialog" class="card g-4">
                                <div class="card-header">
                                    <h3 class="card-title">Dialog with "<?= $Npc->name ?>"</h3>
                                </div>
                                <div class="card-body">
                                    <?php if ($Npc->first_dialog_id): ?>
                                        <?= $this->renderFile('@app/views/mission/snippets/dialog.php', ['dialog' => $Npc->firstDialog]) ?>
                                    <?php endif; ?>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
