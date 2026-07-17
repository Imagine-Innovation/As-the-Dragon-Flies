<?php
/** @var \yii\web\View $this */
/** @var bool $devMode */
$this->title = 'Custom Icons';
$this->params['breadcrumbs'][] = $this->title;

$devIcons = $devMode ?? false;

if ($devIcons) {
    $icons = [
        'bad-gnome',
        'barrel',
        'basic-silhouette',
        'battle',
        'believer-religion-pray-believe',
        'book-solid',
        'bookmark',
        'brush',
        'button-choice',
        'card-pickup',
        'cherish',
        'chess-rook',
        'clean-hands',
        'coin-pouch',
        'coins-stacked-on-a-hand-palm',
        'coins',
        'compress',
        'crossed-swords',
        'crystal-shrine',
        'daggers',
        'destroy-ruin-wreck-break',
        'dice',
        'dragon',
        'dungeon-gate',
        'exit-2',
        'exit-vector-1',
        'fall-trip-tumble-go-down',
        'falling',
        'fist-cross-dictator-bang',
        'Flame-Sharp--Streamline-Ionic-Sharp',
        'gargoyle',
        'ghost',
        'gifts',
        'goblin-head',
        'guard',
        'hammer-break',
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
        'hands-praying-solid-full',
        'hatchet',
        'hood',
        'horse-head',
        'iying-down',
        'jumping-dancer',
        'knight-helmet',
        'knight',
        'knot',
        'lips-beauty-pretty-sex',
        'man-doing-pushups',
        'man-jumping-up',
        'man-lifting-an-old-man',
        'man-riding-a-horse',
        'person-praying-solid-full',
        'pirate-flag',
        'potion',
        'praying-hands',
        'read-news-learn-understand-paper',
        'reading-a-book-learn-understand-forecast',
        'robbery',
        'robe',
        'savings-break-down',
        'secret-book',
        'set-kara',
        'silence-silent',
        'skeleton-16',
        'smell',
        'stake-hammer',
        'stringed-isntrument-violin-viola-cello',
        'swimmer',
        'swimming',
        'sword-brandish',
        'theft-crime-steal-thief',
        'troll',
        'tshirt',
        'white-book',
        'woman-looking-by-a-spyglass',
        'wyvern',
        'xxclimbing-with-rope',
        'XXXbroadsword',
        'xxxcastle-emblem',
        'xxxhill-fort',
        'xxxman-playing-a-flute',
        'xxxman-sitting-and-reading-book',
        'xxxrope-2',
        'yoga',
        'yogi-exercise-yoga-headstand',
        'court',
        'finger-gun',
        'handcuffs',
        'paying-for-college',
        'rule',
        'scale',
        'poison',
        'learn',
        'point',
        'medical-poison',
        'map-library',
        'legal-gavel',
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
        'action-pray',
        'action-prepare-spell',
        'action-pull-lever',
        'action-rest',
        'action-run',
        'action-smash-down',
        'action-swim',
        'action-take',
        'action-touch',
        'action-unarmed-strike',
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
                        <p class="action-btn__name text-warning">
                            <i class="bi dnd-<?= $icon ?>" aria-hidden="true"></i> Inline icon with some text
                        </p>
                        <p>&lt;i class=&quot;bi dnd-<?= $icon ?>&quot;&gt;&lt;/i&gt;</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
