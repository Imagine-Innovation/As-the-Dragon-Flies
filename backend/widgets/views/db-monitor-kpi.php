<?php
/**
 * @var string $label
 * @var int|string $value
 */
?>
<div class="col-md-3 mb-3">
    <div class="card text-bg-dark shadow-sm">
        <div class="card-body">
            <h6 class="text-uppercase small mb-1"><?= htmlspecialchars($label) ?></h6>
            <h2 class="mb-0"><?= htmlspecialchars((string) $value) ?></h2>
        </div>
    </div>
</div>
