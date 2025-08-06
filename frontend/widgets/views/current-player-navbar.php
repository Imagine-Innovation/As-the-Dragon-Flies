<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $players */
/** @var int $selectedPlayerId */
/** @var int $user_id */
$ids = [];
$initials = [];
$tooltips = [];
foreach ($players as $player) {
    $ids[] = $player['id'];
    $initials[] = $player['initial'];
    $tooltips[] = $player['name'] . ', a ' . $player['tooltip'];
}
?>
<li class="dropdown top-nav__notifications">
    <a class="top-nav position-relative" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-file-earmark-person"></i>
        <div id="currentPlayerBadge"></div>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu--block">
        <div class="dropdown-header">
            Select your current Player

            <div class="actions">
                <a href="<?= Url::toRoute(['site/index']) ?>" role="button" class="actions__item bi-file-earmark-person"></a>
            </div>
        </div>

        <div class="listview listview--hover">
            <div class="card">
                <div class="card-body">

                    <?php foreach ($players as $player): ?>
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" class="custom-control-input"
                                   id="playerNav-<?= $player['id'] ?>" name="playerNav"
                                   <?= $player['id'] == $selectedPlayerId ? "checked" : "" ?>
                                   onclick="PlayerSelector.select(<?= $user_id ?>, <?= $player['id'] ?>);">
                            <label class="custom-control-label" for="playerNav-<?= $player['id'] ?>">
                                <span data-bs-toggle="tooltip" title="<?= ucfirst($player['tooltip']) ?>" data-placement="bottom">
                                    <?= $player['name'] ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" class="custom-control-input"
                               id="playerNav-void" name="playerNav"
                               <?= $selectedPlayerId ? "" : "checked" ?>
                               onclick="PlayerSelector.select(<?= $user_id ?>, null);">
                        <label class="custom-control-label" for="playerNav-void">Select no player</label>
                    </div>
                    <div class="d-none">
                        <span id="hiddenSelectedPlayerId"><?= $selectedPlayerId ?></span>
                        <span id="ids"><?= implode(";", $ids) ?></span>
                        <span id="initials"><?= implode(";", $initials) ?></span>
                        <span id="tooltips"><?= implode(";", $tooltips) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>
