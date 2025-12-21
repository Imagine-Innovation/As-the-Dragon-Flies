<?php

use common\helpers\Utilities;

/** @var yii\web\View $this */
/** @var frontend\models\PlayerBuilder $model */
/** @var string[] $paragraphs */
?>
<!-- Character Builder - Images Tab -->
<?= Utilities::formatMultiLine($paragraphs) ?>
<div class="container">
    <div class="card">
        <div class="card-body" id="ajaxAvatarChoice">
            <h4 class="card-title text-decoration">Images</h4>
            <h6 class="card-subtitle">Please select a race, a class and a gender before choosing an image.</h6>
        </div>
    </div>
</div>
