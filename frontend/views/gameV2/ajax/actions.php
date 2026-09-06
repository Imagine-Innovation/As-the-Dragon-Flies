<?php
/** @var common\models\QuestAction[] $questActions */
?>
<section class="actions-panel" aria-labelledby="actions-title">
    <p class="panel-heading" id="actions-title"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Actions disponibles</p>
    <div class="actions-panel__grid">
        <?php
        foreach ($questActions as $questAction):
            $action = $questAction->action;
            $onclick = $action->reply_id ?
                    "vtt.talk({$action->id}, {$action->reply_id}); return false;" :
                    "vtt.evaluateAction({$action->id}); return false;";
            ?>
            <button type="button" class="action-btn" onclick="<?= $onclick ?>">
                <span class="action-btn__top">
                    <span class="action-btn__name">
                        <?php if ($action->actionType?->icon): ?>
                            <i class="bi <?= $action->actionType->icon ?>" aria-hidden="true"></i>
                        <?php endif; ?>
                        <?= $action->name ?>
                    </span>
                    <span class="action-btn__badges">
                        <?php if ($action->is_free): ?>
                            <span class="badge badge-success">Gratuite</span>
                        <?php endif; ?>
                        <?php if ($action->dc > 0): ?>
                            <span class="badge badge-warning">DC <?= $action->dc ?></span>
                        <?php endif; ?>
                    </span>
                </span>
                <span class="action-btn__desc">
                    <?=
                    MarkDown::widget([
                        'content' => $action->description,
                        'placeholders' => [
                            'playerName' => $currentPlayer->name,
                        ],
                    ])
                    ?>
                </span>
            </button>
        <?php endforeach; ?>
    </div>
</section>
