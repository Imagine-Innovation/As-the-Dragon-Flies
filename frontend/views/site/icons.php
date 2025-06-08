<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */
$icons = [
    'd20', 'chest',
    'action-rest', 'action-run', 'action-fight', 'action-prepare-spell', 'action-hide', 'action-move', 'action-climb',
    'armor-plate', 'armor-shield', 'armor-helmet',
    'weapon-sword', 'weapon-bow',
    'class-wizard', 'class-artificer', 'class-barbarian', 'class-bard', 'class-rogue', 'class-sorcerer',
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
                            <a type="button" class="actions__item bi-<?= $icon ?>" href="#"></a>
                        </div>
                        <i class="bi bi-<?= $icon ?> h1"></i>
                        <i class="bi bi-<?= $icon ?> h2"></i>
                        <i class="bi bi-<?= $icon ?> h3"></i>
                        <i class="bi bi-<?= $icon ?> h4"></i>
                        <i class="bi bi-<?= $icon ?> h5"></i>
                        <i class="bi bi-<?= $icon ?> h6"></i>
                        <i class="bi bi-<?= $icon ?>"></i>
                        <br>
                        <a class="btn btn-theme-dark btn--icon" href="">
                            <i class="bi bi-<?= $icon ?>"></i>
                        </a>
                        <div class="btn-group">
                            <a type="button" class="btn btn-theme" href="#">
                                <i class="bi bi-<?= $icon ?>"></i>
                            </a>
                        </div>
                        <p>&lt;i class=&quot;bi bi-<?= $icon ?>&quot;&gt;&lt;/i&gt;</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>