<?php
/** @var yii\web\View $this */
/** @var common\models\Quest[] $activeQuests */
?>
<div class="card shadow-sm">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-journal-check me-2"></i>Active Quests</span>
        <button class="btn btn-sm btn-link text-decoration-none">View All</button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Quest</th>
                    <th>Progress</th>
                    <th>Initiator</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activeQuests)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">No active quests found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($activeQuests as $quest): ?>
                        <?php $progress = $quest->getProgress(); ?>
                        <tr>
                            <td><?= yii\helpers\Html::encode($quest->name) ?></td>
                            <td style="width: 40%;">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar <?= $progress < 30 ? 'bg-danger' : ($progress < 70 ? 'bg-warning' : 'bg-success') ?>"
                                             role="progressbar"
                                             style="width: <?= $progress ?>%"
                                             aria-valuenow="<?= $progress ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100"></div>
                                    </div>
                                    <span class="ms-2 small fw-bold"><?= $progress ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-person-fill me-1"></i>
                                    <?= yii\helpers\Html::encode($quest->initiator?->fullname ?? $quest->initiator?->username ?? 'Unknown') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
