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

<?php if ($dialog->audio): ?>
    <audio id="npcLines" autoplay muted>
        <source src="<?= "resources/story-{$storyId}/audio/{$dialog->audio}" ?>" type="audio/mpeg">
    </audio>
<?php endif; ?>

<div class="text-decoration">
    <?php if ($npc->image): ?>
        <div class="clearfix">
            <img class="float-md-end mb-3 ms-md-4" src="resources/story-<?= $storyId ?>/img/<?= $npc->image ?>" alt="<?= $npc->name ?>" style="max-width: 50%;">
        <?php endif; ?>

        <h3><?= $npc->name ?></h3>
        <p class="text-muted"><?= nl2br(Html::encode($npc->description)) ?></p>
        <hr class="border border-warning border-1 opacity-50 w-50"><hr>
        <p><span class="text-warning"><?= Html::encode($playerName) ?></span> &mdash; <span class="text-muted"><?= nl2br(Html::encode($reply->text)) ?></span></p>
        <p><span class="text-warning"><?= Html::encode($npc->name) ?></span> &mdash; <span class="text-muted"><?= nl2br(Html::encode($dialog->text)) ?></span></p>
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
                'style' => 'align-bottom',
                'tooltip' => "Back to the mission",
                'onclick' => "vtt.evaluateAction(); return false;",
                'isCta' => true,
            ])
            ?>
        <?php endif; ?>

        <?php if ($npc->image): ?>
        </div>
    <?php endif; ?>
</div>
