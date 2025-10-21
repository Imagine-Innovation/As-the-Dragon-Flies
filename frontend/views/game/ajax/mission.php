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
$playerId = Yii::$app->session->get('playerId');
?>
<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenQuestId"><?= $model->quest_id ?></span>
    <span id="hiddenQuestProgressId"><?= $model->id ?></span>
    <span id="hiddenQuestMissionId"><?= $model->mission_id ?></span>
    <span id="hiddenCurrentPlayerId"><?= $model->current_player_id ?></span>
</div>

<article class="flex-grow-1 h-auto mb-3 text-decoration">
    <?php if ($mission->image): ?>
        <div class="clearfix">
            <img class="float-md-end mb-3 ms-md-4" src="img/story/<?= $quest->story_id ?>/<?= $mission->image ?>" alt="<?= $mission->name ?>" style="max-width: 50%;">
            <?= $model->description ?>
        </div>
    <?php else: ?>
        <?= $model->description ?>
    <?php endif; ?>
    <div id="actionList">
        <?php
        if ($model->current_player_id === $playerId) {
            echo $this->renderFile('@app/views/game/ajax/actions.php', ['questActions' => $model->questActions]);
        }
        ?>
    </div>
</article>
