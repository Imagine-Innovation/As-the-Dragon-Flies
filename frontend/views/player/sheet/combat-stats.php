<?php
/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
$proficiencyBonus = $model->level->proficiency_bonus;

$combatStat = [
    ['label' => 'AC', 'value' => $model->armor_class],
    ['label' => 'Speed', 'value' => ($model->speed ?? $model->race->speed) . " ft"],
    ['label' => 'Prof. Bonus', 'value' => "+{$proficiencyBonus}"],
    ['label' => 'Hit Dice', 'value' => $model->class->hit_die],
];
?>
<!--Combat Stats-->
<div class="card">
    <div class="<?= $cardHeaderClass ?>">
        <i class="fas fa-shield-alt me-2"></i>Combat Stats
    </div>
    <div class="card-body">
        <div class="row row-cols-3 row-cols-sm-4 row-cols-md-3 row-cols-xl-4">
            <?php foreach ($combatStat as $stat): ?>
                <div class="col">
                    <div class="card text-center">
                        <div class="card-body p-2">
                            <?= $stat['label'] ?><br>
                            <?= $stat['value'] ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p>Hit points</p>
        <div class="progress" role="progressbar" aria-label="Hit points"
             aria-valuenow="<?= $model->hit_points ?>" aria-valuemin="0" aria-valuemax="<?= $model->max_hit_points ?>">
            <div class="progress-bar text-bg-warning"
                 style="width: <?= intval(($model->hit_points ?? 0) / ($model->max_hit_points ?? 1) * 100) ?>%">
                <?= $model->hit_points ?>/<?= $model->max_hit_points ?>
            </div>
        </div>
    </div>
</div>
