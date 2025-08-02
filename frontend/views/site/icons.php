<?php
$this->title = 'Custom Icons';
$this->params['breadcrumbs'][] = $this->title;

$icons = ['dev',
    'd20', 'badge', 'chest', 'castel', 'large-castel', 'crown', 'danger', 'diamond', 'diamond-ring', 'key', 'key2',
    'pirate', 'ring', 'spell-book', 'tent', 'tower',
    'action-rest', 'action-run', 'action-fight', 'action-prepare-spell', 'action-hide', 'action-move', 'action-climb',
    'action-unarmed-strike', 'action-attack',
    'armor', 'armor-plate', 'armor-shield', 'armor-round-shield', 'armor-helmet', 'armor-helmet-large', 'armor-helmet-plume', 'armor-spartan',
    'weapon', 'weapon-arrows', 'weapon-sword', 'weapon-bow',
    'class-barbarian', 'class-bard', 'class-rogue', 'class-sorcerer', 'class-wizard',
];
?>
<div class="container-fluid">
    <div class="row g-4">
        <?php foreach ($icons as $icon): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><?= $icon ?></h4>
                        <div class="actions">
                            <a type="button" class="actions__item dnd-<?= $icon ?>" href="#"></a>
                        </div>
                        <i class="bi dnd-<?= $icon ?> h1"></i>
                        <i class="bi dnd-<?= $icon ?> h2"></i>
                        <i class="bi dnd-<?= $icon ?> h3"></i>
                        <i class="bi dnd-<?= $icon ?> h4"></i>
                        <i class="bi dnd-<?= $icon ?> h5"></i>
                        <i class="bi dnd-<?= $icon ?> h6"></i>
                        <i class="bi dnd-<?= $icon ?>"></i>
                        <br>
                        <a class="btn btn-theme-dark btn--icon" href="">
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
