<?php

use common\helpers\WebResourcesHelper;
use common\helpers\Utilities;

/** @var yii\web\View $this */
/** @var common\models\Player[] $topPlayers */

$imgPath = WebResourcesHelper::imagePath();
?>
<div class="card shadow-sm">
    <div class="card-header bg-transparent border-bottom">
        <span class="fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>Top 10 Players</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>Player</th>
                    <th class="text-center">Quests</th>
                    <th class="text-center">Lvl</th>
                    <th>Class</th>
                    <th>Race</th>
                    <th class="text-end">XP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topPlayers as $player): ?>
                    <tr>
                        <td>
                            <img src="<?= $imgPath ?>/character/<?= $player->avatar ?>" class="image-thumbnail" style="width: 32px; height: 32px; object-fit: cover;">
                        </td>
                        <td><strong><?= Utilities::encode($player->name ?? '') ?></strong></td>
                        <td class="text-center"><span class="badge bg-info text-dark"><?= $player->quest_count ?></span></td>
                        <td class="text-center"><?= Utilities::encode($player->level->name ?? 'Unknown') ?></td>
                        <td><small><?= Utilities::encode($player->class->name ?? 'Unknown') ?></small></td>
                        <td><small><?= Utilities::encode($player->race->name ?? 'Unknown') ?></small></td>
                        <td class="text-end"><span class="text-success"><?= number_format($player->experience_points) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
