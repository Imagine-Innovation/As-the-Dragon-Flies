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
    <p class="small text-secondary">Explain Plan</p>
    <div class="bg-dark text-light p-3">
        <?= $tree ?>
    </div>
</div>
