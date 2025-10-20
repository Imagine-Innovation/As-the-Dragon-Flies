<?php
/** @var yii\web\View $this */
/** @var common\models\QuestProgress $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
$model = $models[0];
$quest = $model->quest;
$mission = $model->mission;
?>

<article class="flex-grow-1 h-auto mb-3 text-decoration">
    <?php if ($mission->image): ?>
        <div class="clearfix">
            <img class="float-md-end mb-3 ms-md-4" src="img/story/<?= $quest->story_id ?>/<?= $mission->image ?>" alt="<?= $mission->name ?>" style="max-width: 50%;">
            <?= $model->description ?>
        </div>
    <?php else: ?>
        <?= $model->description ?>
    <?php endif; ?>
    <div class="card">
        <div class="card-header">
            What do want to do?
        </div>
        <div class="card-body">
            <?php foreach ($model->questActions as $questAction): ?>
                <p><?= $questAction->action->name ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</article>
