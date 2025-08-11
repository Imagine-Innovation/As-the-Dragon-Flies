<?php

use frontend\components\PlayerComponent;

/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
$proficiencyBonus = $model->level->proficiency_bonus;

$playerAbilities = PlayerComponent::getAbilitiesAndSavingThrow($model->playerAbilities, $proficiencyBonus);

$combatStat = [
    ['label' => 'AC', 'value' => $model->armor_class],
    ['label' => 'Speed', 'value' => ($model->speed ?? $model->race->speed) . " ft"],
    ['label' => 'Prof. Bonus', 'value' => "+{$proficiencyBonus}"],
    ['label' => 'Hit Dice', 'value' => $model->class->hit_die],
];
?>
<section class="card mb-4">
    <div class="<?= $cardHeaderClass ?>">
        <i class="bi dnd-d20 me-2"></i>Abilities & Saving Throws
    </div>
    <article class="card-body p-4">
        <table class="w-100">
            <thead>
                <tr>
                    <th class="w-25">Ability</th>
                    <th class="text-center w-25">Score</th>
                    <th class="text-center w-25">Bonus</th>
                    <th class="text-center w-25">Saving Throw</th>
                </tr>
            </thead>
            <?php foreach ($playerAbilities as $playerAbility): ?>
                <tr>
                    <td class="text-left"><?= $playerAbility['code'] ?></td>
                    <td class="text-center"><?= $playerAbility['score'] ?></td>
                    <td class="text-center">
                        <?php if ($playerAbility['modifier']): ?>
                            <span class="badge bg-danger w-75">
                                <?= $playerAbility['modifier'] >= 0 ? "+{$playerAbility['modifier']}" : $playerAbility['modifier'] ?>
                            </span>
                        <?php else: ?>
                            &nbsp;
                        <?php endif ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary w-75">
                            <?= $playerAbility['savingThrow'] >= 0 ? "+{$playerAbility['savingThrow']}" : $playerAbility['savingThrow'] ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </article>
</section>

<section class="card">
    <div class="<?= $cardHeaderClass ?>">
        <i class="bi bi-shield-shaded me-2"></i>Combat Stats
    </div>
    <div class="card-body">
        <div class="row row-cols-3 row-cols-sm-4 row-cols-md-3 row-cols-xl-4">
            <?php foreach ($combatStat as $stat): ?>
                <article class="col">
                    <div class="card text-center">
                        <div class="card-body p-2">
                            <?= $stat['label'] ?><br>
                            <?= $stat['value'] ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p>Hit points</p>
        <article class="progress" role="progressbar" aria-label="Hit points"
                 aria-valuenow="<?= $model->hit_points ?>" aria-valuemin="0" aria-valuemax="<?= $model->max_hit_points ?>">
            <div class="progress-bar text-bg-warning"
                 style="width: <?= intval(($model->hit_points ?? 0) / ($model->max_hit_points ?? 1) * 100) ?>%">
                <?= $model->hit_points ?>/<?= $model->max_hit_points ?>
            </div>
        </article>
    </div>
</section>
