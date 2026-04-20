<?php

use common\components\AppStatus;
use common\widgets\MarkDown;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var common\models\Outcome[] $outcomes */
/* @var string[] $t */
?>


<?php foreach ($outcomes as $outcome): ?>
    <div class="outcome-item border p-2 mb-2">
        <span class="fw-bold"><?= Html::encode($outcome->name) ?></span> (status: <?= AppStatus::from($outcome->status)->getLabel() ?>)
        <?php if ($outcome->description): ?>
            <?= MarkDown::widget(['content' => $outcome->description ?? '']) ?>
        <?php endif; ?>
        <?php if ($outcome->gained_xp): ?>
            <span class="badge text-bg-warning"><?= $t['gained_xp'] ?>: <?= $outcome->gained_xp ?></span>
        <?php endif; ?>
        <?php if ($outcome->gained_gp): ?>
            <span class="badge text-bg-warning"><?= $t['gained_gp'] ?>: <?= $outcome->gained_gp ?></span>
        <?php endif; ?>
        <?php if ($outcome->item_id): ?>
            <span class="badge text-bg-info"><?= $t['gained_item'] ?>: <?= Html::encode($outcome->item?->name ?? '') ?></span>
        <?php endif; ?>
        <?php if ($outcome->hp_loss_dice && $outcome->hp_loss_dice != '0'): ?>
            <span class="badge text-bg-danger"><?= $t['hp_loss'] ?>: <?= $outcome->hp_loss_dice ?></span>
        <?php endif; ?>
        <?php if ($outcome->next_mission_id): ?>
            <p><span class="fw-bold"><?= $t['next_mission'] ?></span>: <?= Html::encode($outcome->nextMission?->name ?? '') ?></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
