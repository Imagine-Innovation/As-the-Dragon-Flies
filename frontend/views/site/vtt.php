<?php

use common\components\NarrativeComponent;
use common\helpers\WebResourcesHelper;

/** @var yii\web\View $this */
/** @var common\models\quest $quest */
$chapter = $quest->currentChapter;
$storyRoot = WebResourcesHelper::storyRootPath($chapter->story_id);

$currentPlayer = $quest->currentPlayer;
$localPlayerId = Yii::$app->session->get('playerId');

$isYourTurn = ($currentPlayer->id === $localPlayerId);

$questProgress = $quest->currentQuestProgress;
$questActions = $questProgress?->questActions;
$mission = $questProgress?->mission;
$narrative = new NarrativeComponent(['mission' => $mission, 'title' => false]);
$description = $narrative->renderDescription();

$npcs = $mission?->npcs;

$title = "Chapitre {$chapter->chapter_number}. {$chapter->name} > " . ($mission?->name ?? '??');
$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- =========================================================
     EN-TÊTE
     ========================================================= -->
<header class="vtt-topbar">
    <div class="vtt-topbar__brand">
        <div class="vtt-topbar__title">
            <strong><?= $title ?></strong>
        </div>
    </div>

    <div class="vtt-topbar__meta">
        <span class="vtt-round"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Round 3</span>
        <span class="vtt-turn-pill"><i class="bi bi-hourglass-split" aria-hidden="true"></i> <?= $currentPlayer->name ?> joue</span>

        <nav class="vtt-topbar__jump" aria-label="Accès rapide">
            <!-- Smartphone uniquement : ouvre la liste des joueurs dans une modale -->
            <button type="button" class="vtt-icon-btn d-md-none" data-bs-toggle="modal" data-bs-target="#teamMembersModal" aria-label="Voir l'équipe">
                <i class="bi bi-people-fill" aria-hidden="true"></i>
            </button>
            <!-- Visible tant que le journal n'est pas affiché en colonne fixe (<992px) -->
            <a class="vtt-icon-btn vtt-icon-btn--journal" href="#gameLogPanel" aria-label="Aller au journal">
                <i class="bi bi-journal-text" aria-hidden="true"></i>
            </a>
        </nav>
    </div>
</header>

<!-- =========================================================
     GRILLE PRINCIPALE
     ========================================================= -->
<div class="vtt-shell">

    <main class="vtt-stage">

        <section id="stage-scene" class="scene-card" aria-labelledby="missionTitle">
            <?php if ($mission->image): ?>
                <div class="clearfix">
                    <img class="float-md-end mb-3 ms-md-4" src="<?= $storyRoot ?>/img/<?= $mission->image ?>" alt="<?= $mission->name ?>" style="max-width: 50%;">
                    <div class="scene-card__body text-decoration">
                        <h1 id="missionTitle" class="scene-card__title"><?= $mission?->name ?? '???' ?></h1>
                        <div class="scene-card__text"><?= $description ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="scene-card__body text-decoration">
                    <h1 id="missionTitle" class="scene-card__title"><?= $mission?->name ?? '???' ?></h1>
                    <div class="scene-card__text"><?= $description ?></div>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($npcs): ?>
            <section aria-labelledby="npc-title">
                <p class="panel-heading" id="npc-title"><i class="bi bi-chat-square-text-fill" aria-hidden="true"></i> Personnages présents</p>
                <div class="npc-grid">
                    <?php foreach ($npcs as $npc): ?>
                        <article class="npc-card">
                            <?php if ($npc->image): ?>
                                <img class="npc-card__portrait" src="<?= $storyRoot ?>/img/<?= $npc->image ?>" alt="<?= $mission->name ?>">
                            <?php else: ?>
                                <span class="npc-card__portrait" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
                            <?php endif; ?>
                            <div class="npc-card__body">
                                <p class="npc-card__name"><?= $npc->name ?></p>
                                <p class="npc-card__desc"><?= $npc->description ?></p>
                                <button type="button" class="npc-card__talk"><i class="bi bi-chat-dots" aria-hidden="true"></i> Parler</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($isYourTurn && $questActions !== null): ?>
            <section class="actions-panel" aria-labelledby="actions-title">
                <p class="panel-heading" id="actions-title"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Actions disponibles — Aelric</p>
                <div class="actions-panel__grid">
                    <?php
                    foreach ($questActions as $questAction):
                        $action = $questAction->action;
                        $onclick = $action->reply_id ?
                                "vtt.talk({$action->id}, {$action->reply_id}); return false;" :
                                "vtt.evaluateAction({$action->id}); return false;";
                        ?>
                        <button type="button" class="action-btn">
                            <span class="action-btn__top">
                                <span class="action-btn__name">
                                    <i class="bi bi-chat-dots" aria-hidden="true"></i>
                                    <?= $action->name ?>
                                </span>
                                <span class="action-btn__badges">
                                    <?php if ($action->is_free): ?>
                                        <span class="badge badge-success">Gratuite</span>
                                    <?php endif; ?>
                                    <?php if ($action->dc > 0): ?>
                                        <span class="badge badge-warning">DC <?= $action->dc ?></span>
                                    <?php endif; ?>
                                </span>
                            </span>
                            <span class="action-btn__desc">
                                <?= $action->description ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <footer class="turn-bar">
            <span class="turn-bar__info"><i class="bi bi-hourglass-split" aria-hidden="true"></i> C'est au tour de <strong>Aelric</strong> — Round 3</span>
            <button type="button" class="turn-bar__btn"><i class="bi bi-check2-circle" aria-hidden="true"></i> Terminer le tour</button>
        </footer>

    </main>

    <!-- ---------------- COLONNE ÉQUIPE + JOURNAL/TCHAT ----------------
    .vtt-team-col ne fait rien par défaut (display:contents) : groupe,
    journal et tchat restent des zones de grille indépendantes.
    À partir de 992px et jusqu'à 1199px, elle devient un vrai bloc
    flex (colonne unique, collée en haut, défilement interne) afin
    que le groupe et le journal/tchat se suivent sans espace vide et
    restent visibles pendant le défilement. ---------------- -->
    <div class="vtt-team-col">

        <aside id="party-panel" class="d-none d-md-block" aria-label="Groupe d'aventuriers">
            <p class="panel-heading"><i class="bi bi-people-fill" aria-hidden="true"></i> Groupe (2 joueurs)</p>
            <ul class="party-panel__list list-unstyled">

                <li class="char-card char-card--active">
                    <span class="char-card__turn-badge">En jeu</span>
                    <div class="char-card__head">
                        <span class="char-card__avatar" aria-hidden="true">A</span>
                        <div>
                            <p class="char-card__name">Aelric</p>
                            <p class="char-card__class">Guerrier · Niveau 2</p>
                        </div>
                    </div>
                    <div class="stat-row stat-row--hp">
                        <span class="stat-row__label">PV</span>
                        <span class="progress" role="progressbar" aria-label="Points de vie d'Aelric" aria-valuenow="18" aria-valuemin="0" aria-valuemax="20">
                            <span class="progress-bar progress-bar--hp" style="width:90%"></span>
                        </span>
                        <span class="stat-row__value">18/20</span>
                    </div>
                    <div class="stat-row stat-row--xp">
                        <span class="stat-row__label">XP</span>
                        <span class="progress" role="progressbar" aria-label="Expérience d'Aelric" aria-valuenow="120" aria-valuemin="0" aria-valuemax="300">
                            <span class="progress-bar progress-bar--xp" style="width:40%"></span>
                        </span>
                        <span class="stat-row__value">120/300</span>
                    </div>
                    <div class="char-card__footer">
                        <span class="badge badge-warning"><i class="bi bi-coin" aria-hidden="true"></i> 14 po</span>
                        <span class="badge badge-secondary">CA 16</span>
                    </div>
                </li>

                <li class="char-card">
                    <div class="char-card__head">
                        <span class="char-card__avatar" aria-hidden="true">N</span>
                        <div>
                            <p class="char-card__name">Nym</p>
                            <p class="char-card__class">Voleuse · Niveau 2</p>
                        </div>
                    </div>
                    <div class="stat-row stat-row--hp">
                        <span class="stat-row__label">PV</span>
                        <span class="progress" role="progressbar" aria-label="Points de vie de Nym" aria-valuenow="14" aria-valuemin="0" aria-valuemax="16">
                            <span class="progress-bar progress-bar--hp" style="width:87%"></span>
                        </span>
                        <span class="stat-row__value">14/16</span>
                    </div>
                    <div class="stat-row stat-row--xp">
                        <span class="stat-row__label">XP</span>
                        <span class="progress" role="progressbar" aria-label="Expérience de Nym" aria-valuenow="95" aria-valuemin="0" aria-valuemax="300">
                            <span class="progress-bar progress-bar--xp" style="width:32%"></span>
                        </span>
                        <span class="stat-row__value">95/300</span>
                    </div>
                    <div class="char-card__footer">
                        <span class="badge badge-warning"><i class="bi bi-coin" aria-hidden="true"></i> 9 po</span>
                        <span class="badge badge-secondary">CA 14</span>
                    </div>
                </li>

            </ul>
        </aside>
        <!-- ---------------- GROUPE ----------------
        Masqué sous 768px : la liste des joueurs vit alors uniquement
        dans la modale #teamMembersModal, ouverte depuis le bouton "équipe"
        de l'en-tête. ---------------- -->

        <!-- ---------------- SCÈNE + PNJ + ACTIONS ---------------- -->

        <!-- ---------------- JOURNAL & DISCUSSION ----------------
        Deux <aside> physiquement séparés, regroupés par .vtt-sidebar
        qui les empile par défaut et les met côte à côte à partir de
        1981px (voir vtt-custom.css). ---------------- -->
        <div class="vtt-sidebar">

            <aside id="gameLogPanel" class="side-panel" aria-label="Journal de la quête">
                <p class="side-panel__title"><i class="bi bi-journal-text" aria-hidden="true"></i> Journal de la quête</p>
                <div class="journal-feed">

                    <p class="journal-entry">
                        <time>Round 1</time>
                        Le groupe entre dans <strong>l'Auberge du Sanglier Rieur</strong>, à la lisière de la forêt de Vaelthar.
                    </p>

                    <p class="journal-entry">
                        <time>Round 1</time>
                        <strong>Tommy l'aubergiste</strong> met en garde les aventuriers contre un manoir maudit et un trésor perdu des croisades.
                    </p>

                    <p class="journal-entry">
                        <time>Round 2</time>
                        <strong>Nym</strong> examine le tableau du Chevalier Noir et découvre un texte dissimulé sur le parchemin peint.
                    </p>

                    <p class="journal-entry">
                        <time>Round 2</time>
                        <strong>Aelric</strong> examine le comptoir (DC 20) — rien trouvé pour l'instant.
                    </p>

                    <p class="journal-entry">
                        <time>Round 3</time>
                        <strong>Gurdil l'ivrogne</strong> évoque des rumeurs sur le manoir et un chemin à travers les bois.
                    </p>

                </div>
            </aside>

            <aside id="chat-panel" class="side-panel" aria-label="Discussion entre joueurs">
                <p class="side-panel__title"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i> Discussion</p>
                <div class="chat-feed">

                    <div class="chat-bubble">
                        <p class="chat-bubble__author">Marc (Aelric)</p>
                        <p class="chat-bubble__text">On tente le tableau en premier ?</p>
                    </div>

                    <div class="chat-bubble chat-bubble--self">
                        <p class="chat-bubble__author">Julie (Nym)</p>
                        <p class="chat-bubble__text">Oui, je m'en occupe, j'ai un bon score en Perception.</p>
                    </div>

                    <div class="chat-bubble">
                        <p class="chat-bubble__author">Marc (Aelric)</p>
                        <p class="chat-bubble__text">Pendant ce temps je discute avec l'aubergiste.</p>
                    </div>

                </div>
                <form class="chat-composer" aria-label="Envoyer un message">
                    <label class="visually-hidden" for="chat-input">Votre message</label>
                    <textarea id="chat-input" placeholder="Écrire un message…"></textarea>
                    <button type="button">Envoyer</button>
                </form>
            </aside>

        </div>

    </div>

</div>

<!-- =========================================================
     MODALE ÉQUIPE — smartphone uniquement (bouton dans l'en-tête)
     ========================================================= -->
<div class="modal fade" id="teamMembersModal" tabindex="-1" aria-labelledby="teamMembersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h6 mb-0" id="teamMembersModalLabel"><i class="bi bi-people-fill" aria-hidden="true"></i> Groupe (2 joueurs)</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <ul class="party-panel__list list-unstyled mb-0">

                    <li class="char-card char-card--active">
                        <span class="char-card__turn-badge">En jeu</span>
                        <div class="char-card__head">
                            <span class="char-card__avatar" aria-hidden="true">A</span>
                            <div>
                                <p class="char-card__name">Aelric</p>
                                <p class="char-card__class">Guerrier · Niveau 2</p>
                            </div>
                        </div>
                        <div class="stat-row stat-row--hp">
                            <span class="stat-row__label">PV</span>
                            <span class="progress" role="progressbar" aria-label="Points de vie d'Aelric" aria-valuenow="18" aria-valuemin="0" aria-valuemax="20">
                                <span class="progress-bar progress-bar--hp" style="width:90%"></span>
                            </span>
                            <span class="stat-row__value">18/20</span>
                        </div>
                        <div class="stat-row stat-row--xp">
                            <span class="stat-row__label">XP</span>
                            <span class="progress" role="progressbar" aria-label="Expérience d'Aelric" aria-valuenow="120" aria-valuemin="0" aria-valuemax="300">
                                <span class="progress-bar progress-bar--xp" style="width:40%"></span>
                            </span>
                            <span class="stat-row__value">120/300</span>
                        </div>
                        <div class="char-card__footer">
                            <span class="badge badge-warning"><i class="bi bi-coin" aria-hidden="true"></i> 14 po</span>
                            <span class="badge badge-secondary">CA 16</span>
                        </div>
                    </li>

                    <li class="char-card">
                        <div class="char-card__head">
                            <span class="char-card__avatar" aria-hidden="true">N</span>
                            <div>
                                <p class="char-card__name">Nym</p>
                                <p class="char-card__class">Voleuse · Niveau 2</p>
                            </div>
                        </div>
                        <div class="stat-row stat-row--hp">
                            <span class="stat-row__label">PV</span>
                            <span class="progress" role="progressbar" aria-label="Points de vie de Nym" aria-valuenow="14" aria-valuemin="0" aria-valuemax="16">
                                <span class="progress-bar progress-bar--hp" style="width:87%"></span>
                            </span>
                            <span class="stat-row__value">14/16</span>
                        </div>
                        <div class="stat-row stat-row--xp">
                            <span class="stat-row__label">XP</span>
                            <span class="progress" role="progressbar" aria-label="Expérience de Nym" aria-valuenow="95" aria-valuemin="0" aria-valuemax="300">
                                <span class="progress-bar progress-bar--xp" style="width:32%"></span>
                            </span>
                            <span class="stat-row__value">95/300</span>
                        </div>
                        <div class="char-card__footer">
                            <span class="badge badge-warning"><i class="bi bi-coin" aria-hidden="true"></i> 9 po</span>
                            <span class="badge badge-secondary">CA 14</span>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</div>
