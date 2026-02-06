<?php

/** @var yii\web\View $this */
/** @var common\models\Player $model */
/** @var string $cardHeaderClass */
?>
<!-- Equipment -->
<div class="card mb-4">
    <div class="<?= $cardHeaderClass ?>">
        <i class="bi bi-backpack2 me-2"></i>Equipment
    </div>
    <div class="card-body">
        <div class="accordion" id="equipmentAccordion">
            <div class="accordion-item bg-transparent border-secondary">
                <h2 class="accordion-header">
                    <button class="accordion-button bg-transparent text-light border-0" type="button" data-bs-toggle="collapse" data-bs-target="#weaponsArmor">
                        <i class="bi bi-shield-shaded me-2 text-warning"></i>Weapons & Armor
                    </button>
                </h2>
                <div id="weaponsArmor" class="accordion-collapse collapse show" data-bs-parent="#equipmentAccordion">
                    <div class="accordion-body">
                        <div class="card equipment-item mb-2">
                            <div class="card-body p-2">
                                <h6 class="mb-1 fw-bold">Plate Armor</h6>
                                <small class="text-muted">AC 18, Heavy, Stealth Disadvantage</small>
                            </div>
                        </div>
                        <div class="card equipment-item mb-2">
                            <div class="card-body p-2">
                                <h6 class="mb-1 fw-bold">Longsword</h6>
                                <small class="text-muted">Versatile (1d10)</small>
                            </div>
                        </div>
                        <div class="card equipment-item">
                            <div class="card-body p-2">
                                <h6 class="mb-1 fw-bold">Shield</h6>
                                <small class="text-muted">+2 AC</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item bg-transparent border-secondary">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-transparent text-light border-0" type="button" data-bs-toggle="collapse" data-bs-target="#gear">
                        <i class="bi bi-tools me-2 text-warning"></i>Gear & Items
                    </button>
                </h2>
                <div id="gear" class="accordion-collapse collapse" data-bs-parent="#equipmentAccordion">
                    <div class="accordion-body">
                        <div class="card equipment-item mb-2">
                            <div class="card-body p-2">
                                <h6 class="mb-1 fw-bold">Adventurer's Pack</h6>
                                <small class="text-muted">Backpack, bedroll, mess kit, tinderbox, 10 torches, 10 days of rations, waterskin, 50 feet of hempen rope</small>
                            </div>
                        </div>
                        <div class="card equipment-item">
                            <div class="card-body p-2">
                                <h6 class="mb-1 fw-bold">Smith's Tools</h6>
                                <small class="text-muted">Proficient</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currency -->
        <div class="mt-4">
            <h6 class="text-warning mb-3"><i class="bi bi-cash-coin me-2"></i>Currency</h6>
            <div class="row g-2">
                <div class="col-4">
                    <div class="card currency-card text-center">
                        <div class="card-body p-2">
                            <small class="d-block opacity-75">GP</small>
                            <div class="fw-bold">150</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card currency-card text-center">
                        <div class="card-body p-2">
                            <small class="d-block opacity-75">SP</small>
                            <div class="fw-bold">25</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card currency-card text-center">
                        <div class="card-body p-2">
                            <small class="d-block opacity-75">CP</small>
                            <div class="fw-bold">50</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
