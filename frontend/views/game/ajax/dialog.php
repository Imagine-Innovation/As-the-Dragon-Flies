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
            <li><a href="" onclick="VirtualTableTop.nextDialog(<?= $reply->next_dialog_id ?>)"><?= Html::encode($reply->text) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php elseif ($dialog->outcome_id): ?>
    <?=
    Button::widget([
        'title' => "Next step",
        'icon' => 'dnd-d20',
        'tooltip' => "Evaluate your success",
        'onclick' => "VirtualTableTop.evaluateAction()",
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
