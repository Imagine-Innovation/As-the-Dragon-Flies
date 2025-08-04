<?php

use frontend\helpers\ItemTool;

/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
?>
<!-- Attacks -->
<section class="card mb-4">
    <header class="<?= $cardHeaderClass ?>">
        <i class="bi dnd-action-attack me-2"></i>Attacks & Spells
    </header>
    <div class="card-body">
        <?php foreach ($model->playerItems as $playerItem): ?>
            <?php
            if ($playerItem->item_type === "Weapon"):
                $weapon = $playerItem->weapon;
                $properties = ItemTool::getLiteWeaponProperties($weapon);
                $remaining = ItemTool::getRemainingAmunitions($playerItem);
                ?>
                <article class="card mb-3">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold"><?= $weapon->item->name ?></h6>
                            <span class="badge btn-fantasy"><?= $playerItem->attack_modifier ?> to hit</span>
                        </div>
                        <small class="text-muted"><?= $playerItem->damage ?> <?= $weapon->damageType->name ?><?= $properties ? ", {$properties}" : "" ?></small>
                        <?php if ($remaining): ?>
                            <br><small class="text-muted"><?= $remaining ?></small>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
