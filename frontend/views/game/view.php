<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Quest $player */
$player = Yii::$app->session->get('currentPlayer');
$quest = Yii::$app->session->get('currentQuest');
$this->title = Yii::$app->session->get('questName');
?>
<main class="row" style="height: calc(100dvh - 120px);">
    <!-- Left Panel - Character Data -->

    <!-- Offcanvas Aside -->
    <aside class="offcanvas offcanvas-start d-xl-none" tabindex="-1" id="offcanvasPlayers" aria-labelledby="offcanvasPlayersLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasPlayersLabel">Offcanvas Aside</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <section class="card p-3">
                <?= $this->renderFile('@app/views/game/snippets/player.php', ['player' => $player]) ?>
                <?= $this->renderFile('@app/views/game/snippets/partners.php', ['playerId' => $player->id, 'quest' => $quest]) ?>
            </section>
        </div>
    </aside>

    <!-- Visible Aside for xl and larger screens -->
    <aside class="col-xl-3 d-none d-xl-block">
        <section class="card p-3 h-100">
            <?= $this->renderFile('@app/views/game/snippets/player.php', ['player' => $player]) ?>
            <?= $this->renderFile('@app/views/game/snippets/partners.php', ['playerId' => $player->id, 'quest' => $quest]) ?>
        </section>
    </aside>

    <!-- Center Panel - Game World -->
    <section class="col-12 col-xl-9">
        <div class="card p-3 h-100 d-flex flex-column">
            <div class="actions">
                <!-- Button to trigger the offcanvas on smaller screens -->
                <a role="button"class="actions__item d-xl-none"  data-bs-toggle="offcanvas" href="#offcanvasPlayers" aria-controls="offcanvasPlayers">
                    <i class="bi bi-person-square"></i>
                </a>
                <a role="button" class="actions__item d-md-none" href="<?= Url::toRoute(['site/index']) ?>" data-bs-toggle="tooltip" title="Back to lobby" data-placement="bottom">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>

            <div class="card-header">
                <h2 class="text-warning mb-3 h5">The Ancient Throne Room</h2>
            </div>

            <div class="card-body row">
                <!-- Game Scene -->
                <article class="col-12 col-lg-6 flex-grow-1 h-auto mb-3">
                    <p id="scene-description">
                        You stand before a massive obsidian throne, its surface carved with writhing shadows that seem to move in the flickering torchlight. Ancient runes glow with a malevolent purple light along the armrests. The air is thick with dark magic, and you can hear whispers in a language long forgotten echoing from the walls.
                    </p>
                    <aside class="text-info">
                        <strong>Thorin:</strong> Your darkvision allows you to see clearly in this dim chamber. You notice something glinting behind the throne that the others cannot see.
                    </aside>
                    <nav class="scene-actions">
                        <button class="btn btn-action btn-sm me-2" onclick="performAction('examine-throne')" aria-label="Examine throne">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>
                        <button class="btn btn-action btn-sm me-2" onclick="performAction('approach-throne')" aria-label="Approach throne">
                            <i class="bi bi-person-walking" aria-hidden="true"></i>
                        </button>
                        <button class="btn btn-action btn-sm" onclick="performAction('cast-detect-magic')" aria-label="Cast detect magic">
                            <i class="bi bi-magic" aria-hidden="true"></i>
                        </button>
                    </nav>
                </article>

                <!-- Chat System -->
                <section class="col-12 col-lg-6 pt-3 h-auto">
                    <?= $this->renderFile('@app/views/quest/snippets/chat.php', ['questId' => $quest->id, 'playerId' => $player->id]) ?>
                </section>
            </div>
    </section>
</main>
