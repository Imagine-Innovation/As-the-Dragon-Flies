<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $diceRoll */
/** @var common\components\Apptatus $status */
/** @var common\models\Outcome $outcomes */
/** @var int $hpLoss */
?>

<p><?= $diceRoll ?>: the action <?= $status->getLabel() ?></p>
<?php foreach ($outcomes as $outcome): ?>
    <hr class="border border-warning border-1 opacity-50 w-50"><hr>
    <?php if ($outcome->description): ?>
        <p><?= nl2br($outcome->description) ?></p>
    <?php endif; ?>
    <?php if ($outcome->gained_gp > 0): ?>
        <p>You gained <?= $outcome->gained_gp ?> gold pieces</p>
    <?php endif; ?>
    <?php if ($outcome->gained_xp > 0): ?>
        <p>You gained <?= $outcome->gained_xp ?> experience points</p>
    <?php endif; ?>
    <?php if ($outcome->item_id): ?>
        <p>You now have a <?= $outcome->item->name ?> in your back bag</p>
    <?php endif; ?>
<?php endforeach; ?>
<hr class="border border-warning border-1 opacity-50 w-50"><hr>
<?php if ($hpLoss > 0): ?>
    <p>You lost <?= $hpLoss ?> hit points</p>
<?php endif; ?>
