<?php ?>
<div class="modal fade" id="equipmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-custom-size">
        <div class="modal-content">
            <div class="equipment-modal-body">
                <div class="row h-100">
                    <div class="col-sm-6 text-center">
                        <div class="equipment-card">
                            <div class="card-header">
                                <p class="lead text-decoration">List a available equipment</p>
                                <p class="text-decoration">Click on the white areas to see the list of items your player can pick up.</p>
                            </div>
                            <div id="packageContent" class="card-body" style="height: 80%; overflow: auto;">
                                <p class="text-decoration">You have nothing at all</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="equipment-card" id="svg-modal">
                            <?php if (1 === 2): ?>
                                <div class="card-body svg-container">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 900" preserveAspectRatio="xMidYMid meet" id="equipmentSvg">
                                        <image href="/frontend/web/img/man-back-view.png" width="200" height="450" x=300 y=0 class="img-fluid" />

                                        <image href="/frontend/web/img/man-front-view.png" width="400" height="900" class="img-fluid" />

                                        <!-- Head -->
                                        <circle id="equipmentHeadZone" cx="200" cy="90" r="90" fill="white" fill-opacity="0.5" />

                                        <!-- Chest -->
                                        <circle id="equipmentChestZone" cx="200" cy="330" r="120" fill="white" fill-opacity="0.5" />

                                        <!-- Right Hand -->
                                        <circle id="equipmentRightHandZone" cx="70" cy="520" r="70" fill="white" fill-opacity="0.5" />

                                        <!-- Left Hand -->
                                        <circle id="equipmentLeftHandZone" cx="330" cy="520" r="70" fill="white" fill-opacity="0.5" />

                                        <!-- Back -->
                                        <circle id="equipmentBackZone" cx="395" cy="150" r="70" fill="black" fill-opacity="0.5" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
