<?php

use common\helpers\Utilities;
use common\widgets\MarkDown;

/** @var string $UUID */
/** @var string $description */
/** @var int $maxLength */
/** @var string $name */
/** @var string $style */
$shortDesc = Utilities::trim($description, $maxLength);
?>
<div class="container g-0 p-0">
    <a<?= $style ? " class=\"{$style}\"" : '' ?> data-bs-toggle="modal" data-bs-target="#modal-<?= $UUID ?>">
        <?= MarkDown::widget(['content' => $shortDesc]) ?>
    </a>
    <div class="modal fade" id="modal-<?= $UUID ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content <?= $style ?>">
                <?php if ($name): ?>
                    <div class="modal-header">
                        <h5 class="modal-title"><?= Utilities::encode($name) ?></h5>
                    </div>
                <?php endif; ?>
                <div class="modal-body">
                    <?= MarkDown::widget(['content' => $description]) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-theme btn--icon" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
