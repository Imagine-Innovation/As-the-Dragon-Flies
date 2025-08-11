<?php

use frontend\widgets\Button;

/** @var string $countLabel */
/** @var array() $actions action buttons associated with the context */
?>
<p class="text-muted"><?= $countLabel ?></p>
<?php if (isset($actions)): ?>
    <div class="actions">
        <!-- Record Count -->
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
