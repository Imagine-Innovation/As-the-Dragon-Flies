<?php

use frontend\components\PlayerComponent;

/** @var yii\web\View $this */
/** @var common\models\Player $player */
$avatar = Yii::$app->session->get('avatar');
$proficiencyBonus = $player->level->proficiency_bonus;

$playerAbilities = PlayerComponent::getAbilitiesAndSavingThrow($player->playerAbilities, $proficiencyBonus);
?>
<!-- Character Info -->
<section class="mb-3">
    <header class="text-center m-3">
        <img src="img/characters/<?= $avatar ?>" alt="Avatar" class="avatar mb-2">
        <h6 class="text-warning text-decoration"><?= $player->name ?></h6>
        <p class="text-muted small"><?= $player->level->name ?> <?= $player->class->name ?> <?= $player->race->name ?></p>
    </header>

    <!-- Health -->
    <p class="mx-3">Health</p>
    <div class="progress mx-3" role="progressbar" aria-label="Hit points"
         aria-valuenow="<?= $player->hit_points ?>" aria-valuemin="0" aria-valuemax="<?= $player->max_hit_points ?>">
        <div class="progress-bar text-bg-warning"
             style="width: <?= intval(($player->hit_points ?? 0) / ($player->max_hit_points ?? 1) * 100) ?>%">
            <?= $player->hit_points ?>/<?= $player->max_hit_points ?>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="card mb-3">
    <h6 class="text-warning m-3">Abilities</h6>

    <div class="row mx-3">
        <?php foreach ($playerAbilities as $playerAbility): ?>
            <div class="col-2 col-sm-4 col-lg-6 col-xl-4 g-2">
                <?= $playerAbility['code'] ?> <?= $playerAbility['score'] ?>
                <?= $playerAbility['modifier'] ? ($playerAbility['modifier'] > 0 ? "(+{$playerAbility['modifier']})" : "({$playerAbility['modifier']})") : "" ?>
            </div>
        <?php endforeach; ?>
        <div class="col-12 g-2 mb-2">Armor Class <?= $player->armor_class ?></div>
    </div>
</section>
