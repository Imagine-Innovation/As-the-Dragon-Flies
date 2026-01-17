<?php
/** @var \yii\web\View $this */
$this->title = 'Custom Icons';
$this->params['breadcrumbs'][] = $this->title;

$devIcons = false;

if ($devIcons) {
    $icons = [
        'dungeon-gate-svgrepo-com', 'goblin-head-svgrepo-com', 'gargoyle-svgrepo-com', 'hatchet-svgrepo-com',
        'horse-head-svgrepo-com', 'man-doing-pushups-svgrepo-com', 'man-jumping-up-svgrepo-com', 'iying-down-svgrepo-com',
        'woman-looking-by-a-spyglass-svgrepo-com', 'man-riding-a-horse-svgrepo-com',
        'daggers-svgrepo-com', 'falling-svgrepo-com', 'wyvern-svgrepo-com',
        'secret-book-svgrepo-com', 'set-kara-svgrepo-com', 'skeleton-16-svgrepo-com',
        'stake-hammer-svgrepo-com', 'stringed-isntrument-violin-viola-cello-svgrepo-com',
        'troll-svgrepo-com', 'tshirt-svgrepo-com', 'white-book-svgrepo-com',
        'bad-gnome', 'basic-silhouette', 'battle', 'book-solid', 'bookmark',
        'crossed-swords', 'crystal-shrine', 'hood', 'knight-helmet', 'knight',
        'pirate-flag', 'robe',
    ];
} else {
    $icons = [
        // Misc
        'd20', 'apothecary', 'badge', 'banner', 'castle', 'castle2', 'castle3', 'chest',
        'coins', 'coins2', 'danger', 'dungeon', 'equipment', 'fire', 'horse',
        'pirate', 'power-off', 'spell', 'spell-book', 'tent', 'tower', 'treble-clef',
        'trophy', 'scroll', 'gate', 'ghost', 'logo', 'skull', 'trap',
        // items
        'candle', 'cauldron', 'crown', 'crown2', 'diamond', 'key', 'key2',
        'potion', 'pouch', 'ring', 'ring-diamond', 'rope', 'magic-wand', 'magic-wand2',
        // actions
        'action-attack', 'action-climb', 'action-fight', 'action-hide',
        'action-inventory', 'action-move', 'action-prepare-spell', 'action-pull-lever',
        'action-rest', 'action-run', 'action-unarmed-strike',
        'action-smash-down',
        // armors
        'armor', 'armor-plate', 'armor-shield', 'armor-round-shield',
        'armor-helmet', 'armor-helmet-large', 'armor-helmet-plume',
        'armor-spartan',
        // weapons
        'weapon', 'weapon-arrows', 'weapon-axe', 'weapon-crossbow',
        'weapon-glaive', 'weapon-sword', 'weapon-bow',
        // classes
        'class-barbarian', 'class-bard', 'class-rogue', 'class-sorcerer',
        'class-wizard',
        // races
        'race-dwarf', 'race-elf', 'race-orc',
        // monsters
        'monster', 'monster2', 'monster-dragon',
        // conditions
        'condition-stunned',
    ];
}
?>
<div class="container-fluid">
    <div class="row g-4">
        <?php foreach ($icons as $icon): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><?= $icon ?></h4>
                        <div class="actions">
                            <a role="button" class="actions__item dnd-<?= $icon ?>" href="#"></a>
                        </div>
                        <i class="bi dnd-<?= $icon ?> h1"></i>
                        <i class="bi dnd-<?= $icon ?> h2"></i>
                        <i class="bi dnd-<?= $icon ?> h3"></i>
                        <i class="bi dnd-<?= $icon ?> h4"></i>
                        <i class="bi dnd-<?= $icon ?> h5"></i>
                        <i class="bi dnd-<?= $icon ?> h6"></i>
                        <i class="bi dnd-<?= $icon ?>"></i>
                        <i class="bi dnd-<?= $icon ?>" style="color: var(--yellow);"></i>
                        <br>
                        <a class="btn btn--icon" href=''>
                            <i class="bi dnd-<?= $icon ?>"></i>
                        </a>
                        <div class="btn-group">
                            <a type="button" class="btn btn-theme" href="#">
                                <i class="bi dnd-<?= $icon ?>"></i>
                            </a>
                        </div>
                        <p>&lt;i class=&quot;bi dnd-<?= $icon ?>&quot;&gt;&lt;/i&gt;</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
