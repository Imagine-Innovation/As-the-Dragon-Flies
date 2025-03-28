<?php
/** @var yii\web\View $this */
/** @var array $messages[] */
?>
<?php foreach ($messages as $chat): ?>
    <div class="messages__item<?= ($chat['is_author']) ? ' messages__item--right' : '' ?>" id="<?= $chat['div_id'] ?>">
        <?php if (!$chat['is_author']): ?>
            <img src="<?= $chat['avatar'] ?>" class="avatar-img" alt="">
        <?php endif; ?>
        <div class="messages__details">
            <?= implode("", $chat['messages']) ?>
            <small><i class="bi-clock"></i> <?= $chat['date_time'] ?> - <?= $chat['sender'] ?></small>
        </div>
    </div>
<?php endforeach; ?>
