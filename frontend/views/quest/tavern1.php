<?php

use common\components\AppStatus;
use frontend\widgets\AjaxContainer;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Quest $model */
$player = Yii::$app->session->get('currentPlayer');
$playerId = $player->id;
$playerName = $player->name;
$avatar = $player->image->file_name;

$questName = $model->story->name;

$this->title = $questName;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php if (1 === 1): ?>
    <script src="js/atdf-quest-events.js"></script>
    <?php if (1 === 2): ?>
        <script src="js/atdf-quest-manager.js"></script>
    <?php endif; ?>
    <div class="container-flex">
        <div id="questView">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title text-decoration">Welcome to our tavern, your friends are waiting for you!</h4>
                        </div>
                        <img class="card-img-top" src="img/story/Tavern<?= mt_rand(1, 3) ?>.png"/>
                        <div class="card-body">
                            <p class="card-text">
                                The tavern is where all the adventurers meet before embarking on the quest.
                            </p>
                            <p class="card-text">
                                The quest can be started as soon as all the conditions have been met and the minimum number of players has entered the tavern.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <?=
                    $this->renderFile('@app/views/quest/_chat.php', [
                        'questId' => $model->id,
                        'playerId' => $playerId
                    ])
                    ?>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" id="tavernWelcomeMessage"></h4>
                    <div class="actions">
                        <a class="actions__item bi-chat-left-dots" type="button" data-bs-toggle="modal" data-bs-target="#questChatModal"></a>
                    </div>
                    <?= AjaxContainer::widget(['name' => 'questTavernPlayersContainer']) ?>
                </div>
            </div>
        </div>

        <div class="modal fade" id="questChatModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Quest chat</h6>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-theme btn--icon" data-bs-dismiss="modal">
                            <i class="bi bi-x-square"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <style>
        .notification-panel, .chat-panel {
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
            max-width: 600px;
        }

        .panel-header {
            background-color: #f5f5f5;
            padding: 10px;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notifications-list, .chat-messages {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
        }

        .notification {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }

        .notification.read {
            opacity: 0.7;
            background-color: #f0f0f0;
        }

        .notification-header, .chat-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #666;
        }

        .notification-content, .chat-content {
            margin-bottom: 10px;
        }

        .notification-actions {
            text-align: right;
        }

        .chat-message {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f0f8ff;
        }

        .chat-input-area {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ccc;
        }

        #chat-input {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        button {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        #connection-status {
            font-size: 0.8em;
            padding: 3px 8px;
            border-radius: 10px;
        }

        .status-connected {
            background-color: #dff0d8;
            color: #3c763d;
        }

        .status-disconnected-reconnecting {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }

        .status-connection-error {
            background-color: #f2dede;
            color: #a94442;
        }

        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .message {
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .timestamp {
            color: #999;
            font-size: 0.8em;
        }

        .player-name {
            font-weight: bold;
            color: #337ab7;
        }

        .system-message {
            color: #777;
            font-style: italic;
        }

        .system-text {
            color: #5cb85c;
        }

        #player-list {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
    <div class="quest-tavern">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="row">
            <div class="col-md-8">
                <div class="notification-panel">
                    <div class="panel-header">
                        <h3>Notifications</h3>
                        <span id="connection-status">Connecting...</span>
                        <button id="mark-all-read-btn" onclick="markAllNotificationsAsRead()">Mark All as Read</button>
                    </div>
                    <div id="notifications-container" class="notifications-list">
                        <!-- Notifications will be inserted here -->
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Tavern Chat</h3>
                    </div>
                    <div class="panel-body">
                        <div id="chat-messages" class="chat-messages">
                            <!-- Chat messages will be displayed here -->
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="input-group">
                            <input type="text" id="message-input" class="form-control" placeholder="Type your message...">
                            <span class="input-group-btn">
                                <button id="send-message" class="btn btn-primary" type="button">Send</button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Players in Tavern</h3>
                    </div>
                    <div class="panel-body">
                        <ul id="player-list" class="list-group">
                            <?php foreach ($model->currentPlayers as $player): ?>
                                <li class="list-group-item" data-player-id="<?= $player->id ?>">
                                    <?= Html::encode($player->name) ?>
                                    <?php if ($model->initiator_id === $player->id): ?>
                                        <span class="badge">Quest Master</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="panel-footer">
                        <?=
                        Html::a('Leave Quest', ['quest/leave-quest'], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to leave this quest?',
                                'method' => 'post',
                            ],
                        ])
                        ?>

                        <?php if ($model->status === AppStatus::WAITING->value && $model->initiator_id === $playerId): ?>
                            <?=
                            Html::a('Start Quest', ['quest/game-action'], [
                                'class' => 'btn btn-success',
                                'id' => 'start-quest-btn',
                                'data' => [
                                    'method' => 'post',
                                ],
                            ])
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


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
