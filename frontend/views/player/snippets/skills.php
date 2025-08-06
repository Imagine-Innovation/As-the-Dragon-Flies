<?php
/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
?>
<!-- Skills -->
<section class="card mb-4">
    <div class="<?= $cardHeaderClass ?>">
        <i class="bi bi-tools me-2"></i>Skills
    </div>
    <article class="card-body p-4">
        <table class="w-100">
            <?php foreach ($model->playerSkills as $playerSkill): ?>
                <tr>
                    <td><?= $playerSkill->skill->name ?></td>
                    <td class="text-center w-25">
                        <span class="badge bg-secondary w-75">
                            +<?= $playerSkill->bonus ?>
                            <?= $playerSkill->is_proficient ? '&nbsp;<i class="bi bi-shield-plus"></i>' : "" ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </article>
</section>
