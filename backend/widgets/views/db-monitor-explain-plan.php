<?php
/**
 * @var string $sql
 * @var string $tree
 * @var int $queryId
 */
?>
<div class="mb-3">
    <div class="small text-secondary">Query</div>
    <pre class="mb-0" style="white-space:pre-wrap;"><?= htmlspecialchars($sql) ?></pre>
</div>

<div class="mb-3">
    <div class="small text-secondary">Explain Plan</div>
    <pre class="bg-dark text-light p-3" style="white-space:pre-wrap;">
        <?= htmlspecialchars($tree) ?>
    </pre>
</div>

<div class="text-end">
    <button class="btn btn-warning" onclick="loadSuggestion(<?= (int) $queryId ?>)">
        Improve Query
    </button>
</div>
