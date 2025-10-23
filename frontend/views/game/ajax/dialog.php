<?php

use frontend\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Dialog $dialog */
?>
<p><?= Html::encode($dialog->text) ?></p>
<?php if ($dialog->replies): ?>
    <ul>
        <?php foreach ($dialog->replies as $reply): ?>
            <li><a href="" onclick="vtt.nextDialog(<?= $reply->next_dialog_id ?>); return false;"><?= Html::encode($reply->text) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php elseif ($dialog->outcome_id): ?>
    <?=
    Button::widget([
        'title' => "Next step",
        'icon' => 'dnd-d20',
        'tooltip' => "Evaluate your success",
        'onclick' => "vtt.evaluateAction()",
        'isCta' => true,
    ])
    ?>
<?php else: ?>
    <?=
    Button::widget([
        'title' => "Close",
        'tooltip' => "Back to the mission",
        'onclick' => "",
        'isCta' => true,
    ])
    ?>
<?php endif; ?>
