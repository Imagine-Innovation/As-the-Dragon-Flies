<?php
/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
?>
<!-- Skills -->
<div class="card mb-4">
    <div class="<?= $cardHeaderClass ?>">
        <i class="bi bi-tools me-2"></i>Skills
    </div>
    <div class="card-body p-4">
        <table class="w-100">
            <?php foreach ($model->playerSkills as $playerSkill): ?>
                <?php if ($playerSkill->bonus <> 0): ?>
                    <tr>
                        <td><?= $playerSkill->skill->name ?></td>
                        <td class="text-center w-25">
                            <span class="badge bg-secondary w-75">
                                +<?= $playerSkill->bonus ?>
                                <?= $playerSkill->is_proficient ? '&nbsp;<i class="bi bi-shield-plus"></i>' : "" ?>
                            </span>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>
    </div>
</div>
