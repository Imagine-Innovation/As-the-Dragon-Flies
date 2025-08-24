<?php

use frontend\widgets\Button;
?>
<style>
    .equipment-modal-body {
        height: 70vh; /* or any fixed height you prefer */
        padding: 0;
    }

    .equipment-modal-body .col-sm-6 {
        height: 100%;
        padding: 10px;
    }

    .equipment-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .svg-container {
        overflow: hidden;
    }

    .svg-container svg {
        width: 100%;
        height: auto;
        max-height: 100%;
    }

</style>

<div class="modal fade" id="equipmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-custom-size">
        <div class="modal-content">
            <div class="equipment-modal-body">
                <div class="row h-100">
                    <div class="col-sm-6">
                        <div class="equipment-card">
                            <div class="card-body svg-container">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 918" preserveAspectRatio="xMidYMid meet" id="equipmentSvg">
                                    <image href="/frontend/web/img/simpleman3.png" width="400" height="918" class="img-fluid" />

                                    <!-- Head -->
                                    <circle id="equipmentHeadZone" cx="200" cy="90" r="90" fill="white" fill-opacity="0.5" />

                                    <!-- Chest -->
                                    <circle id="equipmentChestZone" cx="200" cy="330" r="120" fill="white" fill-opacity="0.5" />

                                    <!-- Right Hand -->
                                    <circle id="equipmentRightHandZone" cx="70" cy="520" r="70" fill="white" fill-opacity="0.5" />

                                    <!-- Left Hand -->
                                    <circle id="equipmentLeftHandZone" cx="330" cy="520" r="70" fill="white" fill-opacity="0.5" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="equipment-card">
                            <div class="card-header">
                                <p class="lead text-decoration">List a available equipment</p>
                            </div>
                            <div id="packageContent" class="card-body" style="height: 80%; overflow: auto;">
                                <p class="text-decoration">You have nothing at all</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?=
                Button::widget([
                    'icon' => 'bi-floppy',
                    'title' => 'Save and continue',
                    'id' => 'playerBuilderSaveButton',
                    'callToAction' => true,
                    'style' => 'btn-sm mt-2 w-50',
                ])
                ?>
            </div>
        </div>
    </div>
</div>
