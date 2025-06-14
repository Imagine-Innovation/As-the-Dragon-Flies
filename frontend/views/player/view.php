<?php

use frontend\widgets\PlayerCharacteristics;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Players', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container py-3">
    <?php if (1 === 2): ?>
        <!-- Character Header -->
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="avatar-img">
                <img src="img/characters/<?= $model->avatar ?>" class="w-100 h-100 rounded-circle image-thumbnail" style="object-fit: cover;">
            </div>
            <div class="fs-5"><?= empty($model->name) ? 'Unknown' : $model->name ?>, <?= $model->race->name ?> <?= $model->level->name ?> <?= $model->class->name ?></div>
        </div>

        <?= PlayerCharacteristics::widget(['player' => $model]) ?>
    <?php else: ?>
        <!-- Character Header -->
        <div class="row mb-4">
            <div class="col-lg-3 text-center mb-3 mb-lg-0">
                <div class="d-flex align-items-center justify-content-center mx-auto">
                    <div class="avatar-img">
                        <img src="img/characters/<?= $model->avatar ?>" class="rounded-circle image-thumbnail" style="object-fit: cover;">
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <h1 class="mb-3 text-decoration"><?= $model->name ?></h1>
                <h5><?= $model->description ?></h5>
            </div>
        </div>

        <hr class="border-secondary">

        <!-- Main Content -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <!-- Ability Scores -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-decoration text-dark ext-center py-2">
                        <i class="fas fa-fist-raised me-2"></i>Ability Scores
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php foreach ($model->playerAbilities as $playerAbility): ?>
                                <div class="col-4">
                                    <div class="card ability-card text-center">
                                        <div class="card-body p-2">
                                            <?= $playerAbility->ability->code ?>: <?= $playerAbility->score ?><br>
                                            <span class="badge badge-danger fw-bold"><?= $playerAbility->bonus >= 0 ? "+" : "-" ?><?= $playerAbility->bonus ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combat Stats -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-decoration text-dark text-center py-2">
                        <i class="fas fa-shield-alt me-2"></i>Combat Stats
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body p-3">
                                        <small class="text-uppercase fw-bold text-muted d-block">Armor Class</small>
                                        <div class="stat-value">18</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body p-3">
                                        <small class="text-uppercase fw-bold text-muted d-block">Initiative</small>
                                        <div class="stat-value">+1</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body p-3">
                                        <small class="text-uppercase fw-bold text-muted d-block">Speed</small>
                                        <div class="stat-value">25 ft</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body p-3">
                                        <small class="text-uppercase fw-bold text-muted d-block">Prof. Bonus</small>
                                        <div class="stat-value">+3</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Hit Points -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-decoration text-dark text-center py-2">
                        <i class="fas fa-heart me-2"></i>Hit Points
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="card hp-current text-center">
                                    <div class="card-body p-3">
                                        <small class="text-uppercase fw-bold d-block opacity-75">Current HP</small>
                                        <div class="stat-value text-white">42</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card hp-max text-center">
                                    <div class="card-body p-3">
                                        <small class="text-uppercase fw-bold text-muted d-block">Max HP</small>
                                        <div class="stat-value">47</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="progress progress-fantasy mb-3" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: 89%" aria-valuenow="42" aria-valuemin="0" aria-valuemax="47"></div>
                        </div>
                        <div class="card text-center">
                            <div class="card-body p-3">
                                <small class="text-uppercase fw-bold text-muted d-block">Hit Dice</small>
                                <div class="stat-value">5d10</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Skills -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-decoration text-dark text-center py-2">
                        <i class="fas fa-tools me-2"></i>Skills
                    </div>
                    <div class="card-body p-2">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator me-3"></div>
                                    <span>Athletics</span>
                                </div>
                                <span class="badge bg-secondary">+6</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator not-proficient me-3"></div>
                                    <span>Acrobatics</span>
                                </div>
                                <span class="badge bg-secondary">+1</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator me-3"></div>
                                    <span>Intimidation</span>
                                </div>
                                <span class="badge bg-secondary">+3</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator me-3"></div>
                                    <span>Survival</span>
                                </div>
                                <span class="badge bg-secondary">+5</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator not-proficient me-3"></div>
                                    <span>Perception</span>
                                </div>
                                <span class="badge bg-secondary">+2</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator not-proficient me-3"></div>
                                    <span>Investigation</span>
                                </div>
                                <span class="badge bg-secondary">+1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Saving Throws -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-decoration text-dark text-center py-2">
                        <i class="fas fa-dice-d20 me-2"></i>Saving Throws
                    </div>
                    <div class="card-body p-2">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator me-3"></div>
                                    <span>Strength</span>
                                </div>
                                <span class="badge bg-secondary">+6</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator not-proficient me-3"></div>
                                    <span>Dexterity</span>
                                </div>
                                <span class="badge bg-secondary">+1</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator me-3"></div>
                                    <span>Constitution</span>
                                </div>
                                <span class="badge bg-secondary">+5</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator not-proficient me-3"></div>
                                    <span>Intelligence</span>
                                </div>
                                <span class="badge bg-secondary">+1</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator not-proficient me-3"></div>
                                    <span>Wisdom</span>
                                </div>
                                <span class="badge bg-secondary">+2</span>
                            </div>
                            <div class="list-group-item skill-row d-flex justify-content-between align-items-center bg-transparent border-0 px-3 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="proficiency-indicator not-proficient me-3"></div>
                                    <span>Charisma</span>
                                </div>
                                <span class="badge bg-secondary">+0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attacks -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-decoration text-dark text-center py-2">
                        <i class="fas fa-sword me-2"></i>Attacks & Spells
                    </div>
                    <div class="card-body">
                        <div class="card equipment-item mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold">Longsword</h6>
                                    <span class="badge btn-fantasy">+8 to hit</span>
                                </div>
                                <small class="text-muted">1d8+3 slashing damage</small>
                            </div>
                        </div>
                        <div class="card equipment-item mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold">Handaxe</h6>
                                    <span class="badge btn-fantasy">+6 to hit</span>
                                </div>
                                <small class="text-muted">1d6+3 slashing damage (thrown 20/60)</small>
                            </div>
                        </div>
                        <div class="card equipment-item">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold">Crossbow, Light</h6>
                                    <span class="badge btn-fantasy">+4 to hit</span>
                                </div>
                                <small class="text-muted">1d8+1 piercing damage (range 80/320)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-decoration text-dark text-center py-2">
                        <i class="fas fa-backpack me-2"></i>Equipment
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="equipmentAccordion">
                            <div class="accordion-item bg-transparent border-secondary">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-transparent text-light border-0" type="button" data-bs-toggle="collapse" data-bs-target="#weaponsArmor">
                                        <i class="fas fa-shield-alt me-2 text-warning"></i>Weapons & Armor
                                    </button>
                                </h2>
                                <div id="weaponsArmor" class="accordion-collapse collapse show" data-bs-parent="#equipmentAccordion">
                                    <div class="accordion-body">
                                        <div class="card equipment-item mb-2">
                                            <div class="card-body p-3">
                                                <h6 class="mb-1 fw-bold">Plate Armor</h6>
                                                <small class="text-muted">AC 18, Heavy, Stealth Disadvantage</small>
                                            </div>
                                        </div>
                                        <div class="card equipment-item mb-2">
                                            <div class="card-body p-3">
                                                <h6 class="mb-1 fw-bold">Longsword</h6>
                                                <small class="text-muted">Versatile (1d10)</small>
                                            </div>
                                        </div>
                                        <div class="card equipment-item">
                                            <div class="card-body p-3">
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
                                        <i class="fas fa-tools me-2 text-warning"></i>Gear & Items
                                    </button>
                                </h2>
                                <div id="gear" class="accordion-collapse collapse" data-bs-parent="#equipmentAccordion">
                                    <div class="accordion-body">
                                        <div class="card equipment-item mb-2">
                                            <div class="card-body p-3">
                                                <h6 class="mb-1 fw-bold">Adventurer's Pack</h6>
                                                <small class="text-muted">Backpack, bedroll, mess kit, tinderbox, 10 torches, 10 days of rations, waterskin, 50 feet of hempen rope</small>
                                            </div>
                                        </div>
                                        <div class="card equipment-item">
                                            <div class="card-body p-3">
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
                            <h6 class="text-warning mb-3"><i class="fas fa-coins me-2"></i>Currency</h6>
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
            </div>

            <!-- Features & Traits -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-decoration text-dark text-center py-2">
                        <i class="fas fa-magic me-2"></i>Features & Traits
                    </div>
                    <div class="card-body">
                        <div class="card feature-card mb-3">
                            <div class="card-body p-3">
                                <h6 class="text-warning mb-2">Second Wind</h6>
                                <small class="text-muted">Regain 1d10+5 hit points as a bonus action (1/short rest)</small>
                            </div>
                        </div>
                        <div class="card feature-card mb-3">
                            <div class="card-body p-3">
                                <h6 class="text-warning mb-2">Action Surge</h6>
                                <small class="text-muted">Take an additional action on your turn (1/short rest)</small>
                            </div>
                        </div>
                        <div class="card feature-card mb-3">
                            <div class="card-body p-3">
                                <h6 class="text-warning mb-2">Darkvision</h6>
                                <small class="text-muted">See in dim light within 60 feet as if it were bright light</small>
                            </div>
                        </div>
                        <div class="card feature-card">
                            <div class="card-body p-3">
                                <h6 class="text-warning mb-2">Dwarven Resilience</h6>
                                <small class="text-muted">Advantage against poison saves, resistance to poison damage</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card">
                    <div class="card-header bg-warning text-decoration text-dark text-center py-2">
                        <i class="fas fa-sticky-note me-2"></i>Notes
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" rows="8" placeholder="Character notes, backstory, goals, and other important information...">
                                Thorin was once a simple blacksmith in the mountain village of Ironpeak. When raiders threatened his home, he took up arms to defend his people. His bravery and skill in battle earned him recognition as a folk hero. Now he adventures to protect the innocent and uphold justice wherever he goes.

                                Current Quest: Investigating strange disappearances in the nearby forest. Suspects involve dark magic or aberrant creatures.

                                Party Members:
                                - Elara (Elf Wizard)
                                - Gareth (Human Cleric)
                                - Pip (Halfling Rogue)</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

</div>
