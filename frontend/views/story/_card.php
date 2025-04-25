<?php

use common\helpers\StoryNeededClass;
use common\helpers\StoryPlayers;
use frontend\components\QuestOnboarding;
use frontend\widgets\ModalDesc;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var common\models\Player $player */
/** @var common\models\Story $story */
/** @var common\models\Quest $quest */
/** @var integer $isDesigner */
/** @var integer $isPlayer */
$canJoin = QuestOnboarding::canPlayerJoinQuest($player, $quest);
?>

<div class="card h-100">
    <?php if (!$canJoin['denied']): ?>
        <div class="card-header">
            <a class="card-link" href="<?= Url::toRoute(['quest/tavern', 'storyId' => $story->id]) ?>">
                Join the quest
            </a>
        </div>
    <?php endif; ?>
    <?php if ($user->is_designer): ?>
        <div class="actions">
            <a href="<?= Url::toRoute(['story/view', 'id' => $story->id]) ?>" class="actions__item position-relative">
                <span data-toggle="tooltip" title="View story details" data-placement="bottom">
                    <i class="bi bi-journal"></i>
                </span>
            </a>
            <a href="<?= Url::toRoute(['story/update', 'id' => $story->id]) ?>" class="actions__item position-relative">
                <span data-toggle="tooltip" title="Edit story" data-placement="bottom">
                    <i class="bi bi-journal-code"></i>
                </span>
            </a>
        </div>
    <?php endif; ?>

    <?php if ($story->image): ?>
        <img class="card-img-top" src="img/story/<?= $story->id ?>/<?= $story->image->file_name ?>">
    <?php else: ?>
        <img class="card-img-top" src="img/sm/<?= mt_rand(1, 8) ?>.jpg">
    <?php endif; ?>

    <div class="card-body">
        <h4 class="card-title"><?= $story->name ?></h4>

        <?php if ($user->is_player): ?>
            <?= StoryPlayers::exists($story, $user->players); ?>
        <?php endif; ?>

        <div>
            <span class="badge badge-warning">Level <?= $story->min_level ?> to <?= $story->max_level ?></span>
            <span class="badge badge-warning"><?= $story->min_players ?> to <?= $story->max_players ?> players</span>
            <?php if ($story->tavern): ?>
                <span class="badge badge-warning"><?= $story->tavern->getQuestPlayers()->count() ?> partners waiting</span>
            <?php endif; ?>
        </div>

        <p class="text-muted">
            <?=
            ModalDesc::widget([
                'name' => $story->name,
                'description' => $story->description,
                'maxLength' => 300,
                'id' => $story->id,
            ])
            ?>
        </p>

        <?php if ($story->tags): ?>
            <div class="listview__attrs">
                <?php foreach ($story->tags as $tag): ?>
                    <span><?= $tag->name ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= StoryNeededClass::classList($story); ?>
    </div>
</div>
