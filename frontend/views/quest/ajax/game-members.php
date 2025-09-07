<?php

use common\components\AppStatus;

/** @var yii\web\View $this */
/** @var common\models\QuestPlayer[] $models */
$playerId = Yii::$app->session->get('playerId');
$textColor = [
    AppStatus::ONLINE->value => 'body',
    AppStatus::OFFLINE->value => 'warning',
    AppStatus::LEFT->value => 'error',
];
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
                    <p class="text-<?= $textColor[$questPlayer->status] ?? "" ?>">
                        <span data-bs-toggle="tooltip" title="<?= $iconInfo['tooltip'] ?>" data-placement="bottom">
                            <i class="bi <?= $iconInfo['icon'] ?>"></i>
                            <?= $partner->name ?> (<?= $partner->race->name ?> <?= $partner->class->name ?>)
                        </span>
                    </p>
                    <div class="progress" role="progressbar" aria-label="Hit points"
                         aria-valuenow="<?= $partner->hit_points ?>" aria-valuemin="0" aria-valuemax="<?= $partner->max_hit_points ?>">
                        <div class="progress-bar text-bg-<?= $textColor[$questPlayer->status] ?? "" ?>"
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
