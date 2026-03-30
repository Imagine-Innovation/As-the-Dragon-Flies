<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
/** @var bool $hasSpell */
/** @var array<int, string> $proficiencyHeaders */
/** @var array<int, array<int, array{value: string, is_header: bool}>> $proficiencies */
?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <?php foreach ($proficiencyHeaders as $header): ?>
                            <th class="text-center"><?= $header ?></th>
                        <?php endforeach; ?>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proficiencies as $proficiencyLevel): ?>
                        <tr>
                            <?php foreach ($proficiencyLevel as $level => $proficiency): ?>
                                <td class="text-center"<?= $proficiency['is_header'] ? ' scope="row"' : '' ?>>
                                    <?= $proficiency['value'] === '' ? '&nbsp;' : Html::encode($proficiency['value']) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
