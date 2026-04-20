<?php

use common\helpers\WebResourcesHelper;
use common\widgets\MarkDown;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var yii\web\View $this */
/* @var common\models\Story $story */
/* @var common\models\Chapter[] $chapters */
$this->title = $story->name;
$this->params['breadcrumbs'][] = ['label' => 'Stories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$storyRoot = WebResourcesHelper::storyRootPath($story->id);

// Language labels (extend as needed)
$lang = $story->language ?? 'en';
$labels = [
    'en' => [
        'story' => 'Story',
        'level_range' => 'Level Range',
        'players' => 'Players',
        'tags' => 'Tags',
        'chapter' => 'Chapter',
        'mission' => 'Mission',
        'npcs' => 'NPCs',
        'actions' => 'Actions',
        'decor' => 'Decor',
        'monsters' => 'Monsters',
        'traps' => 'Traps',
        'hidden_items' => 'Hidden Items',
        'dialog' => 'Dialog',
        'player_reply' => 'Player reply',
        'outcome' => 'Outcome',
        'status' => 'Status',
        'success' => 'Success',
        'partial' => 'Partial',
        'failure' => 'Failure',
        'any_status' => 'Any',
        'gained_xp' => 'XP Gained',
        'gained_gp' => 'GP Gained',
        'gained_item' => 'Item Gained',
        'hp_loss' => 'HP Loss',
        'dc' => 'DC',
        'partial_dc' => 'Partial DC',
        'required_item' => 'Required Item',
        'action_type' => 'Action Type',
        'skills' => 'Skills',
        'damage' => 'Damage',
        'found_chance' => 'Found Chance',
        'identified_chance' => 'Identified Chance',
        'is_team_trap' => 'Team Trap',
        'image' => 'Image',
        'audio' => 'Audio',
        'next_mission' => 'Next mission',
    ],
    'fr' => [
        'story' => 'Histoire',
        'level_range' => 'Niveaux',
        'players' => 'Joueurs',
        'tags' => 'Mots-clés',
        'chapter' => 'Chapitre',
        'mission' => 'Mission',
        'npcs' => 'Personnages non joueur',
        'actions' => 'Actions',
        'decor' => 'Décor',
        'monsters' => 'Monstres',
        'traps' => 'Pièges',
        'hidden_items' => 'Objets cachés',
        'dialog' => 'Dialogue',
        'player_reply' => 'Réponse du joueur',
        'outcome' => 'Résultat',
        'status' => 'Statut',
        'success' => 'Succès',
        'partial' => 'Partiel',
        'failure' => 'Échec',
        'any_status' => 'Tout',
        'gained_xp' => 'XP gagné',
        'gained_gp' => 'PO gagnée(s)',
        'gained_item' => 'Objet gagné',
        'hp_loss' => 'PV perdu(s)',
        'dc' => 'DC',
        'partial_dc' => 'DD partiel',
        'required_item' => 'Objet requis',
        'action_type' => 'Type d\'action',
        'skills' => 'Compétences',
        'damage' => 'Dégâts',
        'found_chance' => 'Chance de découverte',
        'identified_chance' => 'Chance d\'identification',
        'is_team_trap' => 'Piège collectif',
        'image' => 'Image',
        'audio' => 'Audio',
        'next_mission' => 'Mission suivante',
    ],
];

$t = match ($lang) {
    'fr' => $labels['fr'],
    default => $labels['en'],
};
?>
<style>
    /* Print Optimization */
    @media print {
        .no-print, .main-nav, nav, footer, header, .offcanvas, .navbar, #mainNavContent, #sidebar {
            display: none !important;
        }
        .container {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        body {
            background-color: white !important;
            color: black;
            font-size: 12pt;
        }
        article, h1, h2, h3, h4, h5, h6, p, div {
            color: black;
        }
        .chapter {
            break-before: page;
        }
    }
</style>
<div class="story-view container-fluid py-4">
    <?=
    $this->renderFile('@app/views/story/snippets/print-story.php', [
        'story' => $story,
        'storyRoot' => $storyRoot,
        't' => $t,
    ])
    ?>

    <?php foreach ($chapters as $chapter): ?>
        <section class="chapter mb-5">
            <h2><?= $t['chapter'] ?> <?= $chapter->chapter_number ?> - <?= Html::encode($chapter->name) ?></h2>
            <?php if ($chapter->image): ?>
                <div class="mb-2">
                    <img src="<?= $storyRoot ?>/img/<?= $chapter->image ?>" class="img-fluid object-fit-cover rounded" alt="<?= $chapter->name ?>" style="max-height:150px;">
                </div>
            <?php endif; ?>
            <?= MarkDown::widget(['content' => $chapter->description ?? '']) ?>

            <?php
            $missions = $chapter->missions;
            foreach ($missions as $mission):
                // Load mission details
                $npcs = $mission->npcs;
                $actions = $mission->actions;
                $decors = $mission->decors;
                $monsters = $mission->monsters;
                $passages = $mission->passages;
                // Traps are linked via decor, but we can also list them directly from decor
                ?>
                <article class="mission mb-4">
                    <h3><?= $t['mission'] ?>: <?= Html::encode($mission->name) ?></h3>
                    <?php if ($mission->image): ?>
                        <div class="mb-2">
                            <img src="<?= $storyRoot ?>/img/<?= $mission->image ?>" class="img-fluid object-fit-cover rounded" alt="<?= $mission->name ?>" style="max-height:150px;">
                        </div>
                    <?php endif; ?>
                    <?= MarkDown::widget(['content' => $mission->description ?? '']) ?>

                    <!-- NPCs -->
                    <?php if ($npcs): ?>
                        <h4><?= $t['npcs'] ?></h4>
                        <?php foreach ($npcs as $npc): ?>
                            <div class="mb-3">
                                <h5><?= Html::encode($npc->name) ?></h5>
                                <?php if ($npc->image): ?>
                                    <?= Html::img(Url::to('@web/images/' . $npc->image), ['class' => 'img-thumbnail float-end', 'style' => 'max-width:100px;']) ?>
                                <?php endif; ?>
                                <?= MarkDown::widget(['content' => $npc->description ?? '']) ?>
                                <ul>
                                    <li><span class="fw-bold">Type:</span> <?= Html::encode($npc->npcType?->name ?? 'Commoner') ?>
                                        <?php if (!empty($npc->npcType?->description)): ?>
                                            <div class="ms-3 small"><?= MarkDown::widget(['content' => $npc->npcType->description ?? '']) ?></div>
                                        <?php endif; ?>
                                    </li>
                                    <li><span class="fw-bold">Language:</span> <?= Html::encode($npc->language->name ?? 'Common') ?></li>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Actions -->
                    <?php if ($actions): ?>
                        <h4><?= $t['actions'] ?></h4>
                        <?php foreach ($actions as $action): ?>
                            <hr />
                            <div class="mb-3">
                                <h5><?= Html::encode($action->name) ?></h5>
                                <?= MarkDown::widget(['content' => $action->description ?? '']) ?>
                                <ul class="list-unstyled">
                                    <li><span class="fw-bold"><?= $t['action_type'] ?>:</span>
                                        <?= Html::encode($action->actionType?->name ?? 'N/A') ?>
                                        <?php if (!empty($action->actionType?->description)): ?>
                                            <div class="ms-3 small"><?= MarkDown::widget(['content' => $action->actionType->description ?? '']) ?></div>
                                        <?php endif; ?>
                                    </li>
                                    <li><span class="fw-bold"><?= $t['dc'] ?>:</span> <?= $action->dc ?>
                                        <?php if ($action->partial_dc): ?> (<?= $t['partial_dc'] ?>: <?= $action->partial_dc ?>)<?php endif; ?></li>
                                    <li><span class="fw-bold">Free action:</span> <?= $action->is_free ? 'Yes' : 'No' ?></li>
                                    <?php
                                    // Show related elements
                                    if ($action->passage_id) {
                                        echo '<li>Passage: ' . Html::encode($action->passage?->name ?? '') . '</li>';
                                    }
                                    if ($action->decor_id) {
                                        echo '<li>Decor: ' . Html::encode($action->decor?->name ?? '') . '</li>';
                                    }
                                    if ($action->decor_item_id) {
                                        echo '<li>Hidden item: ' . Html::encode($action->decorItem?->name ?? '') . '</li>';
                                    }
                                    if ($action->npc_id) {
                                        echo '<li>NPC: ' . Html::encode($action->npc?->name ?? '') . '</li>';
                                        if ($action->npc?->first_dialog_id) {
                                            echo "<h6>{$t['dialog']}</h6><div class=\"dialog-tree\">";
                                            echo $this->renderFile('@app/views/mission/snippets/dialog.php', ['dialog' => $action->npc->firstDialog]);
                                            echo "</div>";
                                        }
                                    }
                                    if ($action->reply_id) {
                                        echo '<li>First reply: ' . Html::encode($action->reply?->text ?? '') . '</li>';
                                    }
                                    if ($action->trap_id) {
                                        echo '<li>Trap: ' . Html::encode($action->trap?->name ?? '') . '</li>';
                                    }
                                    if ($action->required_item_id) {
                                        echo '<li>' . $t['required_item'] . ': ' . Html::encode($action->requiredItem?->name ?? '') . '</li>';
                                    }
                                    // Skills via action_type_skill
                                    $skills = $action->actionType ? $action->actionType->skills : [];
                                    if ($skills) {
                                        echo '<li>' . $t['skills'] . ': ' . implode(', ', array_map(function ($s) {
                                                    return $s->name;
                                                }, $skills)) . '</li>';
                                    }
                                    ?>
                                </ul>

                                <!-- Outcomes -->
                                <?php if ($action->outcomes): ?>
                                    <h6><?= $t['outcome'] ?>s</h6>
                                    <?=
                                    $this->renderFile('@app/views/story/snippets/print-outcomes.php', [
                                        'outcomes' => $action->outcomes,
                                        't' => $t,
                                    ])
                                    ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Decor and Traps -->
                    <?php if ($decors): ?>
                        <h4><?= $t['decor'] ?></h4>
                        <?php foreach ($decors as $decor): ?>
                            <div class="mb-3">
                                <h5><?= Html::encode($decor->name) ?></h5>
                                <?php if ($decor->image): ?>
                                    <?= Html::img(Url::to('@web/images/' . $decor->image), ['class' => 'img-thumbnail float-end', 'style' => 'max-width:100px;']) ?>
                                <?php endif; ?>
                                <?= MarkDown::widget(['content' => $decor->description ?? '']) ?>

                                <!-- Hidden Items -->
                                <?php if ($decor->decorItems): ?>
                                    <h6><?= $t['hidden_items'] ?></h6>
                                    <ul>
                                        <?php foreach ($decor->decorItems as $decorItem): ?>
                                            <li>
                                                <span class="fw-bold"><?= Html::encode($decorItem->name) ?></span>
                                                (<?= $t['found_chance'] ?>: <?= $decorItem->found ?>%,
                                                <?= $t['identified_chance'] ?>: <?= $decorItem->identified ?>%)
                                                <?php if ($decorItem->description): ?><div><?= MarkDown::widget(['content' => $decorItem->description]) ?></div><?php endif; ?>
                                                <?php if ($decorItem->item): ?>
                                                    <br>Item: <?= Html::encode($decorItem->item->name) ?>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>

                                <!-- Traps -->
                                <?php if ($decor->traps): ?>
                                    <h6><?= $t['traps'] ?></h6>
                                    <ul>
                                        <?php foreach ($decor->traps as $trap): ?>
                                            <li>
                                                <span class="fw-bold"><?= Html::encode($trap->name) ?></span>
                                                <?= $t['damage'] ?>: <?= $trap->damage ?> <?= Html::encode($trap->damageType?->name ?? '') ?>
                                                <?= $t['found_chance'] ?>: <?= $trap->found ?>%
                                                <?= $t['is_team_trap'] ?>:
                                                <?=
                                                $trap->is_team_trap ? 'Yes' : 'No'
                                                ?>
                                            </li>
                                            <li>
                                                <?php if ($trap->description): ?>
                                                    <?= MarkDown::widget(['content' => $trap->description ?? '']) ?>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Monsters -->
                    <?php if ($monsters): ?>
                        <h4><?= $t['monsters'] ?></h4>
                        <?php foreach ($monsters as $monster): ?>
                            <div class="mb-3">
                                <h5><?= Html::encode($monster->name) ?></h5>
                                <?php if ($monster->image): ?>
                                    <?= Html::img(Url::to('@web/images/' . $monster->image), ['class' => 'img-thumbnail float-end', 'style' => 'max-width:100px;']) ?>
                                <?php endif; ?>
                                <?= MarkDown::widget(['content' => $monster->description ?? '']) ?>
                                <p>
                                    <?= $t['found_chance'] ?>: <?= $monster->found ?>% |
                                    <?= $t['identified_chance'] ?>: <?= $monster->identified ?>%
                                </p>
                                <?php
                                $creature = $monster->creature;
                                if ($creature):
                                    ?>
                                    <p><span class="fw-bold">Creature:</span> <?= Html::encode($creature->name) ?> (CR <?= $creature->cr ?>)</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Passages (if any) -->
                    <?php if ($passages): ?>
                        <h4>Passages</h4>
                        <ul>
                            <?php foreach ($passages as $passage): ?>
                                <li>
                                    <span class="fw-bold"><?= Html::encode($passage->name) ?></span> (found: <?= $passage->found ?>%)
                                    <?php if ($passage->description): ?>
                                        <?= MarkDown::widget(['content' => $passage->description ?? '']) ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                </article>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>
