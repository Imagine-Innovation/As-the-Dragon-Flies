<?php

use common\helpers\StoryNeededClass;
use common\helpers\StoryPlayers;
use frontend\components\QuestOnboarding;
use frontend\widgets\Button;
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

$image = $story->image ?
        "img/story/{$story->id}/{$story->image->file_name}" :
        "img/sm/" . mt_rand(1, 8) . ".jpg";
?>

<div class="card h-100">
    <div class="card-header">
        <h4 class="card-title"><?= $story->name ?></h4>
    </div>
    <?php if ($user->is_designer): ?>
        <div class="actions">
            <a href="<?= Url::toRoute(['story/view', 'id' => $story->id]) ?>" role="button" class="actions__item position-relative"
               data-bs-toggle="tooltip" title="View story details" data-placement="bottom">
                <i class="bi bi-journal"></i>
            </a>
            <a href="<?= Url::toRoute(['story/update', 'id' => $story->id]) ?>" role="button" class="actions__item position-relative"
               data-bs-toggle="tooltip" title="Edit story" data-placement="bottom">
                <i class="bi bi-journal-code"></i>
            </a>
        </div>
    <?php endif; ?>

    <img class="card-img-top" src="<?= $image ?>">

    <div class="card-body">
        <?php if (!$canJoin['denied']): ?>
            <p>
                <?=
                Button::widget([
                    'route' => ['quest/join-quest', 'storyId' => $story->id],
                    'icon' => 'bi-action-move',
                    'title' => 'Join the quest'
                ])
                ?>
            </p>
        <?php endif; ?>

        <?php if ($user->is_player): ?>
            <p class="lead">
                <?= StoryPlayers::exists($story, $user->players); ?>
            </p>
        <?php endif; ?>

        <p>
            <span class="badge badge-info"><?= $story->requiredLevels ?></span>
            <span class="badge badge-info"><?= $story->companySize ?></span>
            <?php if ($story->tavern): ?>
                <span class="badge badge-info"><?= $story->tavern->getQuestPlayers()->count() ?> partners waiting</span>
            <?php endif; ?>
        </p>

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
