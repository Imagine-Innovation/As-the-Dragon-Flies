<?php
/** @var yii\web\View $this */
// Game configuration
$character = [
    'name' => 'Thorin Ironbeard',
    'class' => 'Paladin',
    'level' => 12,
    'hp' => 85,
    'maxHp' => 120,
    'mp' => 45,
    'maxMp' => 60,
    'ac' => 18,
    'stats' => [
        'strength' => 16,
        'dexterity' => 12,
        'constitution' => 15,
        'intelligence' => 10,
        'wisdom' => 14,
        'charisma' => 16,
    ],
];

$party = [
    ['name' => 'Elara Moonwhisper', 'class' => 'Wizard', 'hp' => 45, 'maxHp' => 65],
    ['name' => 'Gareth Swiftarrow', 'class' => 'Ranger', 'hp' => 70, 'maxHp' => 80],
    ['name' => 'Luna Shadowstep', 'class' => 'Rogue', 'hp' => 55, 'maxHp' => 70]
];

$inventory = [
    ['name' => 'Flame Tongue Sword', 'type' => 'weapon', 'rarity' => 'rare'],
    ['name' => 'Plate Armor', 'type' => 'armor', 'rarity' => 'common'],
    ['name' => 'Health Potion', 'type' => 'consumable', 'rarity' => 'common', 'quantity' => 3],
    ['name' => 'Ring of Protection', 'type' => 'accessory', 'rarity' => 'uncommon'],
    ['name' => 'Holy Symbol', 'type' => 'focus', 'rarity' => 'common']
];

$spells = [
    ['name' => 'Cure Wounds', 'level' => 1, 'cost' => 5],
    ['name' => 'Divine Smite', 'level' => 2, 'cost' => 10],
    ['name' => 'Protection from Evil', 'level' => 1, 'cost' => 5],
    ['name' => 'Turn Undead', 'level' => 3, 'cost' => 15]
];

$chatLog = [
    ['type' => 'system', 'message' => 'You enter the ancient dungeon...'],
    ['type' => 'player', 'name' => 'Thorin', 'message' => 'I\'ll take the lead and check for traps.'],
    ['type' => 'dm', 'message' => 'Roll for Investigation.'],
    ['type' => 'roll', 'name' => 'Thorin', 'result' => 'Investigation: 15 (Success!)'],
    ['type' => 'system', 'message' => 'You spot a pressure plate ahead.']
];

// Dungeon map data (12x8 grid);
$dungeonMap = [
    // Row 0
    ['wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall'],
    // Row 1
    ['wall', 'empty', 'empty', 'door', 'empty', 'empty', 'treasure', 'wall', 'empty', 'empty', 'empty', 'wall'],
    // Row 2
    ['wall', 'empty', 'wall', 'wall', 'wall', 'empty', 'empty', 'wall', 'empty', 'monster', 'empty', 'wall'],
    // Row 3
    ['wall', 'trap', 'wall', 'empty', 'empty', 'empty', 'empty', 'door', 'empty', 'empty', 'empty', 'wall'],
    // Row 4
    ['wall', 'empty', 'wall', 'empty', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'empty', 'wall'],
    // Row 5
    ['wall', 'empty', 'empty', 'empty', 'wall', 'boss', 'empty', 'empty', 'empty', 'wall', 'secret', 'wall'],
    // Row 6
    ['wall', 'empty', 'empty', 'empty', 'wall', 'empty', 'empty', 'empty', 'empty', 'wall', 'empty', 'wall'],
    // Row 7
    ['wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall', 'wall']
];

function getRarityClass($rarity) {
    switch ($rarity) {
        case 'common': return 'text-secondary';
        case 'uncommon': return 'text-success';
        case 'rare': return 'text-info';
        case 'epic': return 'text-warning';
        case 'legendary': return 'text-danger';
        default: return 'text-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Realm of Shadows - D&D Game</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            :root {
                --bg-dark-primary: #000000;
                --bg-dark-secondary: #0a0a0a;
                --bg-dark-card: #111111;
                --bg-dark-header: #1a1a1a;
                --text-warning: #ffc107;
                --text-warning-light: #ffda6a;
                --border-warning: #ffc107;
                --text-light-purple: #c084fc;
                --shadow-dark: rgba(0, 0, 0, 0.95);
                --shadow-glow: rgba(255, 193, 7, 0.3);
            }

            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background: radial-gradient(ellipse at center, #0a0a0a 0%, #000000 70%, #000000 100%);
                min-height: 100vh;
                color: var(--text-warning-light);
                overflow-x: hidden;
            }

            .bg-dark-custom {
                background: radial-gradient(ellipse at center, #0a0a0a 0%, #000000 70%, #000000 100%) !important;
            }

            .bg-dark-card {
                background: linear-gradient(145deg, #111111 0%, #0a0a0a 100%) !important;
                backdrop-filter: blur(10px);
                box-shadow: 0 8px 32px var(--shadow-dark), inset 0 1px 0 rgba(255, 193, 7, 0.1);
            }

            .bg-dark-header {
                background: linear-gradient(145deg, #1a1a1a 0%, #0f0f0f 100%) !important;
                box-shadow: 0 4px 20px var(--shadow-dark);
            }

            .bg-dark-input {
                background-color: var(--bg-dark-secondary) !important;
                border: 1px solid rgba(255, 193, 7, 0.3) !important;
            }

            .text-warning-light {
                color: var(--text-warning-light) !important;
            }

            .text-light-purple {
                color: var(--text-light-purple) !important;
            }

            .border-warning {
                border-color: var(--border-warning) !important;
            }

            .game-container {
                height: calc(100vh - 56px);
            }

            .character-sidebar {
                background: linear-gradient(180deg, rgba(17, 17, 17, 0.98) 0%, rgba(0, 0, 0, 0.99) 100%);
                border-right: 2px solid var(--border-warning);
                box-shadow: 4px 0 25px var(--shadow-dark);
            }

            .actions-sidebar {
                background: linear-gradient(180deg, rgba(17, 17, 17, 0.98) 0%, rgba(0, 0, 0, 0.99) 100%);
                border-left: 2px solid var(--border-warning);
                box-shadow: -4px 0 25px var(--shadow-dark);
            }

            .game-main {
                background: radial-gradient(ellipse at center, rgba(10, 10, 10, 0.8) 0%, rgba(0, 0, 0, 0.95) 100%);
            }

            .chat-section {
                height: 180px;
                background: linear-gradient(145deg, rgba(10, 10, 10, 0.95) 0%, rgba(0, 0, 0, 0.98) 100%);
                border-bottom: 2px solid var(--border-warning);
                box-shadow: 0 4px 20px var(--shadow-dark);
            }

            .game-world-section {
                flex: 1;
                min-height: 300px;
            }

            .chat-messages {
                flex: 1;
                overflow-y: auto;
                max-height: 120px;
            }

            .character-avatar i {
                font-size: 2.5rem;
                filter: drop-shadow(0 0 10px var(--text-warning));
            }

            .throne-icon i {
                font-size: 4rem;
                filter: drop-shadow(0 0 15px var(--text-warning));
            }

            .progress-sm {
                height: 8px;
                background-color: rgba(0, 0, 0, 0.8);
                border: 1px solid rgba(255, 193, 7, 0.2);
            }

            .progress-xs {
                height: 4px;
                background-color: rgba(0, 0, 0, 0.8);
            }

            .ac-display {
                background: linear-gradient(135deg, rgba(255, 193, 7, 0.2) 0%, rgba(255, 193, 7, 0.05) 100%);
                border: 2px solid rgba(255, 193, 7, 0.5);
                box-shadow: inset 0 2px 10px rgba(255, 193, 7, 0.1), 0 0 20px rgba(255, 193, 7, 0.1);
            }

            .nav-dark .nav-link {
                color: var(--text-warning-light);
                border-color: var(--border-warning);
                background-color: rgba(17, 17, 17, 0.9);
                transition: all 0.3s ease;
            }

            .nav-dark .nav-link.active {
                color: var(--text-warning);
                background-color: var(--bg-dark-card);
                border-color: var(--border-warning);
                box-shadow: 0 4px 15px var(--shadow-glow);
            }

            .nav-dark .nav-link:hover {
                color: var(--text-warning);
                background-color: rgba(255, 193, 7, 0.15);
                transform: translateY(-2px);
            }

            .btn-outline-warning {
                color: var(--text-warning);
                border-color: var(--border-warning);
                background-color: rgba(255, 193, 7, 0.1);
                transition: all 0.3s ease;
            }

            .btn-outline-warning:hover {
                color: #000;
                background-color: var(--text-warning);
                border-color: var(--border-warning);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px var(--shadow-glow);
            }

            .bg-outline-warning {
                background-color: rgba(255, 193, 7, 0.25);
                color: var(--text-warning);
                border: 1px solid var(--border-warning);
            }

            .action-btn {
                transition: all 0.3s ease;
                background-color: rgba(17, 17, 17, 0.9);
                border: 1px solid rgba(255, 193, 7, 0.3);
            }

            .action-btn:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px var(--shadow-glow);
                background-color: rgba(255, 193, 7, 0.15);
            }

            .action-btn.active {
                background-color: rgba(255, 193, 7, 0.25);
                border-color: var(--text-warning);
                color: var(--text-warning);
                box-shadow: 0 0 20px var(--shadow-glow);
            }

            .inventory-scroll,
            .spells-scroll {
                max-height: 300px;
                overflow-y: auto;
            }

            .inventory-scroll::-webkit-scrollbar,
            .spells-scroll::-webkit-scrollbar,
            .chat-messages::-webkit-scrollbar {
                width: 8px;
            }

            .inventory-scroll::-webkit-scrollbar-track,
            .spells-scroll::-webkit-scrollbar-track,
            .chat-messages::-webkit-scrollbar-track {
                background: var(--bg-dark-secondary);
                border-radius: 4px;
            }

            .inventory-scroll::-webkit-scrollbar-thumb,
            .spells-scroll::-webkit-scrollbar-thumb,
            .chat-messages::-webkit-scrollbar-thumb {
                background: var(--border-warning);
                border-radius: 4px;
            }

            .inventory-scroll::-webkit-scrollbar-thumb:hover,
            .spells-scroll::-webkit-scrollbar-thumb:hover,
            .chat-messages::-webkit-scrollbar-thumb:hover {
                background: var(--text-warning);
            }

            .card {
                transition: all 0.3s ease;
                border: 1px solid rgba(255, 193, 7, 0.4);
                background: linear-gradient(145deg, #111111 0%, #0a0a0a 100%);
            }

            .card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 30px var(--shadow-glow);
                border-color: var(--border-warning);
            }

            .inventory-item:hover,
            .spell-item:hover {
                background: linear-gradient(145deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%) !important;
            }

            .progress-bar {
                transition: width 0.8s ease;
            }

            .chat-message {
                padding: 0.5rem 0;
                border-left: 3px solid transparent;
                padding-left: 1rem;
                margin-left: -1rem;
                transition: all 0.2s ease;
                border-radius: 0 4px 4px 0;
            }

            .chat-message:hover {
                background-color: rgba(255, 193, 7, 0.08);
            }

            .chat-message.system {
                border-left-color: var(--text-warning-light);
                background-color: rgba(255, 193, 7, 0.03);
            }

            .chat-message.player {
                border-left-color: #0dcaf0;
                background-color: rgba(13, 202, 240, 0.03);
            }

            .chat-message.dm {
                border-left-color: var(--text-light-purple);
                background-color: rgba(192, 132, 252, 0.03);
            }

            .chat-message.roll {
                border-left-color: #198754;
                background-color: rgba(25, 135, 84, 0.03);
            }

            /* Dungeon Map Styles */
            .dungeon-map {
                display: grid;
                grid-template-columns: repeat(12, 1fr);
                grid-template-rows: repeat(8, 1fr);
                gap: 1px;
                background-color: rgba(0, 0, 0, 0.9);
                border: 2px solid var(--border-warning);
                border-radius: 8px;
                padding: 4px;
                max-width: 100%;
                aspect-ratio: 3/2;
                margin: 0 auto;
            }

            .map-cell {
                aspect-ratio: 1;
                border: 1px solid rgba(255, 193, 7, 0.2);
                border-radius: 2px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                min-height: 25px;
            }

            .map-cell.wall {
                background: linear-gradient(145deg, #333333 0%, #1a1a1a 100%);
                border-color: #555;
            }

            .map-cell.empty {
                background: linear-gradient(145deg, rgba(17, 17, 17, 0.8) 0%, rgba(10, 10, 10, 0.9) 100%);
            }

            .map-cell.door {
                background: linear-gradient(145deg, #8B4513 0%, #654321 100%);
                color: var(--text-warning);
            }

            .map-cell.treasure {
                background: linear-gradient(145deg, #FFD700 0%, #FFA500 100%);
                color: #000;
            }

            .map-cell.monster {
                background: linear-gradient(145deg, #8B0000 0%, #4B0000 100%);
                color: #FF6B6B;
            }

            .map-cell.trap {
                background: linear-gradient(145deg, #800080 0%, #4B0082 100%);
                color: #DA70D6;
            }

            .map-cell.boss {
                background: linear-gradient(145deg, #FF0000 0%, #8B0000 100%);
                color: #FFD700;
                box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
            }

            .map-cell.secret {
                background: linear-gradient(145deg, #2F4F4F 0%, #1C3A3A 100%);
                color: #40E0D0;
            }

            .map-cell.player {
                background: linear-gradient(145deg, #0066CC 0%, #004499 100%);
                color: #FFFFFF;
                box-shadow: 0 0 15px rgba(0, 102, 204, 0.8);
                z-index: 10;
            }

            .map-cell.fogged {
                background: linear-gradient(145deg, #000000 0%, #0a0a0a 100%) !important;
                color: transparent !important;
                border-color: rgba(255, 193, 7, 0.1) !important;
            }

            .map-cell.revealed {
                border-color: rgba(255, 193, 7, 0.4);
            }

            .map-cell.explored {
                opacity: 0.7;
            }

            .map-cell:hover:not(.fogged) {
                transform: scale(1.1);
                z-index: 5;
                box-shadow: 0 0 10px var(--shadow-glow);
            }

            .map-legend {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 1rem;
                justify-content: center;
            }

            .legend-item {
                display: flex;
                align-items: center;
                gap: 0.25rem;
                font-size: 0.75rem;
                color: var(--text-warning-light);
            }

            .legend-color {
                width: 12px;
                height: 12px;
                border-radius: 2px;
                border: 1px solid rgba(255, 193, 7, 0.3);
            }

            .map-controls {
                display: flex;
                justify-content: center;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .movement-btn {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
            }

            /* Mobile-specific styles */
            @media (max-width: 991.98px) {
                .game-container {
                    height: auto;
                    min-height: calc(100vh - 56px);
                }

                .character-sidebar,
                .actions-sidebar {
                    border: none;
                    border-bottom: 2px solid var(--border-warning);
                    background: linear-gradient(90deg, rgba(17, 17, 17, 0.98) 0%, rgba(0, 0, 0, 0.99) 100%);
                }

                .mobile-sidebars {
                    background: linear-gradient(145deg, rgba(17, 17, 17, 0.95) 0%, rgba(0, 0, 0, 0.98) 100%);
                    border-bottom: 2px solid var(--border-warning);
                    box-shadow: 0 4px 20px var(--shadow-dark);
                }

                .chat-section {
                    height: 160px;
                    order: -2;
                }

                .chat-messages {
                    max-height: 100px;
                }

                .game-world-section {
                    min-height: 250px;
                    order: -1;
                }

                .mobile-tabs .nav-link {
                    font-size: 0.85rem;
                    padding: 0.5rem 0.75rem;
                }

                .inventory-scroll,
                .spells-scroll {
                    max-height: 200px;
                }

                .dungeon-map {
                    grid-template-columns: repeat(12, 1fr);
                    max-width: 100%;
                }

                .map-cell {
                    font-size: 0.6rem;
                    min-height: 20px;
                }

                .movement-btn {
                    width: 35px;
                    height: 35px;
                    font-size: 1rem;
                }
            }

            @media (max-width: 767.98px) {
                .game-world-section {
                    min-height: 200px;
                }

                .throne-icon i {
                    font-size: 3rem;
                }

                .character-avatar i {
                    font-size: 2rem;
                }

                .chat-section {
                    height: 140px;
                }

                .chat-messages {
                    max-height: 80px;
                }

                .map-cell {
                    font-size: 0.5rem;
                    min-height: 18px;
                }
            }

            @media (max-width: 575.98px) {
                .navbar-brand {
                    font-size: 1rem;
                }

                .badge {
                    font-size: 0.7rem;
                }

                .game-world-section {
                    min-height: 180px;
                }

                .throne-icon i {
                    font-size: 2.5rem;
                }

                .mobile-tabs .nav-link {
                    font-size: 0.75rem;
                    padding: 0.4rem 0.6rem;
                }

                .map-cell {
                    font-size: 0.4rem;
                    min-height: 15px;
                }

                .movement-btn {
                    width: 30px;
                    height: 30px;
                    font-size: 0.9rem;
                }
            }

            /* Animation keyframes */
            @keyframes glow {
                0%, 100% {
                    text-shadow: 0 0 5px var(--text-warning);
                    filter: drop-shadow(0 0 10px var(--text-warning));
                }
                50% {
                    text-shadow: 0 0 20px var(--text-warning), 0 0 30px var(--text-warning);
                    filter: drop-shadow(0 0 20px var(--text-warning));
                }
            }

            @keyframes reveal {
                from {
                    opacity: 0;
                    transform: scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .map-cell.revealing {
                animation: reveal 0.5s ease-out;
            }

            .throne-icon i {
                animation: glow 4s ease-in-out infinite;
            }

            /* Enhanced dark theme elements */
            .navbar {
                background: linear-gradient(90deg, rgba(26, 26, 26, 0.98) 0%, rgba(0, 0, 0, 0.99) 100%) !important;
                backdrop-filter: blur(10px);
            }

            .form-control:focus {
                background-color: var(--bg-dark-secondary) !important;
                border-color: var(--border-warning) !important;
                box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25) !important;
                color: var(--text-warning-light) !important;
            }

            .form-control::placeholder {
                color: rgba(255, 218, 106, 0.6) !important;
            }

            .map-view-toggle {
                position: absolute;
                top: 10px;
                right: 10px;
                z-index: 20;
            }
        </style>
    </head>
    <body class="bg-dark-custom">
        <!-- Top Navigation Header -->
        <header class="navbar navbar-dark bg-dark-header border-bottom border-warning">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <h1 class="navbar-brand mb-0 h1 text-warning">Realm of Shadows</h1>
                    <span class="badge bg-outline-warning ms-3">Campaign: The Lost Crown</span>
                </div>
                <nav class="d-flex gap-2">
                    <button class="btn btn-outline-warning btn-sm" id="toggleMapView" title="Toggle Map">
                        <i class="bi bi-map"></i>
                    </button>
                    <button class="btn btn-outline-warning btn-sm" title="Party">
                        <i class="bi bi-people"></i>
                    </button>
                    <button class="btn btn-outline-warning btn-sm" title="Settings">
                        <i class="bi bi-gear"></i>
                    </button>
                </nav>
            </div>
        </header>

        <div class="container-fluid p-0 game-container">
            <div class="row g-0 h-100">
                <!-- Chat Section - Top on Mobile -->
                <section class="col-12 chat-section d-flex flex-column order-lg-2">
                    <div class="chat-messages p-3">
                        <div class="chat-log">
                            <?php foreach ($chatLog as $entry): ?>
                                <div class="chat-message <?php echo $entry['type']; ?>">
                                    <?php if ($entry['type'] === 'system'): ?>
                                        <small class="text-warning-light fst-italic"><?php echo $entry['message']; ?></small>
                                    <?php elseif ($entry['type'] === 'player'): ?>
                                        <small class="text-info"><strong><?php echo $entry['name']; ?>:</strong> <?php echo $entry['message']; ?></small>
                                    <?php elseif ($entry['type'] === 'dm'): ?>
                                        <small class="text-light-purple"><strong>DM:</strong> <?php echo $entry['message']; ?></small>
                                    <?php elseif ($entry['type'] === 'roll'): ?>
                                        <small class="text-success">
                                            <i class="bi bi-dice-6 me-1"></i><strong><?php echo $entry['name']; ?></strong> <?php echo $entry['result']; ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <footer class="chat-input p-3 border-top border-warning">
                        <div class="input-group">
                            <input type="text" class="form-control bg-dark-input text-warning"
                                   placeholder="Type your message or action..." id="chatInput">
                            <button class="btn btn-warning" type="button" id="sendButton">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </footer>
                </section>

                <!-- Mobile Merged Sidebars -->
                <div class="col-12 d-lg-none mobile-sidebars order-1">
                    <div class="p-2">
                        <!-- Mobile Navigation Tabs -->
                        <nav>
                            <ul class="nav nav-tabs nav-dark mobile-tabs mb-2" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mobile-character-tab" role="tab">
                                        <i class="bi bi-person me-1"></i>Character
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#mobile-actions-tab" role="tab">
                                        <i class="bi bi-sword me-1"></i>Actions
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#mobile-inventory-tab" role="tab">
                                        <i class="bi bi-backpack me-1"></i>Items
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#mobile-spells-tab" role="tab">
                                        <i class="bi bi-star me-1"></i>Spells
                                    </button>
                                </li>
                            </ul>
                        </nav>

                        <div class="tab-content">
                            <!-- Mobile Character Tab -->
                            <section class="tab-pane fade show active" id="mobile-character-tab" role="tabpanel">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="character-avatar me-2">
                                                <i class="bi bi-person-circle text-warning" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-warning mb-0"><?php echo $character['name']; ?></h6>
                                                <small class="text-warning-light">L<?php echo $character['level']; ?> <?php echo $character['class']; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="ac-display p-2 rounded text-center">
                                            <small class="text-warning d-block">AC</small>
                                            <span class="h6 text-warning mb-0"><?php echo $character['ac']; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="text-warning d-block">Health: <?php echo $character['hp']; ?>/<?php echo $character['maxHp']; ?></small>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-danger" style="width: <?php echo ($character['hp'] / $character['maxHp']) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-warning d-block">Mana: <?php echo $character['mp']; ?>/<?php echo $character['maxMp']; ?></small>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary" style="width: <?php echo ($character['mp'] / $character['maxMp']) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Mobile Actions Tab -->
                            <section class="tab-pane fade" id="mobile-actions-tab" role="tabpanel">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button class="btn btn-outline-warning btn-sm w-100 action-btn" data-action="attack">
                                            <i class="bi bi-sword me-1"></i>Attack
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-outline-warning btn-sm w-100 action-btn" data-action="defend">
                                            <i class="bi bi-shield me-1"></i>Defend
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-outline-warning btn-sm w-100 action-btn" data-action="dash">
                                            <i class="bi bi-lightning me-1"></i>Dash
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-outline-warning btn-sm w-100 action-btn" data-action="help">
                                            <i class="bi bi-heart me-1"></i>Help
                                        </button>
                                    </div>
                                </div>
                                <article class="card bg-dark-card border-warning mt-2 d-none" id="mobile-selected-action">
                                    <div class="card-body p-2">
                                        <small class="text-warning">Selected: <span class="fw-bold" id="mobile-action-name">None</span></small>
                                        <button class="btn btn-warning btn-sm w-100 mt-1" id="mobileExecuteAction">Execute</button>
                                    </div>
                                </article>
                            </section>

                            <!-- Mobile Inventory Tab -->
                            <section class="tab-pane fade" id="mobile-inventory-tab" role="tabpanel">
                                <div class="inventory-scroll" style="max-height: 120px;">
                                    <?php foreach (array_slice($inventory, 0, 3) as $item): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1 p-1 rounded" style="background-color: rgba(17, 17, 17, 0.5);">
                                            <small class="<?php echo getRarityClass($item['rarity']); ?>"><?php echo $item['name']; ?></small>
                                            <?php if (isset($item['quantity'])): ?>
                                                <span class="badge bg-outline-warning"><?php echo $item['quantity']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <!-- Mobile Spells Tab -->
                            <section class="tab-pane fade" id="mobile-spells-tab" role="tabpanel">
                                <div class="spells-scroll" style="max-height: 120px;">
                                    <?php foreach (array_slice($spells, 0, 2) as $spell): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1 p-1 rounded" style="background-color: rgba(17, 17, 17, 0.5);">
                                            <div>
                                                <small class="text-info d-block"><?php echo $spell['name']; ?></small>
                                                <small class="text-warning-light">Level <?php echo $spell['level']; ?> • <?php echo $spell['cost']; ?> MP</small>
                                            </div>
                                            <button class="btn btn-outline-warning btn-sm cast-spell"
                                                    data-spell="<?php echo $spell['name']; ?>"
                                                    data-cost="<?php echo $spell['cost']; ?>"
                                                    <?php echo ($character['mp'] < $spell['cost']) ? 'disabled' : ''; ?>>
                                                Cast
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <!-- Left Sidebar - Character Information (Desktop Only) -->
                <aside class="col-lg-3 col-xl-3 character-sidebar d-none d-lg-block order-lg-1">
                    <div class="p-3 h-100 overflow-auto">
                        <!-- Character Profile Section -->
                        <section class="character-profile">
                            <article class="card bg-dark-card border-warning mb-3">
                                <header class="card-header bg-dark-header border-warning">
                                    <div class="d-flex align-items-center">
                                        <div class="character-avatar me-3">
                                            <i class="bi bi-person-circle text-warning"></i>
                                        </div>
                                        <div>
                                            <h2 class="card-title text-warning mb-0 h6"><?php echo $character['name']; ?></h2>
                                            <small class="text-warning-light">Level <?php echo $character['level']; ?> <?php echo $character['class']; ?></small>
                                        </div>
                                    </div>
                                </header>
                                <div class="card-body">
                                    <!-- Health and Mana Section -->
                                    <section class="vitals mb-3">
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-warning">
                                                    <i class="bi bi-heart-fill text-danger me-1"></i>Health
                                                </small>
                                                <small class="text-warning-light"><?php echo $character['hp']; ?>/<?php echo $character['maxHp']; ?></small>
                                            </div>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-danger" style="width: <?php echo ($character['hp'] / $character['maxHp']) * 100; ?>%"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-warning">
                                                    <i class="bi bi-lightning-fill text-primary me-1"></i>Mana
                                                </small>
                                                <small class="text-warning-light"><?php echo $character['mp']; ?>/<?php echo $character['maxMp']; ?></small>
                                            </div>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-primary" style="width: <?php echo ($character['mp'] / $character['maxMp']) * 100; ?>%"></div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Character Stats Section -->
                                    <section class="character-stats mb-3">
                                        <h3 class="text-warning mb-2 h6">Attributes</h3>
                                        <div class="row g-1">
                                            <?php foreach ($character['stats'] as $stat => $value): ?>
                                                <div class="col-6">
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-warning-light"><?php echo strtoupper(substr($stat, 0, 3)); ?></small>
                                                        <small class="text-warning"><?php echo $value; ?></small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </section>

                                    <!-- Armor Class Section -->
                                    <section class="armor-class">
                                        <div class="ac-display p-2 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-warning">
                                                    <i class="bi bi-shield-fill me-1"></i>Armor Class
                                                </small>
                                                <span class="h5 text-warning mb-0"><?php echo $character['ac']; ?></span>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </article>
                        </section>

                        <!-- Party Members Section -->
                        <section class="party-section">
                            <article class="card bg-dark-card border-warning">
                                <header class="card-header bg-dark-header border-warning">
                                    <h3 class="card-title text-warning mb-0 h6">Party</h3>
                                </header>
                                <div class="card-body">
                                    <?php foreach ($party as $member): ?>
                                        <div class="party-member mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-warning d-block"><?php echo $member['name']; ?></small>
                                                    <small class="text-warning-light"><?php echo $member['class']; ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-warning-light"><?php echo $member['hp']; ?>/<?php echo $member['maxHp']; ?></small>
                                                    <div class="progress progress-xs" style="width: 60px;">
                                                        <div class="progress-bar bg-danger" style="width: <?php echo ($member['hp'] / $member['maxHp']) * 100; ?>%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </article>
                        </section>
                    </div>
                </aside>

                <!-- Main Game Content -->
                <main class="col-12 col-lg-6 col-xl-6 game-main d-flex flex-column order-lg-3">
                    <!-- Game World Section -->
                    <section class="game-world-section p-3" id="gameWorldSection">
                        <article class="card bg-dark-card border-warning h-100">
                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <div class="throne-icon mb-3">
                                        <i class="bi bi-gem text-warning"></i>
                                    </div>
                                    <h2 class="text-warning mb-3">The Throne Room</h2>
                                    <p class="text-warning-light mb-4">
                                        Ancient tapestries hang from the walls, and a massive throne sits empty at the far end.
                                        Dust motes dance in the shafts of light filtering through stained glass windows.
                                        The air is thick with the weight of forgotten power, and shadows seem to move of their own accord.
                                    </p>
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button class="btn btn-outline-warning btn-action" data-action="investigate">
                                            <i class="bi bi-search me-1"></i>Investigate
                                        </button>
                                        <button class="btn btn-outline-warning btn-action" data-action="approach">
                                            <i class="bi bi-arrow-up me-1"></i>Approach Throne
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </section>

                    <!-- Dungeon Map Section (Hidden by default) -->
                    <section class="game-world-section p-3 d-none" id="dungeonMapSection">
                        <article class="card bg-dark-card border-warning h-100">
                            <header class="card-header bg-dark-header border-warning">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="text-warning mb-0 h6">Dungeon Map</h3>
                                    <small class="text-warning-light">Use WASD or arrow buttons to move</small>
                                </div>
                            </header>
                            <div class="card-body p-2">
                                <!-- Movement Controls -->
                                <div class="map-controls">
                                    <div class="d-flex flex-column align-items-center">
                                        <button class="btn btn-outline-warning movement-btn" id="moveUp" data-direction="up">
                                            <i class="bi bi-arrow-up"></i>
                                        </button>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-outline-warning movement-btn" id="moveLeft" data-direction="left">
                                                <i class="bi bi-arrow-left"></i>
                                            </button>
                                            <button class="btn btn-outline-warning movement-btn" id="moveDown" data-direction="down">
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                            <button class="btn btn-outline-warning movement-btn" id="moveRight" data-direction="right">
                                                <i class="bi bi-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dungeon Map Grid -->
                                <div class="dungeon-map" id="dungeonMap">
                                    <?php
                                    for ($row = 0; $row < 8; $row++) {
                                        for ($col = 0; $col < 12; $col++) {
                                            $cellType = $dungeonMap[$row][$col];
                                            $cellId = "cell-{$row}-{$col}";
                                            $icon = '';

                                            switch ($cellType) {
                                                case 'wall': $icon = '█';
                                                    break;
                                                case 'empty': $icon = '·';
                                                    break;
                                                case 'door': $icon = '▢';
                                                    break;
                                                case 'treasure': $icon = '💰';
                                                    break;
                                                case 'monster': $icon = '👹';
                                                    break;
                                                case 'trap': $icon = '⚠';
                                                    break;
                                                case 'boss': $icon = '👑';
                                                    break;
                                                case 'secret': $icon = '?';
                                                    break;
                                            }

                                            echo "<div class='map-cell {$cellType} fogged' id='{$cellId}' data-row='{$row}' data-col='{$col}' data-type='{$cellType}'>{$icon}</div>";
                                        }
                                    }
                                    ?>
                                </div>

                                <!-- Map Legend -->
                                <div class="map-legend">
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: linear-gradient(145deg, #0066CC 0%, #004499 100%);"></div>
                                        <span>Player</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: linear-gradient(145deg, #8B4513 0%, #654321 100%);"></div>
                                        <span>Door</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: linear-gradient(145deg, #FFD700 0%, #FFA500 100%);"></div>
                                        <span>Treasure</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: linear-gradient(145deg, #8B0000 0%, #4B0000 100%);"></div>
                                        <span>Monster</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: linear-gradient(145deg, #800080 0%, #4B0082 100%);"></div>
                                        <span>Trap</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: linear-gradient(145deg, #FF0000 0%, #8B0000 100%);"></div>
                                        <span>Boss</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: linear-gradient(145deg, #2F4F4F 0%, #1C3A3A 100%);"></div>
                                        <span>Secret</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </section>
                </main>

                <!-- Right Sidebar - Actions & Inventory (Desktop Only) -->
                <aside class="col-lg-3 col-xl-3 actions-sidebar d-none d-lg-block order-lg-4">
                    <div class="p-3 h-100">
                        <!-- Navigation Tabs -->
                        <nav>
                            <ul class="nav nav-tabs nav-dark mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#actions-tab" role="tab">Actions</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventory-tab" role="tab">
                                        <i class="bi bi-backpack"></i>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#spells-tab" role="tab">
                                        <i class="bi bi-star"></i>
                                    </button>
                                </li>
                            </ul>
                        </nav>

                        <div class="tab-content">
                            <!-- Actions Tab -->
                            <section class="tab-pane fade show active" id="actions-tab" role="tabpanel">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-warning text-start action-btn" data-action="attack">
                                        <i class="bi bi-sword me-2"></i>Attack
                                    </button>
                                    <button class="btn btn-outline-warning text-start action-btn" data-action="defend">
                                        <i class="bi bi-shield me-2"></i>Defend
                                    </button>
                                    <button class="btn btn-outline-warning text-start action-btn" data-action="dash">
                                        <i class="bi bi-lightning me-2"></i>Dash
                                    </button>
                                    <button class="btn btn-outline-warning text-start action-btn" data-action="help">
                                        <i class="bi bi-heart me-2"></i>Help Action
                                    </button>
                                </div>

                                <article class="card bg-dark-card border-warning mt-3 d-none" id="selected-action">
                                    <div class="card-body">
                                        <small class="text-warning">Selected: <span class="fw-bold" id="action-name">None</span></small>
                                        <button class="btn btn-warning btn-sm w-100 mt-2" id="executeAction">Execute Action</button>
                                    </div>
                                </article>
                            </section>

                            <!-- Inventory Tab -->
                            <section class="tab-pane fade" id="inventory-tab" role="tabpanel">
                                <div class="inventory-scroll">
                                    <?php foreach ($inventory as $item): ?>
                                        <article class="inventory-item card bg-dark-card border-warning mb-2">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="<?php echo getRarityClass($item['rarity']); ?> fw-medium"><?php echo $item['name']; ?></small>
                                                        <br><small class="text-warning-light"><?php echo $item['type']; ?></small>
                                                    </div>
                                                    <?php if (isset($item['quantity'])): ?>
                                                        <span class="badge bg-outline-warning"><?php echo $item['quantity']; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>

                            <!-- Spells Tab -->
                            <section class="tab-pane fade" id="spells-tab" role="tabpanel">
                                <div class="spells-scroll">
                                    <?php foreach ($spells as $spell): ?>
                                        <article class="spell-item card bg-dark-card border-warning mb-2">
                                            <div class="card-body p-2">
                                                <header class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-info fw-medium"><?php echo $spell['name']; ?></small>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-fire text-warning me-1"></i>
                                                        <small class="text-warning-light"><?php echo $spell['cost']; ?></small>
                                                    </div>
                                                </header>
                                                <footer class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-outline-warning">Level <?php echo $spell['level']; ?></span>
                                                    <button class="btn btn-outline-warning btn-sm cast-spell"
                                                            data-spell="<?php echo $spell['name']; ?>"
                                                            data-cost="<?php echo $spell['cost']; ?>"
                                                            <?php echo ($character['mp'] < $spell['cost']) ? 'disabled' : ''; ?>>
                                                        Cast
                                                    </button>
                                                </footer>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Dungeon Map Data (JavaScript copy for client-side logic);
            const dungeonMapData = <?php echo json_encode($dungeonMap); ?>;

            // Game state
            let playerPosition = {row: 1, col: 1}; // Starting position
            let revealedCells = new Set();
            let exploredCells = new Set();
            let isMapView = false;

            document.addEventListener("DOMContentLoaded", () => {
                // Initialize map
                initializeDungeonMap();

                // Action button functionality for both desktop and mobile
                const actionButtons = document.querySelectorAll(".action-btn");
                const selectedActionCard = document.getElementById("selected-action");
                const mobileSelectedActionCard = document.getElementById("mobile-selected-action");
                const actionNameSpan = document.getElementById("action-name");
                const mobileActionNameSpan = document.getElementById("mobile-action-name");
                const executeButton = document.getElementById("executeAction");
                const mobileExecuteButton = document.getElementById("mobileExecuteAction");

                actionButtons.forEach((button) => {
                    button.addEventListener("click", function () {
                        // Remove active class from all buttons
                        actionButtons.forEach((btn) => btn.classList.remove("active"));

                        // Add active class to clicked button
                        this.classList.add("active");

                        // Get action name from button text
                        const actionName = this.textContent.trim();
                        if (actionNameSpan)
                            ;
                        actionNameSpan.textContent = actionName;
                        if (mobileActionNameSpan)
                            ;
                        mobileActionNameSpan.textContent = actionName;

                        // Show selected action card
                        if (selectedActionCard)
                            ;
                        selectedActionCard.classList.remove("d-none");
                        if (mobileSelectedActionCard)
                            ;
                        mobileSelectedActionCard.classList.remove("d-none");
                    });
                });

                // Game world action buttons
                const gameActionButtons = document.querySelectorAll(".btn-action");
                gameActionButtons.forEach((button) => {
                    button.addEventListener("click", function () {
                        const action = this.getAttribute('data-action');
                        addChatMessage("player", "Thorin", `I want to ${action}.`);
                    });
                });

                // Map toggle functionality
                const toggleMapButton = document.getElementById("toggleMapView");
                const gameWorldSection = document.getElementById("gameWorldSection");
                const dungeonMapSection = document.getElementById("dungeonMapSection");

                toggleMapButton.addEventListener("click", () => {
                    isMapView = !isMapView;
                    if (isMapView) {
                        gameWorldSection.classList.add("d-none");
                        dungeonMapSection.classList.remove("d-none");
                        toggleMapButton.innerHTML = '<i class="bi bi-house"></i>';
                        toggleMapButton.title = "Return to Room";
                    } else {
                        gameWorldSection.classList.remove("d-none");
                        dungeonMapSection.classList.add("d-none");
                        toggleMapButton.innerHTML = '<i class="bi bi-map"></i>';
                        toggleMapButton.title = "Toggle Map";
                    }
                });

                // Movement controls
                const movementButtons = document.querySelectorAll(".movement-btn");
                movementButtons.forEach(button => {
                    button.addEventListener("click", () => {
                        const direction = button.getAttribute("data-direction");
                        movePlayer(direction);
                    });
                });

                // Keyboard controls
                document.addEventListener("keydown", (e) => {
                    if (!isMapView)
                        return;

                    switch (e.key.toLowerCase()) {
                        case 'w':
                        case 'arrowup':
                            e.preventDefault();
                            movePlayer('up');
                            break
                        case 's':
                        case 'arrowdown':
                            e.preventDefault();
                            movePlayer('down');
                            break
                        case 'a':
                        case 'arrowleft':
                            e.preventDefault();
                            movePlayer('left');
                            break
                        case 'd':
                        case 'arrowright':
                            e.preventDefault();
                            movePlayer('right');
                            break
                    }
                });

                // Chat functionality
                const chatInput = document.getElementById("chatInput");
                const sendButton = document.getElementById("sendButton");

                function addChatMessage(type, name, message) {
                    const chatLog = document.querySelector(".chat-log");
                    const messageDiv = document.createElement("div");
                    messageDiv.className = `chat-message ${type}`;

                    let messageContent = "";
                    switch (type) {
                        case "system":
                            messageContent = `<small class="text-warning-light fst-italic">${message}</small>`;
                            break;
                        case "player":
                            messageContent = `<small class="text-info"><strong>${name}:</strong> ${message}</small>`;
                            ;
                            break;
                        case "dm":
                            messageContent = `<small class="text-light-purple"><strong>DM:</strong> ${message}</small>`;
                            break;
                        case "roll":
                            messageContent = `<small class="text-success"><i class="bi bi-dice-6 me-1"></i><strong>${name}</strong> ${message}</small>`;
                            break;
                    }

                    messageDiv.innerHTML = messageContent;
                    chatLog.appendChild(messageDiv);

                    // Scroll to bottom
                    const chatMessages = document.querySelector(".chat-messages");
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }

                function sendMessage() {
                    const message = chatInput.value.trim();
                    if (message) {
                        addChatMessage("player", "Thorin", message);
                        chatInput.value = "";

                        // Simulate DM response after a delay
                        setTimeout(() => {
                            const responses = [
                                "Roll for initiative.",
                                "Make a perception check.",
                                "The shadows seem to move...",
                                "You hear footsteps echoing in the distance.",
                                "A mysterious figure appears.",
                                "The ancient magic stirs...",
                                "Roll a d20 for your action.",
                                "The throne begins to glow with an eerie light...",
                                "Something watches you from the darkness."
                            ];
                            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                            addChatMessage("dm", "", randomResponse);
                        }, 1000 + Math.random() * 2000);
                    }
                }

                sendButton.addEventListener("click", sendMessage);
                chatInput.addEventListener("keypress", (e) => {
                    if (e.key === "Enter") {
                        sendMessage();
                    }
                });

                // Spell casting functionality
                document.addEventListener("click", (e) => {
                    if (e.target.classList.contains("cast-spell")) {
                        const spellName = e.target.getAttribute('data-spell');
                        const spellCost = parseInt(e.target.getAttribute('data-cost'));

                        addChatMessage("player", "Thorin", `I cast ${spellName}!`);

                        // Simulate spell effect
                        setTimeout(() => {
                            addChatMessage("system", "", `${spellName} takes effect with a brilliant flash of light.`);

                            // Simulate dice roll for spell effect
                            setTimeout(() => {
                                const damage = Math.floor(Math.random() * 20) + 1;
                                addChatMessage("roll", "Thorin", `${spellName} deals ${damage} damage!`);
                            }, 800);
                        }, 500);
                    }
                });

                // Execute Action functionality for both desktop and mobile
                function executeAction() {
                    const actionName = (actionNameSpan ? actionNameSpan.textContent : mobileActionNameSpan.textContent);
                    addChatMessage("player", "Thorin", `I use ${actionName}!`);

                    // Hide selected action cards
                    if (selectedActionCard)
                        ;
                    selectedActionCard.classList.add("d-none");
                    if (mobileSelectedActionCard)
                        ;
                    mobileSelectedActionCard.classList.add("d-none");

                    // Remove active class from action buttons
                    actionButtons.forEach((btn) => btn.classList.remove("active"));

                    // Simulate action result
                    setTimeout(() => {
                        const results = [
                            `${actionName} is successful!`,
                            `Your ${actionName} has a powerful effect!`,
                            `The ${actionName} resonates through the chamber!`,
                            `The ancient magic responds to your ${actionName}!`
                        ];
                        const randomResult = results[Math.floor(Math.random() * results.length)];
                        addChatMessage("system", "", randomResult);
                    }, 600);
                }

                if (executeButton)
                    ;
                executeButton.addEventListener("click", executeAction);
                if (mobileExecuteButton)
                    ;
                mobileExecuteButton.addEventListener("click", executeAction);

                // Initialize Bootstrap tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                const tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Make addChatMessage globally accessible for map events
                window.addChatMessage = addChatMessage;
            });

            // Dungeon Map Functions
            function initializeDungeonMap() {
                // Set initial player position
                updatePlayerPosition();
                revealArea(playerPosition.row, playerPosition.col, 1);
            }

            function movePlayer(direction) {
                const newPos = {...playerPosition};

                switch (direction) {
                    case 'up':
                        newPos.row = Math.max(0, newPos.row - 1);
                        break
                    case 'down':
                        newPos.row = Math.min(7, newPos.row + 1);
                        break
                    case 'left':
                        newPos.col = Math.max(0, newPos.col - 1);
                        break
                    case 'right':
                        newPos.col = Math.min(11, newPos.col + 1);
                        break
                }

                // Check if movement is valid (not into walls);
                const targetCellType = dungeonMapData[newPos.row][newPos.col];
                if (targetCellType === 'wall') {
                    window.addChatMessage("system", "", "You cannot move through the wall.");
                    return;
                }

                // Mark old position as explored
                exploredCells.add(`${playerPosition.row}-${playerPosition.col}`);

                // Update player position
                playerPosition = newPos;
                updatePlayerPosition();

                // Reveal new area
                revealArea(playerPosition.row, playerPosition.col, 1);

                // Handle cell interactions
                handleCellInteraction(targetCellType);
            }

            function updatePlayerPosition() {
                // Remove player class from all cells
                document.querySelectorAll('.map-cell.player').forEach(cell => {
                    cell.classList.remove('player');
                });

                // Add player class to current position
                const currentCell = document.getElementById(`cell-${playerPosition.row}-${playerPosition.col}`);
                if (currentCell) {
                    currentCell.classList.add('player');
                    currentCell.innerHTML = '🛡️'; // Player icon
                }
            }

            function revealArea(centerRow, centerCol, radius) {
                for (let row = Math.max(0, centerRow - radius); row <= Math.min(7, centerRow + radius); row++) {
                    for (let col = Math.max(0, centerCol - radius); col <= Math.min(11, centerCol + radius); col++) {
                        const cellId = `cell-${row}-${col}`;
                        const cell = document.getElementById(cellId);
                        const cellKey = `${row}-${col}`;

                        if (cell && !revealedCells.has(cellKey)) {
                            cell.classList.remove('fogged');
                            cell.classList.add('revealed', 'revealing');
                            revealedCells.add(cellKey);

                            // Remove revealing animation after it completes
                            setTimeout(() => {
                                cell.classList.remove('revealing');
                            }, 500);
                        }
                    }
                }

                // Mark explored cells with reduced opacity
                exploredCells.forEach(cellKey => {
                    const cell = document.getElementById(`cell-${cellKey}`);
                    if (cell && cellKey !== `${playerPosition.row}-${playerPosition.col}`) {
                        cell.classList.add('explored');
                    }
                });
            }

            function handleCellInteraction(cellType) {
                switch (cellType) {
                    case 'empty':
                        window.addChatMessage("system", "", "You move through an empty corridor.");
                        break
                    case 'door':
                        window.addChatMessage("system", "", "You approach a heavy wooden door. It creaks as you pass through.");
                        break
                    case 'treasure':
                        window.addChatMessage("system", "", "✨ You discover a treasure chest! Gold coins glitter in the darkness.");
                        window.addChatMessage("roll", "Thorin", "Found 50 gold pieces!");
                        break
                    case 'monster':
                        window.addChatMessage("system", "", "⚔️ A monster lurks in the shadows! Combat begins!");
                        window.addChatMessage("dm", "", "Roll for initiative!");
                        break
                    case 'trap':
                        window.addChatMessage("system", "", "⚠️ You trigger a trap! Make a Dexterity saving throw.");
                        setTimeout(() => {
                            const roll = Math.floor(Math.random() * 20) + 1;
                            if (roll >= 15) {
                                window.addChatMessage("roll", "Thorin", `Dexterity Save: ${roll} (Success!) You dodge the trap.`);
                            } else {
                                window.addChatMessage("roll", "Thorin", `Dexterity Save: ${roll} (Failed!) You take 1d6 damage.`);
                                const damage = Math.floor(Math.random() * 6) + 1;
                                window.addChatMessage("system", "", `You take ${damage} piercing damage from the trap.`);
                            }
                        }, 1000);
                        break
                    case 'boss':
                        window.addChatMessage("system", "", "👑 You enter the boss chamber! The air crackles with dark energy.");
                        window.addChatMessage("dm", "", "The Ancient Lich rises from its throne, eyes glowing with malevolent power!");
                        break
                    case 'secret':
                        window.addChatMessage("system", "", "🔍 You discover a secret room! Ancient runes glow on the walls.");
                        window.addChatMessage("roll", "Thorin", "Investigation: You find a hidden magical item!");
                        break
                }
            }

            // Map cell click functionality
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('map-cell') && !e.target.classList.contains('fogged')) {
                    const row = parseInt(e.target.getAttribute('data-row'));
                    const col = parseInt(e.target.getAttribute('data-col'));
                    const cellType = e.target.getAttribute('data-type');

                    // Calculate distance from player
                    const distance = Math.abs(row - playerPosition.row) + Math.abs(col - playerPosition.col);

                    if (distance === 1 && cellType !== 'wall') {
                        // Adjacent cell - move there
                        const direction = getDirection(playerPosition.row, playerPosition.col, row, col);
                        movePlayer(direction);
                    } else if (distance === 0) {
                        // Current cell - examine
                        window.addChatMessage("player", "Thorin", "I examine my current location.");
                        handleCellInteraction(cellType);
                    } else {
                        // Too far - can't reach
                        window.addChatMessage("system", "", "That location is too far away to reach in one move.");
                    }
                }
            });

            function getDirection(fromRow, fromCol, toRow, toCol) {
                if (toRow < fromRow)
                    return 'up';
                if (toRow > fromRow)
                    return 'down';
                if (toCol < fromCol)
                    return 'left';
                if (toCol > fromCol)
                    return 'right';
            }
        </script>
    </body>
</html>
