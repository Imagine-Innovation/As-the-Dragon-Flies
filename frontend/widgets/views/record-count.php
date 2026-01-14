<?php

use frontend\widgets\Button;

/** @var string $countLabel */
/** @var array<string, array{url: string, icon: string, tooltip: string}>|null $actions action buttons associated with the context */
?>
<p class="text-muted"><?= $countLabel ?></p>
<?php if ($actions !== null && !empty($actions)): ?>
    <div class="actions">
        <?php foreach ($actions as $action): ?>
            <?=
            Button::widget([
                'mode' => 'icon',
                'url' => $action['url'],
                'icon' => $action['icon'],
                'tooltip' => $action['tooltip'],
            ])
            ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
