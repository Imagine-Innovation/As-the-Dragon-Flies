<?php
/** @var yii\web\View $this */
/** @var array $viewParameters */
/** @var int $state */
$this->title = 'Game lobby';

$user = Yii::$app->user->identity;
$player = $viewParameters['player'];

$snippet = "snippets/_state_{$state}";

$row = "row d-flex justify-content-center g-3";
$col = "col-12 col-lg-9 col-xl-8 col-xxl-7";
$viewParameters['row'] = $row;
$viewParameters['col'] = $col;
?>

<?php if ($user->is_admin): ?>
    <?= $this->render('admin') ?>
<?php else: ?>
    <div class="<?= $row ?>">
        <div class="<?= $col ?>">
            <header class="content__title h3 text-decoration">
                Welcome back <?= $state < 2 ? ($user->fullname ?? $user->username) : $player->name ?>
            </header>
        </div>
        <?= $this->render($snippet, $viewParameters) ?>
    </div>
<?php endif; ?>
