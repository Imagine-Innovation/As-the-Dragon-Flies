<?php

use common\components\AppStatus;

/** @var yii\web\View $this */
/** @var common\models\QuestPlayer[] $models */
$playerId = Yii::$app->session->get('playerId');
?>
<div class="m-3">
    <!-- Party Members -->
    <h6 class="text-warning">Partners</h6>
    <?php if (count($models) > 1): ?>
        <?php foreach ($models as $questPlayer): ?>
            <?php
            if ($questPlayer->player_id !== $playerId): // only the other players
                $partner = $questPlayer->player;
                $statusEnum = AppStatus::from($questPlayer->status);
                $iconInfo = $statusEnum->getIcon();
                ?>
                <div class="m-3">
                    <p>
                        <i class="bi <?= $iconInfo['icon'] ?>"></i>
                        <?= $partner->name ?> (<?= $partner->race->name ?> <?= $partner->class->name ?>)
                    </p>
                    <div class="progress" role="progressbar" aria-label="Hit points"
                         aria-valuenow="<?= $partner->hit_points ?>" aria-valuemin="0" aria-valuemax="<?= $partner->max_hit_points ?>">
                        <div class="progress-bar text-bg-primary"
                             style="width: <?= intval(($partner->hit_points ?? 0) / ($partner->max_hit_points ?? 1) * 100) ?>%">
                            <?= $partner->hit_points ?>/<?= $partner->max_hit_points ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="mx-3">You are alone in the quest</p>
    <?php endif; ?>
</div>
