<?php

use common\helpers\WebResourcesHelper;
use common\widgets\MarkDown;

/** @var yii\web\View $this */
/** @var common\models\Mission $mission */
$chapter = $mission->chapter;
$storyRoot = WebResourcesHelper::storyRootPath($chapter->story_id);
?>
<?php if ($mission->image): ?>
    <div class="clearfix">
        <img class="float-md-end mb-3 ms-md-4" src="<?= $storyRoot ?>/img/<?= $mission->image ?>" alt="<?= $mission->name ?>" style="max-width: 50%;">
        <?= MarkDown::widget(['content' => $mission->description]) ?>
    </div>
<?php else: ?>
    <?= MarkDown::widget(['content' => $mission->description]) ?>
<?php endif;
