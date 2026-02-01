<?php

use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var array $players */
/** @var int $selectedPlayerId */
/** @var int $userId */
$ids = [];
$initials = [];
foreach ($players as $player) {
    $ids[] = $player['id'];
    $initials[] = $player['initial'];
}
?>

<div class="modal fade" id="selectPlayerModal" data-bs-keyboard="false" data-bs-backdrop="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select your current player</h5>
            </div>
            <div class="modal-body">
                <p>Before going shopping, please select one of the following players</p>

                <?php foreach ($players as $player): ?>
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" class="custom-control-input"
                               id="selectPlayerModal-<?= $player['id'] ?>" name="selectPlayerModal"
                               <?=
                               ($player['id'] === $selectedPlayerId) ? "checked"
                                           : ''
                               ?>
                               onclick="PlayerSelector.select(<?= $userId ?>, <?= $player['id'] ?>); $('#CloseSelectPlayerModal-button').click();">
                        <label class="custom-control-label" for="selectPlayerModal-<?= $player['id'] ?>">
                            <span data-bs-toggle="tooltip" title="<?= ucfirst($player['tooltip']) ?>" data-placement="bottom">
                                <?= Html::encode($player['name']) ?>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" id="CloseSelectPlayerModal-button" class="btn btn-theme btn--icon" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>
