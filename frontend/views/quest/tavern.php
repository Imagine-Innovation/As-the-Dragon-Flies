<?php

use frontend\components\QuestMessages;
use frontend\widgets\AjaxContainer;

/** @var yii\web\View $this */
/** @var common\models\Quest $model */
$player = Yii::$app->session->get('currentPlayer');
$playerId = $player->id;
$playerName = $player->name;
$avatar = $player->image->file_name;

$questName = $model->story->name;

$this->title = $questName;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $questName;
$messages = QuestMessages::getLastMessages($model->id, $playerId);
?>
<style>
    /* Chat Panel Height Management */
    .chat-container {
        height: calc(100vh - 200px); /* Adjust 200px based on your header/footer height */
        display: flex;
        flex-direction: column;
    }

    .chat-container .card-body {
        flex: 1;
        overflow-y: auto;
        max-height: calc(100vh - 350px); /* Adjust based on header + form + padding */
        min-height: 300px;
    }

    /* Alternative approach using viewport units */
    @media (min-width: 768px) {
        .chat-panel-container {
            height: calc(100vh - 180px);
            overflow: hidden;
        }

        .chat-panel-container .card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .chat-panel-container .card-body {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 1rem;
        }
    }

    /* Scrollbar styling for better UX */
    .card-body.overflow-auto::-webkit-scrollbar {
        width: 6px;
    }

    .card-body.overflow-auto::-webkit-scrollbar-track {
        background: var(--bs-gray-100);
        border-radius: 3px;
    }

    .card-body.overflow-auto::-webkit-scrollbar-thumb {
        background: var(--bs-gray-400);
        border-radius: 3px;
    }

    .card-body.overflow-auto::-webkit-scrollbar-thumb:hover {
        background: var(--bs-gray-500);
    }
    /* Chat Panel Height Management */
    .chat-container {
        height: calc(100vh - 200px); /* Adjust 200px based on your header/footer height */
        display: flex;
        flex-direction: column;
    }

    .chat-container .card-body {
        flex: 1;
        overflow-y: auto;
        max-height: calc(100vh - 350px); /* Adjust based on header + form + padding */
        min-height: 300px;
    }

    /* Alternative approach using viewport units */
    @media (min-width: 768px) {
        .chat-panel-container {
            height: calc(100vh - 180px);
            overflow: hidden;
        }

        .chat-panel-container .card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .chat-panel-container .card-body {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 1rem;
        }
    }

    /* Scrollbar styling for better UX */
    .card-body.overflow-auto::-webkit-scrollbar {
        width: 6px;
    }

    .card-body.overflow-auto::-webkit-scrollbar-track {
        background: var(--bs-gray-100);
        border-radius: 3px;
    }

    .card-body.overflow-auto::-webkit-scrollbar-thumb {
        background: var(--bs-gray-400);
        border-radius: 3px;
    }

    .card-body.overflow-auto::-webkit-scrollbar-thumb:hover {
        background: var(--bs-gray-500);
    }
</style>
<div class="row g-3">
    <!-- Quest Panel -->
    <div class="col-xl-8">
        <div class="card p-4 mb-3">
            <div class="card-header">
                <h5 class="text-decoration">Welcome <?= $playerName ?> in <?= $questName ?> Quest</h5>
            </div>
            <div class="card-body">
                <p class="text-decoration mb-3"><?= $model->story->description ?></p>
                <p class="mb-3">This quest will require <?= $model->story->companySize ?> <?= strtolower($model->story->requiredLevels) ?>.</p>
                <p class="mb-3">We're still waiting for 2 other members to join us before starting</p>
                <p class="mb-0">We still need a bard and a sorcerer to meet all the conditions.</p>
            </div>
        </div>

        <!-- Party Panel -->
        <div class="card p-4">
            <div class="card-header">
                <h5 class="text-decoration">The adventuring companionship that is building up</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <?= AjaxContainer::widget(['name' => 'questTavernPlayersContainer']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Panel -->
    <div class="col-xl-4">
        <div class="chat-panel-container">
            <div class="card p-4 h-100 d-flex flex-column">
                <div class="card-header">
                    <h5 class="text-decoration">Tavern Chat</h5>
                </div>

                <div class="mt-auto">
                    <form id="questChatMessageForm">
                        <div class="input-group">
                            <input type="text" class="form-control" id="questChatInput" placeholder="Type your message...">
                            <button id="send-message" class="btn btn-primary" type="button">Send</button>
                        </div>
                    </form>
                    <small class="text-muted mt-2 d-block">Press Enter to send • Be respectful to fellow adventurers</small>
                </div>

                <div class="card-body overflow-auto flex-grow-1 mb-3">
                    <div id="questChatContent">
                        <?= $this->render('ajax-messages', ['messages' => $messages]) ?>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <script type="text/javascript">
        $(document).ready(function () {
            // Create and initialize the notification client instance
            // Replace these with your actual values
            const currentHost = window.location.hostname;
            const url = `ws://${currentHost}:8082`;
            const playerId = <?= $playerId ?>;
            const avatar = `<?= $avatar ?>`;
            const playerName = `<?= $playerName ?>`;
            const questId = <?= $model->id ?>;
            const questName = `<?= $questName ?>`;
            const chatInput = `questChatInput`;

            console.log(`NotificationClient(url=${url}, playerId=${playerId}, avatar=${avatar}, questId=${questId}, playerName=${playerName}, questName=${questName}, chatInput=${chatInput}`);
            const notificationClient = new NotificationClient(url, playerId, avatar, questId, playerName, questName, chatInput);

            notificationClient.init();

            let config = {
                route: 'quest/ajax-tavern',
                method: 'GET',
                placeholder: 'questTavernPlayersContainer',
                badge: false
            };
            notificationClient.executeRequest(config, '');

        });
    </script>
