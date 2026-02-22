<?php
/** @var yii\web\View $this */
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
                <tr>
                    <td>The Lost Mine of Phandelver</td>
                    <td>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 75%"></div>
                        </div>
                    </td>
                    <td><span class="badge bg-secondary">Gundren R.</span></td>
                </tr>
                <tr>
                    <td>Slaying the Cryovain</td>
                    <td>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: 15%"></div>
                        </div>
                    </td>
                    <td><span class="badge bg-secondary">Townmaster Harbin</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
