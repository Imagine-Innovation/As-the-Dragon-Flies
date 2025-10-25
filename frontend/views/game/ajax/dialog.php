<?php

use frontend\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $storyId */
/** @var string $playerName */
/** @var common\models\Reply $reply */
/** @var common\models\Dialog $dialog */
$npc = $dialog->npc;
?>

<?php if ($npc->image): ?>
    <div class="clearfix">
        <img class="float-md-end mb-3 ms-md-4" src="img/story/<?= $storyId ?>/<?= $npc->image ?>" alt="<?= $npc->name ?>" style="max-width: 50%;">
    <?php endif; ?>

    <p class="text-decoration"><span class="text-warning"><?= Html::encode($playerName) ?></span> &mdash; <?= nl2br(Html::encode($reply->text)) ?></p>
    <p class="text-decoration"><span class="text-warning"><?= Html::encode($npc->name) ?></span> &mdash; <?= nl2br(Html::encode($dialog->text)) ?></p>
    <?php if ($dialog->replies): ?>
        <ul>
            <?php foreach ($dialog->replies as $reply): ?>
                <li><a href="#" onclick="vtt.reply(<?= $reply->id ?>); return false;"><?= nl2br(Html::encode($reply->text)) ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <?=
        Button::widget([
            'title' => "Next step",
            'icon' => 'dnd-d20',
            'tooltip' => "Back to the mission",
            'onclick' => "vtt.evaluateAction(); return false;",
            'isCta' => true,
        ])
        ?>
    <?php endif; ?>

    <?php if ($npc->image): ?>
    </div>
<?php endif; ?>
