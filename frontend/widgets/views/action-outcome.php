<?php
/** @var string $outcomeName */
/** @var string|null $image */
/** @var string $description */
/** @var string $actionOutcome */
/** @var string $storyRoot */
?>
<div class="text-decoration">
    <h4><?= $outcomeName ?></h4>
    <?php if ($image): ?>
        <div class="clearfix">
            <img class="col-md-6 float-md-end mb-3 ms-md-3" src="<?= $storyRoot ?>/img/<?= $image ?>" alt="<?= $outcomeName ?>" style="max-width: 150px;">
            <p class="text-muted"><?= $description ?></p>
            <?= $actionOutcome ?>
        </div>
    <?php else: ?>
        <p class="text-muted"><?= $description ?></p>
        <?= $actionOutcome ?>
    <?php endif; ?>
</div>
