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
        $rowsUpdated = QuestSession::updateAll(
                ['client_id' => null],
                ['client_id' => $clientId]
        );
        if ($rowsUpdated === 0) {
            $this->log("Could not find a session associated with client [{$clientId}]", null, 'warning');
            $this - $this->logQuestSession();
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
        $this->logQuestSession();
        $this->logEnd("newSession");
        return $session->save();
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
        $updated = $needUpdate ? $session->save() : true;
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
        $this->logStart("recoverMessageHistory");

        $session = QuestSession::findOne(['id' => $sessionId]);
        if (!$session) {
            $this->log("sessionId [{$sessionId}] is unknown!", null, 'warning');
            $this->logEnd("recoverMessageHistory");
            return;
        }

        // Collects message notifications for the current quest
        $chatNotifications = $this->getNotifications($session->quest_id, 'chat', $session->last_ts);

        $recoveredMessages = 0;
        foreach ($chatNotifications as $notification) {
            $chatMessage = $this->prepareChatMessage($notification, $sessionId);
            $recoveredMessages += $this->sendToSession($session, $chatMessage) ? 1 : 0;
        }
        $this->logEnd("recoverMessageHistory");
    }

    /**
     *
     * @param int $questId
     * @param string $type
     * @param int $since
     * @return array
     */
    private function getNotifications(int $questId, string $type, int $since): array {
        $this->logStart("getNotifications");
        $notifications = Notification::find()
                ->where(['quest_id' => $questId ?? 0])
                ->andWhere(['notification_type' => $type ?? 'unknown'])
                ->andWhere(['>', 'created_at', $since ?? 0])
                ->orderBy(['created_at' => SORT_ASC])
                ->all();
        $notificationCount = count($notifications);
        $this->log("{$notificationCount} notification(s) found");
        $this->logEnd("getNotifications");
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
        $this->logStart("handleChatMessage");
        // $this->log("sessionId=[{$sessionId}]", $data);

        $message = $data['message'] ?? '';
        $playerId = $data['playerId'] ?? 'unknown';
        $questId = $data['questId'];

        if ($questId) {
            $this->log("Received chat message from player {$playerId} in session [{$sessionId}] for quest {$questId}: {$message}");
            $this->broadcastToQuest($questId, $data, $sessionId);
        } else {
            $this->log("Received chat message from player {$playerId} in session [{$sessionId}]: {$message}");
            $this->broadcast($data);
        }
        //$this->sendBack('echo', $from, $data);
        $from->send(json_encode($data));

        $this->logEnd("handleChatMessage");
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
            $this->log("Unknown clientId [{$clientId}]", array_keys(self::$clients), 'warning');
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
