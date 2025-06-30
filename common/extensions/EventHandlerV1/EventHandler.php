<?php

namespace common\extensions\EventHandler;

use common\models\QuestSession;
use common\models\Notification;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Ratchet\ConnectionInterface;
//use Yii;
use yii\base\Component;

class EventHandler extends Component {

    /**
     * @var string The host to bind the WebSocket server to
     * @var int The port to bind the WebSocket server to
     */
    public $host = '0.0.0.0';  // Listen on all interfaces
    public $port = 8082;

    /**
     * @var IoServer The server instance
     */
    private $server;

    /**
     * @var array Connected clients (clientId => ConnectionInterface)
     */
    private static $clients = [];

    /**
     * @var Server start time
     * @var bool Whether to output debug messages and the associated nested level
     */
    private $startTime;
    public $debug = true;
    private $nestedLevel;

    /**
     * Log a message
     *
     * @param string $message
     * @param mixed $dump
     * @param string $level
     */
    public function log(string $message, mixed $dump = null, string $level = 'info'): void {
        if (!$this->debug) {
            return;
        }
        $filename = "c:/temp/EventHandler.log";
        $myfile = fopen($filename, "a") or die("Unable to open file {$filename}!");
        $offset = str_repeat("    ", max($this->nestedLevel, 0));
        $date = date('Y-m-d H:i:s');
        $txt = "{$date} {$level}: {$this->nestedLevel}.{$offset}{$message}\n";
        fwrite($myfile, $txt);

        if ($dump) {
            if (is_array($dump) || is_object($dump)) {
                $output = print_r($dump, true);
            } else {
                $output = (string) $dump;
            }
            fwrite($myfile, "{$output}\n");
        }

        fclose($myfile);
    }

    public function logStart(string $message, mixed $dump = null, string $level = 'info'): void {
        $this->nestedLevel++;
        $this->log("----------> start {$message}", $dump, $level);
    }

    public function logEnd(string $message, mixed $dump = null, string $level = 'info'): void {
        $this->log("----------< end {$message}\n", $dump, $level);
        $this->nestedLevel--;
    }

    private function logQuestSession(string|null $message = null, array|null $sessions = null): void {
        if (!$sessions) {
            $sessions = QuestSession::find()->all();
        }

        if ($message) {
            $this->log($message);
        }
        foreach ($sessions as $session) {
            $log = "id=[{$session->id}], quest_id=[{$session->quest_id}], player_id=[{$session->player_id}], client_id=[{$session->client_id}], last_ts=[{$session->last_ts}]";
            $this->log($log);
        }
    }

    public function run() {
        // Set error reporting to ignore deprecation warnings
        $previousErrorLevel = error_reporting();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        $this->nestedLevel = 0;

        try {
            $this->logStart("WebSocket server");
            $this->startTime = time();

            // Get the event loop
            $loop = Loop::get();

            // Create your WebSocket handler
            $webSocketHandler = new WebSocketHandler($this);

            // Create a Ratchet WsServer
            $wsServer = new WsServer($webSocketHandler);

            // Create an HTTP server to handle the WebSocket handshake
            $httpServer = new HttpServer($wsServer);

            // Create the socket server - listen on all interfaces
            $socketServer = new SocketServer("{$this->host}:{$this->port}");

            // Create the IO server
            $this->server = new IoServer($httpServer, $socketServer, $loop);

            $this->log("WebSocket server running at {$this->host}:{$this->port}");
            $this->log("Server start time: " . date('Y-m-d H:i:s', $this->startTime));
            $this->logEnd("WebSocket server");

            // Run the event loop
            $loop->run();
        } catch (\Exception $e) {
            $this->log("Exception", $e->getMessage(), 'error');
            $this->logEnd("WebSocket server");
        } finally {
            // Restore previous error reporting level
            error_reporting($previousErrorLevel);
            $this->logEnd("WebSocket server");
        }
    }

    /**
     * Add a client connection
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     */
    public function addClient(ConnectionInterface $conn, string $clientId): void {
        $this->logStart("addClient clientId=[{$clientId}]");
        self::$clients[$clientId] = $conn;
        $this->logEnd("addClient");
    }

    /**
     * Remove a client connection
     *
     * @param string $clientId
     */
    public function removeClient(string $clientId): void {
        $this->logStart("removeClient clientId=[{$clientId}]");
        if (isset(self::$clients[$clientId])) {
            unset(self::$clients[$clientId]);
            $this->log("Client removed: [{$clientId}]");
        }

        // Search for the session that opened the client connection
        $this->log("Attempting to nullify client_id in QuestSession for clientId=[{$clientId}]");
        $rowsUpdated = QuestSession::updateAll(
                ['client_id' => null],
                ['client_id' => $clientId]
        );
        $this->log("QuestSession::updateAll result: {$rowsUpdated} row(s) updated for clientId=[{$clientId}]");
        if ($rowsUpdated === 0) {
            $this->log("QuestSession::updateAll found no rows to update for clientId=[{$clientId}]. Attempting to find session by client_id directly.", null, 'warning');
            // Try to find the session by client_id to log its state if it exists
            $staleSession = QuestSession::findOne(['client_id' => $clientId]);
            if ($staleSession) {
                $this->log("Found QuestSession with client_id=[{$clientId}]: id=[{$staleSession->id}], quest_id=[{$staleSession->quest_id}], player_id=[{$staleSession->player_id}], last_ts=[{$staleSession->last_ts}]", null, 'warning');
            } else {
                $this->log("No QuestSession found with client_id=[{$clientId}] either.", null, 'warning');
            }
            // The original logQuestSession() call can be kept if it's useful for broader context, or removed if too verbose.
            // For now, let's keep it to see all sessions.
            $this->logQuestSession("Current QuestSessions after attempting to remove client [{$clientId}]");
        }
        $this->logEnd("removeClient");
    }

    /**
     * Register a player for a quest (can be called from outside the WebSocket server)
     *
     * @param string $sessionId
     * @param array $data
     * @return bool True if registration was successful, false otherwise
     */
    public function registerSessionForQuest(string $sessionId, array $data): bool {
        $this->logStart("registerSessionForQuest sessionId=[{$sessionId}]", $data);

        $questId = $data['questId'] ?? null;
        // Validate input parameters
        if (!$sessionId || !is_numeric($questId) || $questId <= 0) {
            $this->log("Invalid sessionId or quest ID", null, 'error');
            $this->logEnd("registerSessionForQuest");
            return false;
        }

        try {
            // Register any existing active connections for this player
            $this->registerSession($sessionId, $data);
            $this->logEnd("registerSessionForQuest");
            return true;
        } catch (\Exception $e) {
            $this->log("Error registering session [{$sessionId}] for quest {$questId}: ", $e->getMessage(), 'error');
            $this->logEnd("registerSessionForQuest");
            return false;
        }
    }

    /**
     * Handle a message from a client
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param mixed $message
     */
    public function handleMessage(ConnectionInterface $from, string $clientId, mixed $message): void {
        $this->logStart("handleMessage");
        $this->log("Processing message from client [{$clientId}]:", $message);

        try {
            $data = json_decode($message, true);
            $sessionId = $data['sessionId'] ?? null;
            $this->registerSession($sessionId, $data, $clientId);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->handleJsonMessage($from, $clientId, $data);
            } else {
                $this->handleTextMessage($from, $clientId, (string) $message);
            }
        } catch (\Exception $e) {
            $this->log("Unable to handle message [{$clientId}]: $e->getMessage()", $message, 'error');
            $this->handleError($from, $clientId, $e);
            $this->logEnd("handleMessage");
        }
        $this->logEnd("handleMessage");
    }

    /**
     * Handle a JSON message
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleJsonMessage(ConnectionInterface $from, string $clientId, array $data): void {
        $this->logStart("handleJsonMessage");

        $sessionId = $data['sessionId'] ?? null;
        if (!$sessionId) {
            $this->sendBack('error', $from, 'Missing SessionId in source message');
            return;
        }
        // $this->log("sessionId=[{$sessionId}]");

        if (isset($data['type'])) {
            $this->handleTypedMessage($from, $clientId, $sessionId, $data);
        } elseif (isset($data['playerId'])) {
            $this->handleRegistration($from, $clientId, $sessionId, $data);
        } else {
            $this->handleGenericJsonMessage($from, $clientId, $sessionId, $data);
        }
        $this->logEnd("handleJsonMessage");
    }

    /**
     * Handle a message with a type field
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array $data
     */
    private function handleTypedMessage(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $type = $data['type'] ?? 'unknown';
        $this->logStart("handleTypedMessage type={$type}");

        match ($type) {
            'attach' => $this->handleAttachment($from, $clientId, $sessionId, $data),
            'register' => $this->handleRegistration($from, $clientId, $sessionId, $data),
            'chat' => $this->handleChatMessage($from, $sessionId, $data),
            'action' => $this->handleGameAction($from, $sessionId, $data),
            'announce_player_join' => $this->handleAnnouncePlayerJoin($from, $clientId, $sessionId, $data), // New line
            default => $this->handleUnknownType($from, $sessionId, $type, $data)
        };
        $this->logEnd("handleTypedMessage");
    }

    /**
     * Handle an unknown message type
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param string $type
     * @param array $data
     */
    private function handleUnknownType(ConnectionInterface $from, string $sessionId, string $type, array $data): void {
        $this->logStart("handleUnknownType");
        $this->log("Unknown message type '{$type}' from session [{$sessionId}]", $data, 'warning');
        $this->sendBack('echo', $from, $data);
        $this->logEnd("handleUnknownType");
    }

    /**
     * Register a player with a client ID
     *
     * @param string $sessionId
     * @param array $data
     * @param string|null $clientId
     * @return bool
     */
    private function registerSession(string|null $sessionId, mixed $data, string|null $clientId = null): bool {
        $this->logStart("registerSession (sessionId=[{$sessionId}], clientId=[{$clientId}])");

        if ($sessionId === null) {
            $this->log("Missing sessionId", null, 'warning');
            $this->logEnd("registerSession");
            return false;
        }

        $playerId = $data['playerId'] ?? null;
        $questId = $data['questId'] ?? null;

        $session = QuestSession::findOne(['id' => $sessionId]);

        if ($session) {
            $registered = $this->updateSession($session, $questId, $playerId, $clientId);
        } else {
            $registered = $this->newSession($sessionId, $questId, $playerId, $clientId);
        }

        $this->logEnd("registerSession");
        return $registered;
    }

    /**
     * Create a new session
     *
     * @param string $sessionId
     * @param int|null $questId
     * @param int|null $playerId
     * @param string|null $clientId
     * @return bool
     */
    private function newSession(string $sessionId, int|null $questId, int|null $playerId, string|null $clientId): bool {
        $this->logStart("newSession sessionId=[{$sessionId}], questId=[{$questId}], playerId=[{$playerId}], clientId=[{$clientId}]");
        $session = new QuestSession([
            'id' => $sessionId,
            'quest_id' => $questId,
            'player_id' => $playerId,
            'client_id' => $clientId,
        ]);
        $this->log("Attempting to save new QuestSession: sessionId=[{$sessionId}], questId=[{$questId}], playerId=[{$playerId}], clientId=[{$clientId}]");
        $saved = $session->save();
        if ($saved) {
            $this->log("Successfully saved new QuestSession: id=[{$session->id}]");
        } else {
            $this->log("Failed to save new QuestSession: sessionId=[{$sessionId}]. Errors: " . print_r($session->getErrors(), true), null, 'error');
        }
        $this->logQuestSession();
        $this->logEnd("newSession");
        return $saved;
    }

    /**
     * Update an existing session
     *
     * @param QuestSession $session
     * @param int|null $questId
     * @param int|null $playerId
     * @param string|null $clientId
     * @return bool
     */
    private function updateSession(QuestSession &$session, int|null $questId, int|null $playerId, string|null $clientId): bool {
        $this->logStart("updateSession session=[{$session->id}], questId=[{$questId}], playerId=[{$playerId}], clientId=[{$clientId}]");
        $needUpdate = false;

        // if the user has switched to another player
        // update both playerId and questId
        if (($session->player_id !== $playerId && $playerId) || ($session->player_id === null && $playerId)) {
            $this->log("Update playerId, previous value=[{$session->player_id}]");
            $session->player_id = $playerId;
            $session->last_ts = 0;
            $needUpdate = true;
        }

        if (($session->quest_id !== $questId && $questId) || ($session->quest_id === null && $questId)) {
            $this->log("Update questId, previous value=[{$session->quest_id}]");
            $session->quest_id = $questId;
            $session->last_ts = 0;
            $needUpdate = true;
        }

        if (($session->client_id !== $clientId && $clientId) || ($session->client_id === null && $clientId)) {
            $this->log("Update clientId, previous value=[{$session->client_id}]");
            $session->client_id = $clientId;
            $needUpdate = true;
        }

        // avoid unnecessay updates if nothing change
        if ($needUpdate) {
            $this->log("Attempting to update QuestSession: id=[{$session->id}], newQuestId=[{$questId}], newPlayerId=[{$playerId}], newClientId=[{$clientId}]");
            $saved = $session->save();
            if ($saved) {
                $this->log("Successfully updated QuestSession: id=[{$session->id}]");
            } else {
                $this->log("Failed to update QuestSession: id=[{$session->id}]. Errors: " . print_r($session->getErrors(), true), null, 'error');
            }
            $updated = $saved;
        } else {
            $updated = true;
        }
        $this->logQuestSession();
        $this->logEnd("updateSession: " . ($updated ? "success" : "failed"));
        return $updated;
    }

    /**
     * Handle player registration
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array $data
     */
    private function handleAttachment(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logStart("handleAttachment clientId=[{$clientId}], sessionId=[{$sessionId}]");

        $registered = $this->registerSession($sessionId, $data, $clientId);

        if ($registered) {
            $this->sendBack('connected', $from, "Client ID [{$clientId}] attached to session [{$sessionId}]");
        } else {
            $this->log("Unable to find session Id [{$sessionId}]");
            $this->sendBack('error', $from, "Unable to find session Id [{$sessionId}]");
        }

        $this->logEnd("handleAttachment");
    }

    /**
     * Handle player registration
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array $data
     */
    private function handleRegistration(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logStart("handleRegistration clientId=[{$clientId}], sessionId=[{$sessionId}]");
        $playerId = $data['playerId'] ?? null;
        if (!$playerId) {
            $this->sendBack('error', $from, 'Missing playerId in registration');
            $this->logEnd("handleRegistration");
            return;
        }

        // Register the player and deliver pending messages
        $questId = $data['questId'] ?? null;
        $this->registerSession($sessionId, $data, $clientId);
        if ($questId) {
            // Broadcast to other quest members that a new player has joined
            $this->broadcastToQuest($questId, $data, $sessionId);

            // recover the previouly exchanged messages ans send them to the new joiner
            $this->recoverMessageHistory($sessionId);
        }

        // Send acknowledgment
        $from->send(json_encode([
            'type' => 'connected',
            'playerId' => $playerId,
            'timestamp' => time()
        ]));

        $this->logEnd("handleRegistration");
    }

    /**
     *
     * @param string $sessionId
     * @param array $data
     * @return void
     */
    private function recoverMessageHistory(string $sessionId): void {
        // Consider that this function is called once we know a playerId, questId, sessionId and data
        $this->logStart("recoverMessageHistory for sessionId=[{$sessionId}]");

        $session = QuestSession::findOne(['id' => $sessionId]);
        if (!$session) {
            $this->log("QuestSession not found for sessionId=[{$sessionId}]. Cannot recover history.", null, 'warning');
            $this->logEnd("recoverMessageHistory for sessionId=[{$sessionId}]");
            return;
        }
        $this->log("Found QuestSession: id=[{$session->id}], quest_id=[{$session->quest_id}], player_id=[{$session->player_id}], client_id=[{$session->client_id}], last_ts=[{$session->last_ts}]");

        // Collects message notifications for the current quest
        $this->log("Fetching 'chat' notifications for quest_id=[{$session->quest_id}] created after timestamp=[{$session->last_ts}]");
        $chatNotifications = $this->getNotifications($session->quest_id, 'chat', $session->last_ts);

        $notificationCount = count($chatNotifications);
        $this->log("Processing {$notificationCount} 'chat' notifications for history recovery for sessionId=[{$sessionId}].");

        $recoveredMessages = 0;
        foreach ($chatNotifications as $notification) {
            $this->log("Preparing to send historical chat message: Notification.id=[{$notification->id}], player_id=[{$notification->player_id}], created_at=[{$notification->created_at}], payload=" . json_encode($notification->payload));
            $chatMessage = $this->prepareChatMessage($notification, $sessionId);
            $sent = $this->sendToSession($session, $chatMessage);
            if ($sent) {
                $recoveredMessages++;
                $this->log("Successfully sent historical message Notification.id=[{$notification->id}] to session [{$sessionId}]");
            } else {
                $this->log("Failed to send historical message Notification.id=[{$notification->id}] to session [{$sessionId}]", null, 'error');
            }
        }
        $this->log("Finished recovering history for session [{$sessionId}]. Sent {$recoveredMessages} message(s).");
        $this->logEnd("recoverMessageHistory for sessionId=[{$sessionId}]");
    }

    /**
     *
     * @param int $questId
     * @param string $type
     * @param int $since
     * @return array
     */
    private function getNotifications(int $questId, string $type, int $since): array {
        $this->logStart("getNotifications for questId=[{$questId}], type=[{$type}], since=[{$since}]");
        $this->log("Querying Notifications: quest_id=[{$questId}], notification_type=[{$type}], created_at > {$since}");
        $notifications = Notification::find()
                ->where(['quest_id' => $questId ?? 0])
                ->andWhere(['notification_type' => $type ?? 'unknown'])
                ->andWhere(['>', 'created_at', $since ?? 0])
                ->orderBy(['created_at' => SORT_ASC])
                ->all();
        $notificationCount = count($notifications);
        $this->log("{$notificationCount} notification(s) found for questId=[{$questId}], type=[{$type}], since=[{$since}]");
        $this->logEnd("getNotifications for questId=[{$questId}], type=[{$type}], since=[{$since}]");
        return $notifications;
    }

    /**
     *
     * @param Notification $notification
     * @param string $sessionId
     * @return string
     */
    private function prepareChatMessage(Notification $notification, string $sessionId): string {
        $this->logStart("prepareChatMessage", null, $notification);
        $array = [
            'type' => $notification->notification_type,
            'notificationId' => $notification->id,
            'sessionId' => $sessionId,
            'playerId' => $notification->player_id,
            'player' => $notification->player?->name ?? 'Unknown player',
            'questId' => $notification->quest_id,
            'quest' => $notification->quest?->story->name ?? 'Unknown quest',
            'timestamp' => $notification->created_at,
            'payload' => $notification->payload
        ];

        $jsonMessage = json_encode($array);
        $this->logEnd("prepareChatMessage");
        return $jsonMessage;
    }

    /**
     * Handle a chat message
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param array $data
     */
    private function handleChatMessage(ConnectionInterface $from, string $sessionId, array $data): void {
        $this->logStart("handleChatMessage sessionId=[{$sessionId}]", $data);

        $messageText = $data['message'] ?? '';
        $playerId = $data['playerId'] ?? null;
        $questId = $data['questId'] ?? null;

        if (empty($messageText) || $playerId === null || $questId === null) {
            $this->log("handleChatMessage: Missing message, playerId, or questId.", $data, 'warning');
            $from->send(json_encode(['type' => 'error', 'message' => 'Invalid chat message data.']));
            $this->logEnd("handleChatMessage sessionId=[{$sessionId}]");
            return;
        }

        /*
         * Used only for log purpose
         *
          $sender = Player::findOne($playerId);
          if (!$sender) {
          $this->log("handleChatMessage: Player not found for playerId=[{$playerId}].", $data, 'error');
          $from->send(json_encode(['type' => 'error', 'message' => 'Chat sender (player) not found.']));
          $this->logEnd("handleChatMessage sessionId=[{$sessionId}]");
          return;
          }

          $quest = Quest::findOne($questId);
          if (!$quest) {
          $this->log("handleChatMessage: Quest not found for questId=[{$questId}].", $data, 'error');
          $from->send(json_encode(['type' => 'error', 'message' => 'Chat quest not found.']));
          $this->logEnd("handleChatMessage sessionId=[{$sessionId}]");
          return;
          }

          $this->log("handleChatMessage: Successfully retrieved player '{$sender->name}' and quest '{$quest->story->name}'. Ready for further processing.");

          $notification = $this->saveNotification($playerId, $questId, $data);
          //$timestamp = ($notification?->created_at) ? $notification->created_at : time();


          $broadcastMessage = [
          'type' => 'chat', // Keep type 'chat' for client's existing chat handler
          'senderId' => $sender->id,
          'senderName' => $sender->name,
          'message' => $messageText,
          'timestamp' => $timestamp,
          'questId' => $quest->id // Good to include for client-side context
          ];
         *
         */

        $notification = $this->saveNotification($playerId, $questId, $data);
        $broadcastMessage = $this->prepareChatMessage($notification, $sessionId);

        // Broadcast to other clients in the quest
        $this->log("Broadcasting chat message for questId=[" . $questId . "] from playerId=[" . $playerId . "] (sessionId=[" . $sessionId . "])", $broadcastMessage);
        $this->broadcastToQuest((int) $questId, $broadcastMessage, $sessionId); // Exclude sender by $sessionId
        // Send the structured message back to the original sender for UI consistency
        //$this->log("Sending chat message back to original sender sessionId=[" . $sessionId . "]", $broadcastMessage);
        $from->send(json_encode($broadcastMessage));

        $this->logEnd("handleChatMessage sessionId=[{$sessionId}]");
    }

    /**
     * Handle a game action
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param array $data
     */
    private function handleGameAction(ConnectionInterface $from, string $sessionId, array $data): void {
        $this->logStart("handleGameAction for sessionId=[{$sessionId}]");
        //$action = $data['action'] ?? '';
        // $this->log("Received game action from session [{$sessionId}]: {$action}");
        // Echo back the action for now
        // In a real app, you would process the action
        $this->sendBack('echo', $from, $data);
        $this->logEnd("handleGameAction");
    }

    private function handleAnnouncePlayerJoin(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logStart("handleAnnouncePlayerJoin from clientId=[{$clientId}], sessionId=[{$sessionId}]", $data);
        $payload = $data['payload'] ?? null;
        $questId = $payload['questId'] ?? null; // Extracted from the client-sent payload

        if (!$payload || !is_numeric($questId)) {
            $this->log("Missing payload or invalid questId for announce_player_join. Payload: " . print_r($payload, true) . " QuestID: " . print_r($questId, true), $data, 'warning');
            $this->sendBack('error', $from, 'Invalid announce_player_join message: missing payload or questId.');
            $this->logEnd("handleAnnouncePlayerJoin");
            return;
        }

        // The client sends data like {'playerId': ..., 'playerName': ..., 'questId': ..., 'questName': ..., 'joinedAt': ...}
        // This entire structure is in $payload.

        $broadcastMessage = [
            'type' => 'new_player_joined', // This is the WebSocket message type clients will receive
            'notificationId' => uniqid('notif_event_'), // A unique ID for this event instance
            'triggerSessionId' => $sessionId, // The sessionId of the player whose client triggered this announcement
            'triggerPlayerId' => $payload['playerId'] ?? null, // The playerId of the new player
            'questId' => (int) $questId,
            'timestamp' => time(),
            'payload' => $payload // This nested payload contains playerName, questName, joinedAt etc.
        ];

        $this->log("Broadcasting 'new_player_joined' event for questId=[{$questId}] triggered by sessionId=[{$sessionId}]", $broadcastMessage);

        // Broadcast to all clients in the quest, *excluding* the sender client identified by $sessionId.
        // Note: broadcastToQuest's third argument is $originSessionId to exclude.
        $this->broadcastToQuest((int) $questId, $broadcastMessage, $sessionId);

        // --- ADD HISTORY RECOVERY HERE ---
        if ($questId) { // Ensure questId is valid before attempting recovery
            $this->log("Attempting to recover message history for session [{$sessionId}] in quest [{$questId}] after player announcement.", $payload, 'info');
            $this->recoverMessageHistory($sessionId);
        } else {
            $this->log("Skipping message history recovery for session [{$sessionId}] due to missing questId in payload.", $payload, 'warning');
        }
        // --- END HISTORY RECOVERY ---
        // Send an acknowledgement back to the sender client
        $this->sendBack('ack', $from, ['type' => 'announce_player_join_processed', 'originalPayload' => $payload]);

        $this->logEnd("handleAnnouncePlayerJoin");
    }

    private function saveQuestChat(int $playerId, int $questId, string $message, int $createdAt): bool {
        $this->logStart("saveQuestChat playerId=[{$playerId}], questId=[{$questId}], message=[{$message}], createdAt=[{$createdAt}]");
        $questChat = new QuestChat([
            'player_id' => $playerId,
            'quest_id' => $questId,
            'message' => $message,
            'created_at' => $createdAt
        ]);

        $return = $questChat->save();
        $this->logEnd("saveQuestChat, returned value=" . ($return ? "true" : "false"));
        return $return;
    }

    private function saveNotification(int $playerId, int $questId, array $data): Notification {
        $this->logStart("saveNotification playerId=[{$playerId}], questId=[{$questId}]", $data);
        $message = $data['message'] ?? '';
        $type = $data['type'] ?? 'unknown'; // For chat, $type will be 'chat'
        $createdAt = time();

        /*
          if ($type === 'chat' && !$this->saveQuestChat($playerId, $questId, $message, $createdAt)) {
          $this->log("saveNotification: Failed to save QuestChat, aborting notification save.", $data, 'error');
          $this->logEnd("saveNotification");
          return null;
          }
         *
         */

        $sender = Player::findOne($playerId);
        if (!$sender) {
            $this->log("saveNotification: Player not found for playerId=[{$playerId}] when creating payload.", null, 'warning');
            $this->logEnd("saveNotification");
            return null;
        }

        $payload = [
            'playerName' => $sender->name,
            'playerId' => $playerId,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s', $createdAt),
        ];

        $notification = new Notification([
            'initiator_id' => $playerId,
            'quest_id' => $questId,
            'notification_type' => $type, // This will be 'chat' for chat messages
            'title' => ($type === 'chat' ? "New chat message from " . $sender->name : ($data['title'] ?? 'Unknown Event')),
            'message' => $message, // For chat, this is the core message text
            'created_at' => $createdAt,
            'is_private' => 0, // Assuming chat notifications are not private by default
            'payload' => json_encode($payload),
        ]);

        if (!$notification->save()) {
            $this->log("saveNotification: Failed to save Notification. Errors: " . print_r($notification->getErrors(), true), $data, 'error');
            $this->logEnd("saveNotification");
            return null;
        }
        $this->logEnd("saveNotification");
        return $notification;
    }

    /**
     * Handle a generic JSON message
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array $data
     */
    private function handleGenericJsonMessage(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logStart("handleGenericJsonMessage for clientId=[{$clientId}]");
        // $this->log("sessionId=[{$sessionId}]", $data);
        $this->log("Received generic JSON message from session [{$sessionId}]");

        $this->sendBack('echo', $from, $data);

        $this->logEnd("handleGenericJsonMessage");
    }

    /**
     * Handle a plain text message
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $message
     */
    private function handleTextMessage(ConnectionInterface $from, string $clientId, string $message): void {
        $this->logStart("handleTextMessage for clientId=[{$clientId}]");
        // $this->log("Received plain text message from client [{$clientId}]: {$message}");
        // Echo back the message
        $this->sendBack('echo', $from, $message);

        $this->logEnd("handleTextMessage");
    }

    /**
     * Handle an error during message processing
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param \Exception $e
     */
    private function handleError(ConnectionInterface $from, string $clientId, \Exception $e): void {
        $this->logStart("handleError");
        $this->log("Error processing message from client [{$clientId}]", $e->getMessage(), 'error');

        // Send error response
        $this->sendBack('error', $from, "Failed to process your message: {$e->getMessage()}");
        $this->logEnd("handleError");
    }

    /**
     * Send an error response
     *
     * @param string $type
     * @param ConnectionInterface $to
     * @param mixed $message
     */
    private function sendBack(string $type, ConnectionInterface $to, mixed $message): void {
        $this->logStart("sendBack type={$type}");
        $to->send(json_encode([
            'type' => $type,
            'message' => $message,
            'timestamp' => time()
        ]));
        $this->logEnd("sendBack");
    }

    /**
     * Send a message to a specific session
     *
     * @param QuestSession $session
     * @param string $jsonData
     * @return bool
     */
    private function sendToSession(QuestSession $session, string $jsonData): bool {
        $this->logStart("sendToSession - message=[{$jsonData}] sessionId={$session->id}");

        $clientId = $session->client_id ?? 'null';

        if (!isset(self::$clients[$clientId])) {
            $this->log("Cannot send message: Unknown clientId [{$clientId}] for QuestSession.id (sessionId)=[{$session->id}], quest_id=[{$session->quest_id}], player_id=[{$session->player_id}]. Current known clientIds: " . implode(', ', array_keys(self::$clients)), null, 'warning');
            $this->logEnd("sendToSession");
            return false;
        }

        try {
            self::$clients[$clientId]->send($jsonData);

            QuestSession::updateAll(
                    ['last_ts' => time()],
                    ['id' => $session->id]
            );
        } catch (\Exception $e) {
            $this->log("Unable to send message to client [{$clientId}]", $e->getMessage(), 'error');
            $this->logEnd("sendToSession");
            return false;
        }

        $this->logEnd("sendToSession");
        return true;
    }

    /**
     * Broadcast a message to all connected clients
     *
     * @param array $data
     */
    public function broadcast(array $data): void {
        $this->logStart("broadcast");
        $json = json_encode($data);

        foreach (self::$clients as $client) {
            $client->send($json);
        }

        $clientCount = count(self::$clients);
        $this->log("Message broadcasted to {$clientCount} clients");
        $this->logEnd("broadcast");
    }

    /**
     * Broadcast a message to all clients in a quest
     *
     * @param int $questId Quest ID
     * @param array $message Message to broadcast
     * @param string|null $sessionId
     */
    public function broadcastToQuest(int $questId, array $message, string|null $sessionId = null): void {
        $this->logStart("broadcastToQuest");
        $this->log("Broadcasting message to quest {$questId} from sessionId=[{$sessionId}]", $message);

        // retrieve other conncted sessions with an active client
        $sessions = $this->findOtherSessions($questId, $sessionId);

        if (!$sessions) {
            $this->log("Quest {$questId} has no other connected clients", null, 'warning');
            // debuging stuff to know what are all the quest related sessions
            $sessions = QuestSession::find()->where(['quest_id' => $questId])->all();
            $this->logQuestSession("QuestSession", $sessions);
            $this->logEnd("broadcastToQuest");
            return;
        }

        // Now send the message to all valid clients
        $sessionCount = 0;
        $encodedMessage = json_encode($message);
        foreach ($sessions as $session) {
            $this->sendToSession($session, $encodedMessage);
            $sessionCount++;
        }

        $this->log("Message broadcasted to {$sessionCount} sessions");
        $this->logEnd("broadcastToQuest");
    }

    /**
     * Find sessions other than the one identified by sessionId
     * belonging to the same quest for which a client is defined
     *
     * @param int $questId
     * @param string|null $sessionId
     * @return array
     */
    private function findOtherSessions(int $questId, string|null $sessionId = null): array {
        $this->logStart("findOtherSessions for questId={$questId} and sessionId=[{$sessionId}]");

        $sessions = QuestSession::find()
                ->where(['quest_id' => $questId])
                ->andWhere(['<>', 'id', $sessionId])
                ->andWhere(['is not', 'client_id', null])
                ->all();
        $sessionCount = count($sessions);
        $this->logQuestSession("{$sessionCount} found sessions", $sessions);
        $this->logEnd("findOtherSessions");
        return $sessions;
    }

    /**
     * Shutdown the server gracefully
     */
    public function shutdown() {
        $this->log("Shutting down WebSocket server...");

        // Close all client connections
        foreach (self::$clients as $client) {
            $client->close();
        }

        // Clear client maps
        self::$clients = [];
        // Stop the event loop
        Loop::get()->stop();
    }
}
