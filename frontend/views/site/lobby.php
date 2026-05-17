<?php
/** @var yii\web\View $this */
/** @var array $viewParameters */
/** @var int $state */
$this->title = 'Game lobby';

$user = Yii::$app->user->identity;
$player = $viewParameters['player'];

$snippet = "snippets/_state_{$state}";

$rowCss = 'row d-flex justify-content-center g-3';
$colCss = 'col-12 col-lg-9 col-xl-8 col-xxl-7';
$viewParameters['row'] = $rowCss;
$viewParameters['col'] = $colCss;
$userName = $user->fullname ?? $user->username;
$welcomed = $state < 2 ? $userName : ($player !== null ? $player->name : $userName);
?>

<div class="<?= $rowCss ?>">
    <div class="<?= $colCss ?>">
        <header class="content__title h3 text-decoration">
            Welcome back <?= $welcomed ?>
        </header>
    </div>
    <?= $this->render($snippet, $viewParameters) ?>
</div>
