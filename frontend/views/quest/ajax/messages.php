<?php
/** @var yii\web\View $this */
/** @var array $messages[] */
?>
<?php if ($messages): ?>
    <?php foreach ($messages as $chatMessage): ?>
        <div class="messages__item<?= ($chatMessage['isAuthor']) ? ' messages__item--right' : '' ?>" id="quest-chat-<?= $chatMessage['roundedTime'] ?>">
            <div class="messages__details">
                <?php foreach ($chatMessage['messages'] as $chat): ?>
                    <p><?= $chat ?></p>
                <?php endforeach; ?>
                <small><?= $chatMessage['displayedDateTime'] ?> - <?= $chatMessage['sender'] ?></small>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="messages__item">
        <div class="messages__details">No message yet</div>
    </div>
<?php endif; ?>
