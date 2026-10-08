<?php

use common\helpers\WebResourcesHelper;
use common\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Player[] $models */
$imgPath = WebResourcesHelper::imagePath();
$playerId = Yii::$app->session->get('playerId');
?>
<div class="row g-4">
    <?php foreach ($models as $player): ?>
        <div class="col-12 col-sm-6 col-md-12 col-lg-6 col-xl-4 col-xxl-3">
            <div class="image-card">
                <div class="image-card-body" style="background-image: url('<?= $imgPath ?>/character/<?= $player->image?->file_name ?>');">
                    <div class="image-card-label">
                        <h5><?= $player->name ?></h5>
                        <p class="small mb-1">
                            <?= Yii::t('app/game', '{age}-year-old {gender} {race}', [
                                'age' => $player->age,
                                'gender' => $player->gender === 'M' ? Yii::t('app/game', 'male') : Yii::t('app/game', 'female'),
                                'race' => $player->race?->name,
                            ]) ?>
                        </p>
                        <p class="small mb-0"><?= $player->level?->name ?> <?= $player->alignment?->name ?> <?= $player->class?->name ?></p>
                        <?php
                        // Buttons are displayed only for the current player
                        if ($player->id === $playerId) {
                            // if the current user is the quest initiator,
                            // he is the only one actually allowed to start the quest.
                            if ($player->id === $player->quest->initiator_id) {
                                // The "Start the quest" button remains hidden until
                                // the browser receives a "quest-can-start" event
                                echo
                                Button::widget([
                                    'isPost' => true,
                                    'url' => Url::toRoute(['quest/start', 'id' => $player->quest_id]),
                                    'style' => 'btn-sm mt-2 w-100 d-none',
                                    'isCta' => true,
                                    'id' => 'startQuestButton',
                                    'icon' => 'dnd-action-move',
                                    'title' => Yii::t('app/game', 'Start the quest'),
                                ])
                                ;
                            } else {
                                // If he is not the one who create the quest (the initiator),
                                // he is allowed to quit the quest
                                echo
                                Button::widget([
                                    'isPost' => true,
                                    'url' => Url::toRoute(['quest/quit', 'playerId' => $playerId, 'id' => $player->quest_id,]),
                                    'style' => 'btn-sm mt-2 w-100',
                                    'isCta' => true,
                                    'id' => 'leaveQuestButton',
                                    'icon' => 'bi-box-arrow-right',
                                    'title' => Yii::t('app/game', 'Leave Tavern'),
                                ])
                                ;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
