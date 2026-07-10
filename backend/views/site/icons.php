<?php
/** @var \yii\web\View $this */
/** @var bool $devMode */
$this->title = 'Custom Icons';
$this->params['breadcrumbs'][] = $this->title;

$devIcons = $devMode ?? false;

if ($devIcons) {
    $icons = [
        'bad-gnome',
        'basic-silhouette',
        'battle',
        'book-solid',
        'bookmark',
        'card-pickup',
        'chess-rook',
        'coin-pouch',
        'coins-stacked-on-a-hand-palm',
        'coins',
        'compress',
        'crossed-swords',
        'crystal-shrine',
        'daggers',
        'dice',
        'dragon',
        'dungeon-gate',
        'falling',
        'gargoyle',
        'ghost',
        'gifts',
        'goblin-head',
        'guard',
        'hand-heart-valentine',
        'hand-holding-medical',
        'hand-holding-up-a-book',
        'hand-holding-up-a-gear',
        'hand-holding-up-a-hammer',
        'hand-holding-up-a-key',
        'hand-holding-up-a-pen',
        'hand-holding-up-a-sack-of-money',
        'hand-holding-up-a-wand',
        'hand-holding-vote-paper',
        'hand-switch',
        'handle-with-care',
        'hands-helping',
        'hatchet',
        'hood',
        'horse-head',
        'iying-down',
        'knight-helmet',
        'knight',
        'man-doing-pushups',
        'man-jumping-up',
        'man-riding-a-horse',
        'pirate-flag',
        'potion',
        'praying-hands',
        'robe',
        'secret-book',
        'set-kara',
        'silence-silent',
        'skeleton-16',
        'smell',
        'stake-hammer',
        'stringed-isntrument-violin-viola-cello',
        'swimmer',
        'troll',
        'tshirt',
        'white-book',
        'woman-looking-by-a-spyglass',
        'wyvern',
    ];
} else {
    $icons = [
        // Misc
        'd20',
        'apothecary',
        'badge',
        'banner',
        'castle',
        'castle2',
        'castle3',
        'chest',
        'coins',
        'coins2',
        'danger',
        'dungeon',
        'equipment',
        'fire',
        'horse',
        'pirate',
        'power-off',
        'spell',
        'spell-book',
        'tent',
        'tower',
        'treble-clef',
        'trophy',
        'scroll',
        'gate',
        'ghost',
        'logo',
        'skull',
        'trap',
        // items
        'candle',
        'cauldron',
        'crown',
        'crown2',
        'diamond',
        'key',
        'key2',
        'potion',
        'pouch',
        'ring',
        'ring-diamond',
        'rope',
        'magic-wand',
        'magic-wand2',
        // actions
        'action-attack',
        'action-climb',
        'action-dig',
        'action-fight',
        'action-hide',
        'action-inventory',
        'action-move',
        'action-prepare-spell',
        'action-pull-lever',
        'action-rest',
        'action-run',
        'action-unarmed-strike',
        'action-smash-down',
        // armors
        'armor',
        'armor-plate',
        'armor-shield',
        'armor-round-shield',
        'armor-helmet',
        'armor-helmet-large',
        'armor-helmet-plume',
        'armor-spartan',
        // weapons
        'weapon',
        'weapon-arrows',
        'weapon-axe',
        'weapon-crossbow',
        'weapon-glaive',
        'weapon-sword',
        'weapon-bow',
        // classes
        'class-barbarian',
        'class-bard',
        'class-rogue',
        'class-sorcerer',
        'class-wizard',
        // races
        'race-dwarf',
        'race-elf',
        'race-orc',
        // monsters
        'monster',
        'monster2',
        'monster-dragon',
        // conditions
        'condition-stunned',
    ];
}
?>
<div class="container">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 row-cols-3xl-6 g-4">
        <?php foreach ($icons as $icon): ?>
            <div class="col">
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
                        <a class="btn btn--icon" href="">
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
