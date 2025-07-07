<?php
/** @var string $countLabel */
/** @var array() $actions action buttons associated with the context */
?>
<p class="text-muted"><?= $countLabel ?></p>
<?php if (isset($actions)): ?>
    <div class="actions">
        <?php foreach ($actions as $action): ?>
            <?php
            $tooltipTxt = $action['tooltip'];
            $tooltip = $tooltipTxt ? ' data-bs-toggle="tooltip" title="' . $tooltipTxt . '" data-placement="bottom"' : "";
            ?>
            <?php if ($action['trigger'] == "href"): ?>
                <a class="actions__item <?= $action['icon'] ?>" href="<?= $action['action'] ?>"<?= $tooltip ?>></a>
            <?php else: ?>
                <a class="actions__item <?= $action['icon'] ?>" href="#" <?= $action['trigger'] ?>="<?= $action['action'] ?>"<?= $tooltip ?>></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
