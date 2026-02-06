<?php

/** @var yii\web\View $this */
/** @var frontend\models\PlayerBuilder $model */
/** @var string $embedded */
$colSplit = $embedded ? 'col-4' : 'col-4 col-md-2 col-lg-1';
?>
<div class="card">
    <div class="card-body">
        <div class="row g-2">
            <?php foreach ($model->playerAbilities as $playerAbility): ?>
                <div class="<?= $colSplit ?>">
                    <p><?= $playerAbility->ability->code ?></p>
                    <p>
                        <span class="badge badge-danger">
                            <?= $playerAbility->score ?><?= $playerAbility->bonus >= 0 ? '+' : '-' ?><?=
                $playerAbility->bonus
            ?>
                        </span>
                    </p>
                </div>
            <?php endforeach; ?>

            <!-- Combat Stats: Last 6 columns -->
            <div class="<?= $colSplit ?>">
                <p>Speed</p>
                <p>
                    <span class="badge badge-danger"><?= $model->race->speed ?></span>
                </p>
            </div>

            <div class="<?= $colSplit ?>">
                <p>XP</p>
                <p>
                    <span class="badge badge-danger"><?= $model->experience_points ?></span>
                </p>
            </div>

            <div class="<?= $colSplit ?>">
                <p>HD</p>
                <p>
                    <span class="badge badge-danger"><?= $model->class->hit_die ?></span>
                </p>
            </div>

            <?php if (!$embedded): ?>
                <div class="<?= $colSplit ?>">
                    <p>AC</p>
                    <p>
                        <span class="badge badge-danger"><?= $model->armor_class ?></span>
                    </p>
                </div>

                <div class="<?= $colSplit ?>">
                    <p>HP</p>
                    <p>
                        <span class="badge badge-danger"><?= $model->hit_points ?></span>
                    </p>
                </div>

                <div class="<?= $colSplit ?>">
                    <p>HP max</p>
                    <p>
                        <span class="badge badge-danger"><?= $model->max_hit_points ?></span>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
