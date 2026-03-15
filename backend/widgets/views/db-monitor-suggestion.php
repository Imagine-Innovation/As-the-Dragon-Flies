<?php
/**
 * @var string[] $suggestions
 */
?>
<?php if ($suggestions === []): ?>
    <div class="text-secondary">No suggestions.</div>
<?php else: ?>
    <ul class="list-group">
        <?php foreach ($suggestions as $s): ?>
            <li class="list-group-item"><?= htmlspecialchars($s) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
