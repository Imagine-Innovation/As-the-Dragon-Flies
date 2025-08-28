<?php

use frontend\components\PlayerComponent;

/** @var yii\web\View $this */
/** @var common\models\Player $player */
$avatar = Yii::$app->session->get('avatar');
$proficiencyBonus = $player->level->proficiency_bonus;

$playerAbilities = PlayerComponent::getAbilitiesAndSavingThrow($player->playerAbilities, $proficiencyBonus);
?>
<div class="m-3">
    <!-- Character Info -->
    <header class="text-center">
        <img src="img/characters/<?= $avatar ?>" alt="Avatar" class="avatar my-2">
        <h6 class="text-warning text-decoration"><?= $player->name ?></h6>
        <p class="text-muted small"><?= $player->level->name ?> <?= $player->class->name ?> <?= $player->race->name ?></p>
    </header>

    <!-- Health -->
    <div id="game-player-health">
        <?= $this->renderFile('@app/views/game/ajax/player-health.php', ['player' => $player]) ?>
    </div>

    <!-- Stats -->
    <div id="game-player-abilities">
        <h6 class="text-warning mt-4">Abilities</h6>

        <div class="row g-2">
            <?php foreach ($playerAbilities as $playerAbility): ?>
                <div class="col-4">
                    <?= $playerAbility['code'] ?> <?= $playerAbility['score'] ?>
                    <?= $playerAbility['modifier'] ? ($playerAbility['modifier'] > 0 ? "(+{$playerAbility['modifier']})" : "({$playerAbility['modifier']})") : "" ?>
                </div>
            <?php endforeach; ?>
            <div class="col-12 g-2 mb-2">Armor Class <?= $player->armor_class ?></div>
        </div>
    </div>
</div>
