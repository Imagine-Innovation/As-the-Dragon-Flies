<?php

use common\models\Outcome;

/** @var Outcome $outcome */
/** @var string $actionOutcome */
?>
<div class="text-decoration">
    <h3><?= $outcome->name ?></h3>
    <p class="text-muted"><?= nl2br($outcome->description ?? '') ?></p>
    <?= $actionOutcome ?>
</div>
