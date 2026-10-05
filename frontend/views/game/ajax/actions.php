<?php

/** @var yii\web\View $this */
/** @var common\models\QuestAction[] $questActions */
?>

<div class="card">
    <div class="card-body text-decoration">
        <p><?= Yii::t('app/game', 'What do you want to do?') ?></p>
        <ol>
            <?php

            foreach ($questActions as $questAction):
                $onclick = $questAction->action->reply_id
                    ? "vtt.talk({$questAction->action_id}, {$questAction->action->reply_id}); return false;"
                    : "vtt.evaluateAction({$questAction->action_id}); return false;";
                ?>
                <li><a href='' onclick="<?= $onclick ?>"><?= $questAction->action->name ?></a></li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>
