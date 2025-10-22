<?php
/** @var yii\web\View $this */
/** @var common\models\QuestProgress $questProgress */
$quest = $questProgress->quest;
$mission = $questProgress->mission;
?>
<?php if ($mission->image): ?>
    <div class="clearfix">
        <img class="float-md-end mb-3 ms-md-4" src="img/story/<?= $quest->story_id ?>/<?= $mission->image ?>" alt="<?= $mission->name ?>" style="max-width: 50%;">
        <?= $questProgress->description ?>
    </div>
<?php else: ?>
    <?= $questProgress->description ?>
<?php endif; ?>
