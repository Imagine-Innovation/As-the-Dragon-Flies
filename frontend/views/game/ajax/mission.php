<?php

/** @var yii\web\View $this */
/** @var common\models\Mission $mission */
$chapter = $mission->chapter;
?>
<?php if ($mission->image): ?>
    <div class="clearfix">
        <img class="float-md-end mb-3 ms-md-4" src="resources/story-<?= $chapter->story_id ?>/img/<?= $mission->image ?>" alt="<?=
    $mission->name
?>" style="max-width: 50%;">
        <?= $mission->description ?>
    </div>
<?php else: ?>
    <?= $mission->description ?>
<?php endif;
