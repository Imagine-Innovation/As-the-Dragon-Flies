<?php

use frontend\widgets\AjaxContainer;
use frontend\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Quest $player */
$player = Yii::$app->session->get('currentPlayer');
$quest = Yii::$app->session->get('currentQuest');
$this->title = Yii::$app->session->get('questName');

$playerSnippet = $this->renderFile('@app/views/game/snippets/player.php', [
    'player' => $player
        ]);
?>
<main class="row" style="height: calc(100dvh - 120px);">
    <!-- Left Panel - Character Data -->

    <!-- Offcanvas Aside -->
    <aside class="offcanvas offcanvas-start d-xl-none" tabindex="-1" id="offcanvasPlayer" aria-labelledby="offcanvasPlayerLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasPlayerLabel">Offcanvas Aside</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <section class="">
                <article id="game-player" class="card">
                    <?= $playerSnippet ?>
                </article>
                <article id="game-equipement" class="card">
                    <!-- Equipement -->
                    <div class="actions">
                        <?=
                        Button::widget([
                            'id' => 'showEquipmentModal-Button',
                            'mode' => 'icon',
                            'icon' => 'dnd-equipment',
                            'tooltip' => "Player's equipement",
                            'modal' => 'equipmentModal'
                        ])
                        ?>
                    </div>
                    <div class="m-3">
                        <h6 class="text-warning">Equipment</h6>
                        <div class="equipment-card" id="svg-aside-offcanvas"></div>
                    </div>
                </article>
                <article id="game-partners" class="card">
                    <?= AjaxContainer::widget(['name' => 'offcanvasQuestMembers']) ?>
                </article>
            </section>
        </div>
    </aside>

    <!-- Visible Aside for xl and larger screens -->
    <aside class="col-xl-3 col-xxl-2 d-none d-xl-block">
        <section class="h-100">
            <article id="game-player" class="card">
                <?= $playerSnippet ?>
            </article>
            <article id="game-equipement" class="card">
                <!-- Equipement -->
                <div class="actions">
                    <?=
                    Button::widget([
                        'id' => 'showEquipmentModal-Button',
                        'mode' => 'icon',
                        'icon' => 'dnd-equipment',
                        'tooltip' => "Player's equipement",
                        'modal' => 'equipmentModal'
                    ])
                    ?>
                </div>
                <div class="m-3">
                    <h6 class="text-warning">Equipment</h6>
                    <div class="equipment-card" id="svg-aside"></div>
                </div>
            </article>
            <article id="game-partners" class="card">
                <?= AjaxContainer::widget(['name' => 'questMembers']) ?>
            </article>
        </section>
    </aside>

    <!-- Center Panel - Game World -->
    <section class="col-12 col-xl-9 col-xxl-10">
        <div class="row">
            <div class="col-12 col-lg-6 col-xxl-8">
                <!-- Game Scene -->
                <div class="card p-3 h-100 d-flex flex-column">
                    <div class="actions">
                        <!-- Button to trigger the offcanvas on smaller screens -->
                        <a role="button"class="actions__item d-xl-none"  data-bs-toggle="offcanvas" href="#offcanvasPlayer" aria-controls="offcanvasPlayer">
                            <i class="bi bi-person-square"></i>
                        </a>
                        <?=
                        Button::widget([
                            'mode' => 'icon',
                            'url' => Url::toRoute(['site/index']),
                            'style' => 'd-md-none',
                            'icon' => 'bi-box-arrow-right',
                            'tooltip' => 'Back to lobby'
                        ])
                        ?>
                    </div>

                    <div class="card-header">
                        <h2 class="text-warning text-decoration mb-3 h5">The Ancient Throne Room</h2>
                    </div>

                    <div class="card-body">
                        <article class="flex-grow-1 h-auto mb-3">
                            <p id="scene-description" class="text-decoration">
                                You stand before a massive obsidian throne, its surface carved with writhing shadows that seem to move in the flickering torchlight. Ancient runes glow with a malevolent purple light along the armrests. The air is thick with dark magic, and you can hear whispers in a language long forgotten echoing from the walls.
                            </p>
                            <aside class="text-info">
                                <strong>Thorin:</strong> Your darkvision allows you to see clearly in this dim chamber. You notice something glinting behind the throne that the others cannot see.
                            </aside>
                            <nav>
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
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6 col-xxl-4">
                <div class="h-100 d-flex flex-column">
                    <!-- Chat System -->
                    <?=
                    $this->renderFile('@app/views/quest/snippets/chat.php', [
                        'questId' => $quest->id,
                        'playerId' => $player->id
                    ])
                    ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?=
$this->renderFile('@app/views/game/snippets/equipment-modal.php', [
    'player' => $player
])
?>
