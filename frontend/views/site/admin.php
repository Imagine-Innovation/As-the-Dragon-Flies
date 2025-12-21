<?php

use frontend\widgets\ToolMenu;

/** @var yii\web\View $this */

$currentUser = Yii::$app->user->identity;
$playerName = Yii::$app->session->get('playerName');
$userName = $currentUser->fullname ?? $currentUser->username;

$isAdmin = $currentUser->is_admin;
?>
<?php if ($isAdmin || true): ?>
    <header class="content__title h3">
        <p>
            Welcome back <span class="text-decoration"><?= $playerName ?? $playerName ?></span>
        </p>
    </header>
    <h5>What would you like to do?</h5>
    <div class="container">
        <?= ToolMenu::widget(['mode' => 'lobby']) ?>
    </div>
<?php else: ?>

<?php endif; ?>
