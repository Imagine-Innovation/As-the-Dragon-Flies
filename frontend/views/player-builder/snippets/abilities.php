<?php

use frontend\widgets\AbilityChart;
use common\helpers\Utilities;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
/** @var string[] $paragraphs */
$playerAbilities = $model->playerAbilities;
$abilityValues = [15, 14, 13, 12, 10, 8];
?>
<!--Character Builder - Abilities Tab-->
<?= Utilities::formatMultiLine($paragraphs) ?>
<div class="container-fluid">
    <div class="card h-100">
        <div class="actions">
            <a href="#" role="button" class="actions__item bi bi-eraser" onclick="PlayerBuilder.clearAbilities();"></a>
        </div>
        <div class="card-body">
            <table class="table table-dark mb-0">
                <thead>
                    <tr>
                        <th>Score</th>
                        <?php foreach ($playerAbilities as $playerAbility): ?>
                            <th class="text-center">
                                <?=
                                AbilityChart::widget([
                                    'id' => $playerAbility->ability_id,
                                    'score' => $playerAbility->score,
                                    'code' => $playerAbility->ability->code,
                                    'bonus' => $playerAbility->bonus,
                                ]);
                                ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($abilityValues as $val): ?>
                        <tr>
                            <th scope="row" class="text-center"><?= $val ?></th>
                            <?php
                            foreach ($playerAbilities as $playerAbility):
                                $id = $playerAbility->ability_id;
                                $score = $playerAbility->score;
                                ?>
                                <td class="text-center">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="abilityRadio-<?= $id ?>-<?= $val ?>" name="ability-<?= $id ?>"
                                               value="<?= $val ?>" class="custom-control-input score<?= $val ?>"
                                               <?= $score == $val ? "checked" : "" ?>
                                               onclick='PlayerBuilder.checkAbility(<?= $id ?>, <?= $val ?>);'>
                                        <label class="custom-control-label" for="abilityRadio-<?= $id ?>-<?= $val ?>"></label>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row" class="text-center">Bonus</th>
                        <?php foreach ($playerAbilities as $playerAbility): ?>
                            <td class="text-center">
                                <?= $playerAbility->bonus > 0 ? "+" . $playerAbility->bonus : "&nbsp;" ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
