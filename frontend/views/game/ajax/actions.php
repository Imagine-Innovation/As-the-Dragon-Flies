<?php
/** @var yii\web\View $this */
/** @var common\models\QuestAction $questActions */
?>

<div class="card">
    <div class="card-header">
        What do want to do?
    </div>
    <div class="card-body">
        <ol>
            <?php
            foreach ($questActions as $questAction):
                $onclick = $questAction->action->reply_id ?
                        "vtt.talk({$questAction->action_id}, {$questAction->action->reply_id}); return false;" :
                        "vtt.makeAction({$questAction->action_id}); return false;";
                ?>
                <li><a href="" onclick="<?= $onclick ?>"><?= $questAction->action->name ?></a></li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>
