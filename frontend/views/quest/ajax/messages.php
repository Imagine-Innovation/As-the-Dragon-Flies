<?php

/** @var yii\web\View $this */
/** @var array<'message'|int<0, max>, string> $messages[] */
?>
<?php if (empty($messages)): ?>
    <div class="messages__item">
        <div class="messages__details">No message yet</div>
    </div>
<?php else: ?>
    <?php

    foreach ($messages as $chatMessage): /** @var array{isAuthor: int, roundedTime: int, messages: array<string>, displayedDateTime: string, sender: string} $chatMessage */
    ?>
        <div class="messages__item<?= $chatMessage['isAuthor'] ? ' messages__item--right' : '' ?>" id="quest-chat-<?=
        $chatMessage['roundedTime']
    ?>">
            <div class="messages__details">
                <?php foreach ($chatMessage['messages'] as $chat): ?>
                    <p><?= $chat ?></p>
                <?php endforeach; ?>
                <small><?= $chatMessage['displayedDateTime'] ?> - <?= $chatMessage['sender'] ?></small>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif;
