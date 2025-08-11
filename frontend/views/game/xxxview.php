<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Quest $model */
$this->title = $model->story->name;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<style>
    :root {
        --fantasy-bg: linear-gradient(135deg, #8B4513 0%, #2F1B14 100%);
        --panel-bg: rgba(41, 37, 36, 0.95);
        --border-color: rgba(218, 165, 32, 0.3);
        --text-primary: #F5DEB3;
        --text-secondary: #DAA520;
        --accent-color: #CD853F;
    }

    .fantasy-panel {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(10px);
    }

    .stat-box {
        background: rgba(139, 69, 19, 0.3);
        border: 1px solid var(--border-color);
    }

    .progress-fantasy {
        background-color: rgba(139, 69, 19, 0.5);
        height: 12px;
    }

    .btn-fantasy-action {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    .btn-fantasy-action:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn-fantasy-cta {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }

    .btn-fantasy-cta:hover {
        background-color: #e0a800;
        border-color: #d39e00;
    }

    .chat-message {
        border-left: 3px solid;
        margin-bottom: 10px;
    }

    .chat-system {
        border-left-color: #ffc107;
        background: rgba(255, 193, 7, 0.1);
    }
    .chat-dm {
        border-left-color: #6f42c1;
        background: rgba(111, 66, 193, 0.1);
    }
    .chat-player {
        border-left-color: #0d6efd;
        background: rgba(13, 110, 253, 0.1);
    }
    .chat-dice {
        border-left-color: #198754;
        background: rgba(25, 135, 84, 0.1);
    }

    .inventory-common {
        border-left: 3px solid #6c757d;
    }
    .inventory-uncommon {
        border-left: 3px solid #198754;
    }
    .inventory-rare {
        border-left: 3px solid #0d6efd;
    }
    .inventory-epic {
        border-left: 3px solid #6f42c1;
    }
    .inventory-legendary {
        border-left: 3px solid #fd7e14;
    }

    .character-portrait {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #DAA520, #B8860B);
        border: 3px solid var(--border-color);
    }

    .nav-tabs .nav-link {
        color: var(--text-secondary);
        border-color: transparent;
    }

    .nav-tabs .nav-link.active {
        background-color: rgba(218, 165, 32, 0.2);
        border-color: var(--border-color);
        color: var(--text-primary);
    }

    .scene-description {
        background: rgba(139, 69, 19, 0.2);
        border: 1px solid var(--border-color);
    }

    #chatArea {
        height: 300px;
        overflow-y: auto;
        background: rgba(0, 0, 0, 0.3);
    }

    .spell-card, .action-card, .inventory-item {
        background: rgba(139, 69, 19, 0.3);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .spell-card:hover, .action-card:hover {
        background: rgba(139, 69, 19, 0.5);
        transform: translateY(-2px);
    }
</style>
<div class="container-fluid p-3 vh-100">
    <div class="row h-100 g-3">

        <!-- Character Panel - Left -->
        <div class="col-md-3">
            <div class="card rounded p-3 h-100">
                <!-- Character Info -->
                <div class="text-center mb-4">
                    <div class="character-portrait rounded-circle mx-auto d-flex align-items-center justify-content-center fs-1 fw-bold mb-2">
                        A
                    </div>
                    <h4 class="text-warning">Aragorn Stormwind</h4>
                    <p class="mb-1">Paladin • Level 8</p>
                </div>

                <!-- Health & Mana -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span><i class="fas fa-heart text-danger me-1"></i>Health</span>
                        <small>68/85</small>
                    </div>
                    <div class="progress progress-fantasy mb-3">
                        <div class="progress-bar bg-danger" style="width: 80%"></div>
                    </div>
                </div>


                <div class="card">
                    <table style="border-spacing: 2px;">
                        <tr>
                            <td colspan="3" style="background-color: rgba(255,255,255,.1);">
                                <div class="text-center">
                                    <i class="fas fa-shield-alt text-warning me-2"></i>
                                    <span class="fs-5 fw-bold">AC 18</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="text-center" style="background-color: rgba(255,255,255,.1);">
                                    <small class="text-warning d-block">STR</small><strong>16</strong><br><small class="text-muted">+3</small>
                                </div>
                            </td>
                            <td>
                                <div class="text-center" style="background-color: rgba(255,255,255,.1);">
                                    <small class="text-warning d-block">STR</small><strong>16</strong><br><small class="text-muted">+3</small>
                                </div>
                            </td>
                            <td>
                                <div class="text-center" style="background-color: rgba(255,255,255,.1);">
                                    <small class="text-warning d-block">STR</small><strong>16</strong><br><small class="text-muted">+3</small>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="text-center" style="background-color: rgba(255,255,255,.1);">
                                    <small class="text-warning d-block">STR</small><strong>16</strong><br><small class="text-muted">+3</small>
                                </div>
                            </td>
                            <td>
                                <div class="text-center" style="background-color: rgba(255,255,255,.1);">
                                    <small class="text-warning d-block">STR</small><strong>16</strong><br><small class="text-muted">+3</small>
                                </div>
                            </td>
                            <td>
                                <div class="text-center" style="background-color: rgba(255,255,255,.1);">
                                    <small class="text-warning d-block">STR</small><strong>16</strong><br><small class="text-muted">+3</small>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- Armor Class -->
                <div class="text-center mb-4 p-2 stat-box rounded">
                    <i class="fas fa-shield-alt text-warning me-2"></i>
                    <span class="fs-5 fw-bold">AC 18</span>
                </div>

                <!-- Stats -->
                <div class="row g-2 mb-4">
                    <div class="col-4"><div class="stat-box rounded p-2 text-center"><small class="text-warning d-block">STR</small><strong>16</strong><br><small class="text-muted">+3</small></div></div>
                    <div class="col-4"><div class="stat-box rounded p-2 text-center"><small class="text-warning d-block">DEX</small><strong>12</strong><br><small class="text-muted">+1</small></div></div>
                    <div class="col-4"><div class="stat-box rounded p-2 text-center"><small class="text-warning d-block">CON</small><strong>15</strong><br><small class="text-muted">+2</small></div></div>
                    <div class="col-4"><div class="stat-box rounded p-2 text-center"><small class="text-warning d-block">INT</small><strong>13</strong><br><small class="text-muted">+1</small></div></div>
                    <div class="col-4"><div class="stat-box rounded p-2 text-center"><small class="text-warning d-block">WIS</small><strong>14</strong><br><small class="text-muted">+2</small></div></div>
                    <div class="col-4"><div class="stat-box rounded p-2 text-center"><small class="text-warning d-block">CHA</small><strong>17</strong><br><small class="text-muted">+3</small></div></div>
                </div>

                <!-- Party Members -->
                <div>
                    <h6 class="text-warning mb-3"><i class="fas fa-users me-2"></i>Party Members</h6>
                    <div class="mb-2 p-2 stat-box rounded">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Elara</span>
                            <span class="small text-muted">Wizard</span>
                        </div>
                        <div class="progress progress-fantasy" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: 71%"></div>
                        </div>
                        <small class="text-muted">32/45</small>
                    </div>
                    <div class="mb-2 p-2 stat-box rounded">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Thorin</span>
                            <span class="small text-muted">Fighter</span>
                        </div>
                        <div class="progress progress-fantasy" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: 82%"></div>
                        </div>
                        <small class="text-muted">78/95</small>
                    </div>
                    <div class="mb-2 p-2 stat-box rounded">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Luna</span>
                            <span class="small text-muted">Rogue</span>
                        </div>
                        <div class="progress progress-fantasy" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: 75%"></div>
                        </div>
                        <small class="text-muted">41/55</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Game World - Center -->
        <div class="col-md-6">
            <div class="fantasy-panel rounded h-100 d-flex flex-column">
                <!-- Scene Description -->
                <div class="scene-description rounded p-4 mb-3">
                    <h3 class="text-warning mb-3">The Throne Room of Shadows</h3>
                    <p class="mb-3">You stand in a vast throne room carved from black stone. Towering pillars stretch into darkness above, their surfaces etched with ancient draconic runes that pulse with a faint blue light. At the far end, an obsidian throne dominates the chamber, its surface reflecting the flickering torchlight like a dark mirror.</p>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-fantasy-action btn-sm">
                            <i class="fas fa-eye"></i> Examine Throne
                        </button>
                        <button class="btn btn-fantasy-action btn-sm">
                            <i class="fas fa-walking"></i> Approach Cautiously
                        </button>
                        <button class="btn btn-fantasy-action btn-sm">
                            <i class="fas fa-magic"></i> Cast Detect Magic
                        </button>
                        <button class="btn btn-fantasy-cta btn-sm">
                            <i class="fas fa-shield-alt"></i> Ready for Combat
                        </button>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="flex-grow-1 d-flex flex-column">
                    <div id="chatArea" class="flex-grow-1 p-3 rounded mb-3">
                        <div class="chat-message chat-system p-2 rounded">
                            <div class="d-flex justify-content-between">
                                <strong class="text-warning">System:</strong>
                                <small class="text-muted">14:32</small>
                            </div>
                            <div>Welcome to the Throne Room of Shadows</div>
                        </div>
                        <div class="chat-message chat-dm p-2 rounded">
                            <div class="d-flex justify-content-between">
                                <strong style="color: #6f42c1;">DM:</strong>
                                <small class="text-muted">14:33</small>
                            </div>
                            <div>The ancient throne looms before you, carved from obsidian and adorned with mystical runes that pulse with an eerie blue light.</div>
                        </div>
                        <div class="chat-message chat-player p-2 rounded">
                            <div class="d-flex justify-content-between">
                                <strong class="text-primary">Aragorn:</strong>
                                <small class="text-muted">14:34</small>
                            </div>
                            <div>I want to examine the throne more closely</div>
                        </div>
                        <div class="chat-message chat-dice p-2 rounded">
                            <div class="d-flex justify-content-between">
                                <strong class="text-success">🎲</strong>
                                <small class="text-muted">14:34</small>
                            </div>
                            <div>Aragorn rolled Investigation: 18 (d20: 15 + 3)</div>
                        </div>
                        <div class="chat-message chat-dm p-2 rounded">
                            <div class="d-flex justify-content-between">
                                <strong style="color: #6f42c1;">DM:</strong>
                                <small class="text-muted">14:35</small>
                            </div>
                            <div>You notice hidden mechanisms within the throne's armrests...</div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="input-group">
                        <input type="text" id="messageInput" class="form-control bg-dark text-light border-secondary" placeholder="Type your action or message...">
                        <button class="btn btn-fantasy-action" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions & Inventory - Right -->
        <div class="col-md-3">
            <div class="fantasy-panel rounded p-3 h-100">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#actions">
                            <i class="fas fa-sword"></i> Actions
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventory">
                            <i class="fas fa-backpack"></i> Inventory
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#spells">
                            <i class="fas fa-book-open"></i> Spells
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" style="height: calc(100% - 60px); overflow-y: auto;">
                    <!-- Actions Tab -->
                    <div class="tab-pane fade show active" id="actions">
                        <div class="d-grid gap-2">
                            <button class="btn action-card p-3 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="btn btn-fantasy-action btn-sm me-3">
                                        <i class="fas fa-sword"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-warning">Attack</div>
                                        <small class="text-muted">Make a weapon attack</small>
                                    </div>
                                </div>
                            </button>
                            <button class="btn action-card p-3 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="btn btn-fantasy-action btn-sm me-3">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-warning">Defend</div>
                                        <small class="text-muted">Take the Dodge action</small>
                                    </div>
                                </div>
                            </button>
                            <button class="btn action-card p-3 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="btn btn-fantasy-action btn-sm me-3">
                                        <i class="fas fa-running"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-warning">Dash</div>
                                        <small class="text-muted">Move extra distance</small>
                                    </div>
                                </div>
                            </button>
                            <button class="btn action-card p-3 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="btn btn-fantasy-action btn-sm me-3">
                                        <i class="fas fa-hands-helping"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-warning">Help</div>
                                        <small class="text-muted">Help an ally</small>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Inventory Tab -->
                    <div class="tab-pane fade" id="inventory">
                        <div class="d-grid gap-2">
                            <div class="inventory-item inventory-uncommon p-2 rounded">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold">Longsword +1</div>
                                        <small class="text-muted">Weapon</small>
                                    </div>
                                    <div class="text-end">
                                        <div>×1</div>
                                        <small class="text-success">Uncommon</small>
                                    </div>
                                </div>
                            </div>
                            <div class="inventory-item inventory-rare p-2 rounded">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold">Shield of Faith</div>
                                        <small class="text-muted">Armor</small>
                                    </div>
                                    <div class="text-end">
                                        <div>×1</div>
                                        <small class="text-primary">Rare</small>
                                    </div>
                                </div>
                            </div>
                            <div class="inventory-item inventory-common p-2 rounded">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold">Health Potion</div>
                                        <small class="text-muted">Consumable</small>
                                    </div>
                                    <div class="text-end">
                                        <div>×3</div>
                                        <small class="text-secondary">Common</small>
                                    </div>
                                </div>
                            </div>
                            <div class="inventory-item inventory-uncommon p-2 rounded">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold">Scroll of Fireball</div>
                                        <small class="text-muted">Scroll</small>
                                    </div>
                                    <div class="text-end">
                                        <div>×2</div>
                                        <small class="text-success">Uncommon</small>
                                    </div>
                                </div>
                            </div>
                            <div class="inventory-item inventory-rare p-2 rounded">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold">Ring of Protection</div>
                                        <small class="text-muted">Accessory</small>
                                    </div>
                                    <div class="text-end">
                                        <div>×1</div>
                                        <small class="text-primary">Rare</small>
                                    </div>
                                </div>
                            </div>
                            <div class="inventory-item inventory-common p-2 rounded">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold">Rations</div>
                                        <small class="text-muted">Consumable</small>
                                    </div>
                                    <div class="text-end">
                                        <div>×10</div>
                                        <small class="text-secondary">Common</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Spells Tab -->
                    <div class="tab-pane fade" id="spells">
                        <div class="d-grid gap-2">
                            <button class="btn spell-card p-3 text-start">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="fw-bold text-warning">Cure Wounds</div>
                                    <div class="text-primary">2 MP</div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Level 1</small>
                                    <small class="text-muted">Evocation</small>
                                </div>
                            </button>
                            <button class="btn spell-card p-3 text-start">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="fw-bold text-warning">Shield of Faith</div>
                                    <div class="text-primary">2 MP</div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Level 1</small>
                                    <small class="text-muted">Abjuration</small>
                                </div>
                            </button>
                            <button class="btn spell-card p-3 text-start">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="fw-bold text-warning">Spiritual Weapon</div>
                                    <div class="text-primary">4 MP</div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Level 2</small>
                                    <small class="text-muted">Evocation</small>
                                </div>
                            </button>
                            <button class="btn spell-card p-3 text-start">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="fw-bold text-warning">Hold Person</div>
                                    <div class="text-primary">4 MP</div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Level 2</small>
                                    <small class="text-muted">Enchantment</small>
                                </div>
                            </button>
                            <button class="btn spell-card p-3 text-start">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="fw-bold text-warning">Dispel Magic</div>
                                    <div class="text-primary">6 MP</div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Level 3</small>
                                    <small class="text-muted">Abjuration</small>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
