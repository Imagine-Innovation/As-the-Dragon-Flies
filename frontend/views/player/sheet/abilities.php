<?php

use frontend\components\PlayerTool;

/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
$proficiencyBonus = $model->level->proficiency_bonus;

$playerAbilities = PlayerTool::getAbilitiesAndSavingThrow($model->playerAbilities, $proficiencyBonus);
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
    </article>>
</section>
