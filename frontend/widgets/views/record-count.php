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
                <a href="<?= $action['action'] ?>"<?= $tooltip ?> role="button" class="actions__item <?= $action['icon'] ?>"></a>
            <?php else: ?>
                <a href="#" role="button" class="actions__item <?= $action['icon'] ?>" <?= $action['trigger'] ?>="<?= $action['action'] ?>"<?= $tooltip ?>></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
