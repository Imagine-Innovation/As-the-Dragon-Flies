<?php

use common\helpers\WebResourcesHelper;

/** @var string|null $tag */
/** @var string|null $name */
/** @var string|null $param */
$imgPath = WebResourcesHelper::imagePath();
$tag = $tag ?? 'div';
?>
<<?= $tag ?> id="<?= $name ?>" <?= $param ?>>
<div class="text-center">
    <div class="spinner-border" role="status" style="width: 3rem; height: 3rem;">
        <span>
            <img src="<?= $imgPath ?>/Dragonfly32White.png" alt="">
        </span>
    </div>
</div>
</<?= $tag ?>>
