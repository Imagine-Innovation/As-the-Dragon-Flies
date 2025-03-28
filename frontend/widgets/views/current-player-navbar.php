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
    <a class="top-nav position-relative" href="#" data-toggle="dropdown">
        <i class="bi bi-file-earmark-person"></i>
        <div id="currentPlayerBadge"></div>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu--block">
        <div class="dropdown-header">
            Select your current Player

            <div class="actions">
                <a class="actions__item bi-file-earmark-person" href="<?= Url::toRoute(['site/index']) ?>"></a>
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
                                <span data-toggle="tooltip" title="<?= ucfirst($player['tooltip']) ?>" data-placement="bottom">
                                    <?= $player['name'] ?>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" class="custom-control-input"
                               id="voidPlayerNav" name="playerNav"
                               <?= $selectedPlayerId ? "" : "checked" ?>
                               onclick="PlayerSelector.select(<?= $user_id ?>, null);">
                        <label class="custom-control-label" for="voidPlayerNav">
                            Select no player
                        </label>
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

<script type="text/javascript">
    $(document).ready(function () {
        const ids_str = $("#ids").html();
        PlayerSelector.ids = ids_str.split(';');

        const initials_str = $("#initials").html();
        PlayerSelector.initials = initials_str.split(';');

        const tooltips_str = $("#tooltips").html();
        PlayerSelector.tooltips = tooltips_str.split(';');

        const player_id = $('#hiddenSelectedPlayerId').html();
        const playerIds = PlayerSelector.ids;
        const id = playerIds.indexOf(String(player_id));
        PlayerSelector.setBadge(id);
    });
</script>
