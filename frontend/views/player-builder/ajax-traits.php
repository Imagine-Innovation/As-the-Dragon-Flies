<?php
/** @var yii\web\View $this */
/** @var app/models/Player $player */
?>

<h4 class="card-title">Traits</h4>
<?php if ($player): ?>
    <?php foreach ($player->playerTraits as $playerTrait): ?>
<p>
    <?= $playerTrait->trait->name ?>: <span class="text-muted"><?= $playerTrait->description ?></span>
</p>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-muted">Your player is not properly saved yet!!</p>
<?php endif; ?>