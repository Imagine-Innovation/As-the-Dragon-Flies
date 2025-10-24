<?php

use frontend\widgets\AjaxContainer;
use frontend\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Quest $quest */
/** @var int $nbPlayers */
$player = Yii::$app->session->get('currentPlayer');
//$quest = Yii::$app->session->get('currentQuest');
//$this->title = Yii::$app->session->get('questName');
$this->title = $quest->name;
$story = $quest->story;
$playerSnippet = $this->renderFile('@app/views/game/snippets/player.php', ['player' => $player]);
$currentQuestProgress = $quest->currentQuestProgress;
$mission = $currentQuestProgress->mission;

$actionList = ($currentQuestProgress->current_player_id === $player->id) ?
        $this->renderFile('@app/views/game/ajax/actions.php', ['questActions' => $currentQuestProgress->questActions]) :
        "";
?>
<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenStoryId"><?= $story->id ?></span>
    <span id="hiddenQuestId"><?= $currentQuestProgress->quest_id ?></span>
    <span id="hiddenQuestProgressId"><?= $currentQuestProgress->id ?></span>
    <span id="hiddenQuestMissionId"><?= $currentQuestProgress->mission_id ?></span>
    <span id="hiddenCurrentPlayerId"><?= $currentQuestProgress->current_player_id ?></span>
</div>

<main class="row" style="height: calc(100dvh - 120px);">
    <!-- Left Panel - Character Data -->

    <!-- Offcanvas Aside -->
    <aside class="offcanvas offcanvas-start d-xxl-none" tabindex="-1" id="offcanvasPlayer" aria-labelledby="offcanvasPlayerLabel">
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

    <!-- Visible Aside for xxl and larger screens -->
    <aside class="col-xxl-3 col-3xl-2 d-none d-xxl-block">
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
    <section class="col-12 col-xxl-9 col-3xl-10">
        <div class="row">
            <!-- Game Scene -->
            <div class="<?= $nbPlayers == 1 ? "col-12" : "col-12 col-xl-7 col-3xl-9" ?>">
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
                        <h2 class="text-warning text-decoration mb-3 h5"><?= $quest->currentChapter->name ?></h2>
                    </div>

                    <div class="card-body">
                        <article class="flex-grow-1 h-auto mb-3">
                            <div id="missionDescription" class="text-decoration">
                                <?= $this->renderFile('@app/views/game/ajax/mission.php', ['questProgress' => $currentQuestProgress]) ?>
                            </div>
                            <br />
                            <div id="actionList">
                                <?= $actionList ?>
                            </div>
                            <div id="actionFeedback"></div>
                        </article>
                    </div>
                </div>
            </div>
            <?php if ($nbPlayers > 1): ?>
                <!-- Chat System -->
                <div class="col-12 col-xl-5 col-3xl-3">
                    <div class="h-100 d-flex flex-column">
                        <?=
                        $this->renderFile('@app/views/quest/snippets/chat.php', [
                            'questId' => $quest->id,
                            'playerId' => $player->id
                        ])
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?= $this->renderFile('@app/views/game/snippets/equipment-modal.php', ['player' => $player]) ?>
<?= $this->renderFile('@app/views/game/snippets/npc-dialog-modal.php') ?>
