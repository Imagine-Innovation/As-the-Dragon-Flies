<?php
/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
/** @var int $proficiencyBonus */
?>
<!-- Skills -->
<section class="card mb-4">
    <div class="<?= $cardHeaderClass ?>">
        <i class="bi bi-tools me-2"></i>Skills
    </div>
    <article class="card-body p-4">
        <div class="vertical-columns">
            <?php foreach ($model->playerSkills as $playerSkill): ?>
                <div><?= $playerSkill->skill->name ?><?= $playerSkill->is_proficient ? " (+{$proficiencyBonus})" : '' ?></div>
            <?php endforeach; ?>
        </div>
    </article>
</section>
