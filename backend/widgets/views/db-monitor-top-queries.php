<?php

use backend\models\DbMonitor;

/**
 * @var array<int, DbMonitor> $queries
 */
?>
<table class="table table-hover table-striped align-middle">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Avg Runtime (ms)</th>
            <th>SQL query</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($queries as $q): ?>
            <tr onclick="loadExplain(<?= (int) $q->id ?>)" style="cursor:pointer;">
                <td><?= (int) $q->id ?></td>
                <td><?= (int) $q->avg_runtime_ms ?></td>
                <td><?= htmlspecialchars($q->sql_text) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
