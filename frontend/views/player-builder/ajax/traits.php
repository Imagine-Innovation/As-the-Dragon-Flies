<?php

/** @var yii\web\View $this */
/** @var common\models\Player $player */
?>

<h4 class="card-title text-decoration">Traits</h4>
<?php foreach ($player->playerTraits as $playerTrait): ?>
    <p>
        <?= $playerTrait->trait->name ?>: <span class="text-muted"><?= $playerTrait->description ?></span>
    </p>
<?php endforeach;
