<?php
/** @var yii\web\View $this */
/** @var int $playerId */
/** @var common\models\Quest $quest */
?>
<!-- Party Members -->
<section class="card mb-3">
    <h6 class="text-warning m-3">Partners</h6>
    <?php foreach ($quest->questPlayers as $questPlayer): ?>
        <?php
        if ($questPlayer->player_id !== $playerId): // only the other players
            $partner = $questPlayer->player;
            ?>
            <article class="party-member m-3">
                <p>
                    <i class="bi bi-<?= $questPlayer->reason ? "stop" : "play" ?>-fill"></i>
                    <?= $partner->name ?> (<?= $partner->race->name ?> <?= $partner->class->name ?>)
                </p>
                <div class="progress" role="progressbar" aria-label="Hit points"
                     aria-valuenow="<?= $partner->hit_points ?>" aria-valuemin="0" aria-valuemax="<?= $partner->max_hit_points ?>">
                    <div class="progress-bar text-bg-primary"
                         style="width: <?= intval(($partner->hit_points ?? 0) / ($partner->max_hit_points ?? 1) * 100) ?>%">
                        <?= $partner->hit_points ?>/<?= $partner->max_hit_points ?>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    <?php endforeach; ?>
</section>
