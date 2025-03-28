<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
?>
<div class="modal fade" id="selectPlayerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Back in a moment</h6>
                <div class="d-none">
                    <span id="hiddenSelectedPlayerId"></span>
                    <span id="ids"></span>
                    <span id="initials"></span>
                    <span id="captions"></span>
                </div>
            </div>
            <div class="modal-body">
                <p class="text-muted">Access to the store is reserved for players who are in "Active" status.</p>
                <p class="text-muted">It looks like you don't have any active players yet.</p>
                <p class="text-muted">I invite you to use the Character Builder to create one, 
                    or go to the list of players already created to validate their creation 
                    and set them to active status.</p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-theme" href="<?= Url::toRoute('player/builder') ?>" role="button">
                    <i class="bi-tools"></i>&nbsp;&nbsp;&nbsp;Player Builder
                </a>
                <a class="btn btn-theme" href="<?= Url::toRoute('player/index') ?>" role="button">
                    <i class="bi-person-raised-hand"></i></i>&nbsp;&nbsp;&nbsp;Player list
                </a>
            </div>
        </div>
    </div>
</div>
