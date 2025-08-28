<?php
/** @var yii\web\View $this */
/** @var common\models\Player $player */
?>
<!-- Health -->
<p>Health</p>
<div class="progress" role="progressbar" aria-label="Hit points"
     aria-valuenow="<?= $player->hit_points ?>" aria-valuemin="0" aria-valuemax="<?= $player->max_hit_points ?>">
    <div class="progress-bar text-bg-warning"
         style="width: <?= intval(($player->hit_points ?? 0) / ($player->max_hit_points ?? 1) * 100) ?>%">
        <?= $player->hit_points ?>/<?= $player->max_hit_points ?>
    </div>
</div>
