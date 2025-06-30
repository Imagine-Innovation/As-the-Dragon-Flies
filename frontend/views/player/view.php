<?php

use frontend\widgets\PlayerCharacteristics;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Players', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$proficiencyBonus = $model->level->proficiency_bonus;
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
        <table>
            <tr>
                <td>
                    <div class="avatar-img">
                        <img src="img/characters/<?= $model->avatar ?>" class="rounded-circle image-thumbnail" style="object-fit: cover;">
                    </div>
                </td>
                <td>
                    <h1 class="mb-3 text-decoration"><?= $model->name ?></h1>
                    <h5><?= $model->description ?></h5>
                </td>
            </tr>
        </table>

        <hr class="border-secondary">

        <!-- Main Content -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <!-- Ability Scores -->
                <div class="card mb-4">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
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
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
                        <i class="fas fa-shield-alt me-2"></i>Combat Stats
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body p-3">
                                        Armor Class: <?= $model->armor_class ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body p-3">
                                        Initiative: +1
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body p-3">
                                        Speed: <?= $model->speed ?? $model->race->speed ?> ft
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card text-center">
                                    <div class="card-body p-3">
                                        Prof. Bonus: +<?= $proficiencyBonus ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Hit Points -->
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
                        <i class="fas fa-heart me-2"></i>Hit Points
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="card hp-current text-center">
                                    <div class="card-body p-3">
                                        Current HP: <?= $model->hit_points ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card hp-max text-center">
                                    <div class="card-body p-3">
                                        Max HP: <?= $model->max_hit_points ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="progress progress-fantasy mb-3" style="height: 8px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: <?= intval(($model->hit_points ?? 0) / ($model->max_hit_points ?? 1) * 100) ?>%"
                                 aria-valuenow="<?= $model->hit_points ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="<?= $model->max_hit_points ?>">
                            </div>
                        </div>
                        <div class="card text-center">
                            <div class="card-body p-3">
                                Hit Dice: <?= $model->class->hit_die ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Skills -->
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
                        <i class="fas fa-tools me-2"></i>Skills
                    </div>
                    <div class="card-body p-4">
                        <table class="w-100">
                            <?php foreach ($model->playerSkills as $playerSkill): ?>
                                <tr>
                                    <td><?= $playerSkill->skill->name ?></td>
                                    <td class="text-center w-25">
                                        <span class="badge bg-secondary w-75">
                                            +<?= $playerSkill->bonus ?>
                                            <?= $playerSkill->is_proficient ? '&nbsp;<i class="bi bi-shield-plus"></i>' : "" ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Saving Throws -->
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
                        <i class="fas fa-dice-d20 me-2"></i>Saving Throws
                    </div>
                    <div class="card-body p-4">
                        <table class="w-100">
                            <thead>
                                <tr>
                                    <th>Ability</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center w-25">Bonus</th>
                                    <th class="text-center w-25">Saving Throw</th>
                                </tr>
                            </thead>
                            <?php foreach ($model->playerAbilities as $playerAbility): ?>
                                <?php $savingThrow = $playerAbility->modifier + ($playerAbility->is_saving_throw ? $proficiencyBonus : 0); ?>
                                <tr>
                                    <td class="text-left"><?= $playerAbility->ability->name ?></td>
                                    <td class="text-center"><?= $playerAbility->score ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-danger w-50">
                                            <?= $playerAbility->score >= 0 ? "+$playerAbility->score" : "$playerAbility->score" ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary w-50">
                                            <?= $savingThrow >= 0 ? "+$savingThrow" : "$savingThrow" ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Attacks -->
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
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
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
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
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <div class="card mb-4">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
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
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <div class="card">
                    <div class="card-header bg-purple text-decoration fw-bold h-100 py-2">
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
