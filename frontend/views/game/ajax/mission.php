<?php

use common\components\NarrativeComponent;
use common\helpers\WebResourcesHelper;

/** @var yii\web\View $this */
/** @var common\models\Mission $mission */
$chapter = $mission->chapter;
$storyRoot = WebResourcesHelper::storyRootPath($chapter->story_id);
$narrative = new NarrativeComponent(['mission' => $mission, 'title' => false]);
$description = $narrative->renderDescription();
?>
<?php if ($mission->image): ?>
    <div class="clearfix">
        <img class="float-md-end mb-3 ms-md-4" src="<?= $storyRoot ?>/img/<?= $mission->image ?>" alt="<?= $mission->name ?>" style="max-width: 50%;">
        <?= $description ?>
    </div>
<?php else: ?>
    <?= $description ?>
<?php endif; ?>
