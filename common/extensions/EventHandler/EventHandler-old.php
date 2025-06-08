<?php

namespace common\extensions\EventHandler;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Ratchet\ConnectionInterface;
use Yii;
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
     * @var array Map of session IDs to client IDs (sessionId => [clientId1, clientId2, ...])
     * @var array Map of player IDs to session IDs (playerId => [sessionId1, sessionId2, ...])
     * @var array Map of quest IDs to session IDs (questId => [sessionId1, sessionId2, ...])
     */
    private static $sessionMap = [];
    private static $playerMap = [];
    private static $questMap = [];

    /**
     * @var array Message queue for quests (questId => [message1, message2, ...])
     */
    private static $messageQueue = [];

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

    private function logStart(string $message, mixed $dump = null, string $level = 'info'): void {
        $this->nestedLevel++;
        $this->log("----------> start {$message}", $dump, $level);
    }

    private function logEnd(string $message, mixed $dump = null, string $level = 'info'): void {
        $this->log("----------< end {$message}", $dump, $level);
        $this->nestedLevel--;
    }

    private function logMaps(int $questId = null): void {
        $this->log("sessionMap:", self::$sessionMap);
        $this->log("playerMap:", self::$playerMap);
        $this->log("questMap:", self::$questMap);
        if ($questId) {
            $this->log("messageQueue[{$questId}]:", self::$messageQueue[$questId] ?? []);
        } else {
            $this->log("messageQueue:", self::$messageQueue);
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
            $this->logEnd("WebSocket server\n");

            // Run the event loop
            $loop->run();
        } catch (\Exception $e) {
            $this->log("Exception", $e, 'error');
        } finally {
            // Restore previous error reporting level
            error_reporting($previousErrorLevel);
        }
    }

    /**
     * Add a client connection
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     */
    public function addClient(ConnectionInterface $conn, string $clientId): void {
        $this->logStart("addClient");
        self::$clients[$clientId] = $conn;
        $this->log("Client added: {$clientId}");
        $this->logEnd("addClient\n");
    }

    /**
     * Remove a client connection
     *
     * @param string $clientId
     */
    public function removeClient(string $clientId): void {
        $this->logStart("removeClient");
        if (isset(self::$clients[$clientId])) {
            unset(self::$clients[$clientId]);
            $this->log("Client removed: {$clientId}");
        }
        /*
          // Search for the session that opened the client connection
          $sessionId = array_search($clientId, self::$sessionMap, true);
          if ($sessionId) {
          unset(self::$sessionMap[$sessionId]);
          $this->removeFromMap(self::$playerMap, $sessionId);
          $this->removeFromMap(self::$questMap, $sessionId);
          } else {
          $this->log("Could not find a session associated with client {$clientId}", null, 'warning');
          }
         *
         * Assume sessionId is never destroyed until recieving an explicit disconnection
         */
        $this->logMaps();
        $this->logEnd("removeClient\n");
    }

    /**
     * Register a client for a quest (can be called from outside the WebSocket server)
     *
     * @param int $playerId
     * @param int $questId
     * @return bool True if registration was successful, false otherwise
     */
    public function registerPlayerForQuest($playerId, $questId): bool {
        $this->logStart("registerPlayerForQuest");

        // Validate input parameters
        if (!is_numeric($playerId) || $playerId <= 0 || !is_numeric($questId) || $questId <= 0) {
            $this->log("Invalid player ID or quest ID", null, 'error');
            $this->logEnd("registerPlayerForQuest\n");
            return false;
        }

        try {
            // Register any existing active connections for this player
            $activeConnections = $this->registerActiveConnectionsForQuest($playerId, $questId);
            $this->logRegistrationStatus($playerId, $questId, $activeConnections);
            $this->logEnd("registerPlayerForQuest\n");
            return true;
        } catch (\Exception $e) {
            $this->log("Error registering player {$playerId} for quest {$questId}: ", $e, 'error');
            $this->logEnd("registerPlayerForQuest\n");
            return false;
        }
    }

    /**
     * Register all active connections for a player to a quest
     *
     * @param int $playerId
     * @param int $questId
     * @return int Number of active connections registered
     */
    private function registerActiveConnectionsForQuest(int $playerId, int $questId): int {
        $this->logStart("registerActiveConnectionsForQuest");
        $this->log("playerId={$playerId}, questId={$questId}");
        $activeConnections = 0;

        if (isset(self::$playerMap[$playerId])) {
            foreach (self::$playerMap[$playerId] as $sessionId) {
                if (isset(self::$sessionMap[$sessionId])) {
                    $this->registerQuest($questId, $sessionId);
                    $activeConnections++;
                }
            }
        }

        $this->log("return current active connections={$activeConnections}");
        $this->logEnd("registerActiveConnectionsForQuest\n");
        return $activeConnections;
    }

    /**
     * Log the registration status for a player and quest
     *
     * @param int $playerId
     * @param int $questId
     * @param int $activeConnections
     */
    private function logRegistrationStatus(int $playerId, int $questId, int $activeConnections): void {
        $this->log("playerId={$playerId}, questId={$questId}, activeConnections={$activeConnections}");

        if ($activeConnections > 0) {
            $this->log("Registered {$activeConnections} active session(s) for the quest");
            $this->logMaps($questId);
        } else {
            $this->log("Player has no active session yet");
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
        $this->log("Processing message from client {$clientId}:", $message);

        try {
            $data = json_decode($message, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->handleJsonMessage($from, $clientId, $data);
            } else {
                $this->handleTextMessage($from, $clientId, (string) $message);
            }
        } catch (\Exception $e) {
            $this->handleError($from, $clientId, $e);
        }
        $this->logMaps();
        $this->logEnd("handleMessage\n");
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
        $this->log("Processing JSON message from client {$clientId}:", $data);

        $sessionId = $data['sessionId'] ?? null;
        if (!$sessionId) {
            $this->sendError($from, 'Missing SessionId in source message');
            return;
        }
        $this->log("sessionId={$sessionId}");

        // The session keeps the link between the connection socket,
        // which changes each time the page is refreshed,
        // and the previous actions of players and quests.
        self::$sessionMap[$sessionId] = $clientId;

        if (isset($data['type'])) {
            $this->handleTypedMessage($from, $sessionId, $data);
        } elseif (isset($data['playerId'])) {
            $this->handleRegistration($from, $sessionId, $data);
        } else {
            $this->handleGenericJsonMessage($from, $sessionId, $data);
        }
        $this->logEnd("handleJsonMessage\n");
    }

    /**
     * Handle a message with a type field
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param array $data
     */
    private function handleTypedMessage(ConnectionInterface $from, string $sessionId, array $data): void {
        $this->logStart("handleTypedMessage");
        $type = $data['type'];
        $this->log("Processing typed '{$type}' message from session {$sessionId}:");

        match ($type) {
            'register' => $this->handleRegistration($from, $sessionId, $data),
            'chat' => $this->handleChatMessage($from, $sessionId, $data),
            'action' => $this->handleGameAction($from, $sessionId, $data),
            'heartbeat' => $this->handleHeartbeat($sessionId),
            default => $this->handleUnknownType($from, $sessionId, $type, $data)
        };
        $this->logEnd("handleTypedMessage\n");
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
        $this->log("Unknown message type '{$type}' from session {$sessionId}", $data, 'warning');
        $this->sendEcho($from, $data);
        $this->logEnd("handleUnknownType\n");
    }

    /**
     * Handle heartbeat type
     *
     * @param string $sessionId
     */
    private function handleHeartbeat(string $sessionId): void {
        $this->log("Heart beat - Session {$sessionId} is still alive!");
    }

    private function removeFromMap(array &$map, string $valueToRemove): void {
        $this->logStart("removeFromMap");
        $this->log("Value to remove {$valueToRemove}", $map);
        foreach ($map as $key => $values) {
            // First filter
            $filteredMap = array_filter($values, function ($v) use ($valueToRemove) {
                return $v !== $valueToRemove;
            });

            // Then reindex
            $reindexedMap = array_values($filteredMap);

            // Assign or remove
            if (!empty($reindexedMap)) {
                $map[$key] = $reindexedMap;
            } else {
                unset($map[$key]);
            }
        }
        $this->log("After removal", $map);
        $this->logEnd("removeFromMap\n");
    }

    /**
     * Register a player with a client ID
     *
     * @param int $playerId
     * @param string $sessionId
     * @return bool
     */
    private function registerPlayer(int $playerId, string $sessionId): bool {
        $this->logStart("registerPlayer");
        $this->log("playerId={$playerId}, sessionId={$sessionId}");
        if (!$playerId) {
            $this->logEnd("registerPlayer\n");
            return false;
        }
        // Initialize the player array if it doesn't exist
        if (!isset(self::$playerMap[$playerId])) {
            self::$playerMap[$playerId] = [];
        }

        // Add the client ID to the player's array if not already there
        if (!in_array($sessionId, self::$playerMap[$playerId])) {
            self::$playerMap[$playerId][] = $sessionId;
            $this->log("Registered player {$playerId} with session {$sessionId}: playerMap", self::$playerMap);
        }

        $this->logEnd("registerPlayer\n");
        return true;
    }

    /**
     * Register a client for a quest
     *
     * @param int $questId
     * @param string $sessionId
     */
    private function registerQuest(int $questId, string $sessionId): void {
        $this->logStart("registerQuest");
        $this->log("questId={$questId}, sessionId={$sessionId}");
        // Initialize the quest array if it doesn't exist
        if (!isset(self::$questMap[$questId])) {
            self::$questMap[$questId] = [];
        }

        // Add the client ID to the quest's array if not already there
        if (!in_array($sessionId, self::$questMap[$questId])) {
            self::$questMap[$questId][] = $sessionId;
            $this->log("Registered quest {$questId} with session {$sessionId}: questMap", self::$questMap[$questId]);

            // Deliver any queued messages for this quest to the client
            $this->deliverQueuedMessages($questId, $sessionId);
        }
        $this->logEnd("registerQuest\n");
    }

    /**
     * Deliver queued messages for a quest to a specific client
     *
     * @param int $questId
     * @param string $sessionId
     */
    private function deliverQueuedMessages(int $questId, string $sessionId): void {
        $this->logStart("deliverQueuedMessages");
        $this->log("questId={$questId}, sessionId={$sessionId}");
        if (!isset(self::$messageQueue[$questId]) || empty(self::$messageQueue[$questId])) {
            $this->log("No queued messages for quest {$questId}");
            $this->logEnd("deliverQueuedMessages\n");
            return;
        }

        $clientId = self::$sessionMap[$sessionId] ?? 'unknown';
        if (!isset(self::$clients[$clientId])) {
            $this->log("Cannot deliver queued messages: client {$clientId} not found", null, 'warning');
            $this->logEnd("deliverQueuedMessages\n");
            return;
        }

        $conn = self::$clients[$clientId];
        foreach (self::$messageQueue[$questId] as $message) {
            $conn->send(json_encode($message));
            $this->log("Queued message delivered to client {$clientId} from session {$sessionId}", $message);
        }
        $this->logEnd("deliverQueuedMessages\n");
    }

    /**
     * Handle player registration
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param array $data
     */
    private function handleRegistration(ConnectionInterface $from, string $sessionId, array $data): void {
        $this->logStart("handleRegistration");
        $this->log("sessionId={$sessionId}");
        $playerId = $data['playerId'] ?? null;
        if (!$playerId) {
            $this->sendError($from, 'Missing playerId in registration');
            $this->logEnd("handleRegistration\n");
            return;
        }

        // Register the player
        $this->registerPlayer($playerId, $sessionId);

        // If quest ID is also provided, register it
        $questId = (isset($data['questId'])) ? $data['questId'] : null;
        if ($questId) {
            $this->registerQuest($questId, $sessionId);
        }

        // Send acknowledgment
        $from->send(json_encode([
            'type' => 'connected',
            'playerId' => $playerId,
            'timestamp' => time()
        ]));

        $this->log("Sent connection acknowledgment to session {$sessionId}");
        $this->logEnd("handleRegistration\n");
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
        $this->log("sessionId={$sessionId}", $data);

        //$text = $data['text'] ?? '';
        //$channel = $data['channel'] ?? 'global';
        $message = $data['message'] ?? '';
        $playerId = $data['playerId'] ?? 'unknown';
        $questId = $data['questId'];

        // Echo back the message for now
        // In a real app, you might broadcast to other clients
        if ($questId) {
            $this->log("Received chat message from player {$playerId} in session {$sessionId} for quest {$questId}: {$message}");
            $this->broadcastToQuest($questId, $data);
        } else {
            $this->log("Received chat message from player {$playerId} in session {$sessionId}: {$message}");
            $this->broadcast($data);
        }
        $this->sendEcho($from, $data);
        $this->logEnd("handleChatMessage\n");
    }

    /**
     * Handle a game action
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param array $data
     */
    private function handleGameAction(ConnectionInterface $from, string $sessionId, array $data): void {
        $this->logStart("handleGameAction");
        $this->log("sessionId={$sessionId}", $data);
        $action = $data['action'] ?? '';

        $this->log("Received game action from session {$sessionId}: {$action}");

        // Echo back the action for now
        // In a real app, you would process the action
        $this->sendEcho($from, $data);
        $this->logEnd("handleGameAction\n");
    }

    /**
     * Handle a generic JSON message
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param array $data
     */
    private function handleGenericJsonMessage(ConnectionInterface $from, string $sessionId, array $data): void {
        $this->logStart("handleGenericJsonMessage");
        $this->log("sessionId={$sessionId}", $data);
        $this->log("Received generic JSON message from session {$sessionId}");
        $this->sendEcho($from, $data);
        $this->logEnd("handleGenericJsonMessage\n");
    }

    /**
     * Handle a plain text message
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param string $message
     */
    private function handleTextMessage(ConnectionInterface $from, string $sessionId, string $message): void {
        $this->logStart("handleTextMessage");
        $this->log("Received plain text message from session {$sessionId}: {$message}");

        // Echo back the message
        $from->send(json_encode([
            'type' => 'echo',
            'text' => $message,
            'timestamp' => time()
        ]));
        $this->logEnd("handleTextMessage\n");
    }

    /**
     * Handle an error during message processing
     *
     * @param ConnectionInterface $from
     * @param string $sessionId
     * @param \Exception $e
     */
    private function handleError(ConnectionInterface $from, string $sessionId, \Exception $e): void {
        $this->logStart("handleError");
        $this->log("Error processing message from session {$sessionId}", $e, 'error');

        // Send error response
        $this->sendError($from, 'Failed to process your message');
        $this->logEnd("handleError\n");
    }

    /**
     * Send an echo response
     *
     * @param ConnectionInterface $to
     * @param mixed $originalMessage
     */
    private function sendEcho(ConnectionInterface $to, mixed $originalMessage): void {
        $this->logStart("sendEcho");
        $this->log("Echo orignal message", $originalMessage);
        $to->send(json_encode([
            'type' => 'echo',
            'originalMessage' => $originalMessage,
            'timestamp' => time()
        ]));
        $this->logEnd("sendEcho\n");
    }

    /**
     * Send an error response
     *
     * @param ConnectionInterface $to
     * @param mixed $message
     */
    private function sendError(ConnectionInterface $to, mixed $message): void {
        $this->logStart("sendError");
        $this->log("Error message", $message);
        $to->send(json_encode([
            'type' => 'error',
            'message' => $message,
            'timestamp' => time()
        ]));
        $this->logEnd("sendError\n");
    }

    /**
     * Send a message to a specific player
     *
     * @param int $playerId
     * @param array $data
     * @return bool
     */
    public function sendToPlayer(int $playerId, array $data): bool {
        $this->logStart("sendToPlayer");
        $this->log("Sending message to player {$playerId}", $data);
        $recipients = 0;
        if (isset(self::$playerMap[$playerId])) {
            // We need to bear in mind that the player may be using different
            // devices or browser tabs at the same time.
            $sessionIds = self::$playerMap[$playerId];

            foreach ($sessionIds as $sessionId) {
                $clientId = self::$sessionMap[$sessionId] ?? 'unknown';
                if (isset(self::$clients[$clientId])) {
                    self::$clients[$clientId]->send(json_encode($data));
                    $this->log("Sent message to player {$playerId} in session {$sessionId} via client {$clientId}");
                    $recipients++;
                }
            }
        }

        if ($recipients > 0) {
            $this->log("Message sent to {$recipients} recipient(s)", $data);
        } else {
            $this->log("Player {$playerId} not found or not connected\n", null, 'warning');
        }
        $this->logEnd("sendToPlayer\n");
        return ($recipients > 0);
    }

    /**
     * Broadcast a message to all connected clients
     *
     * @param array $data
     */
    public function broadcast(array $data): void {
        $this->logStart("broadcast");
        $this->log("Broadcasting", $data);
        $json = json_encode($data);

        foreach (self::$clients as $client) {
            $client->send($json);
        }

        $clientCount = count(self::$clients);
        $this->log("Message broadcasted to {$clientCount} clients");
        $this->logEnd("broadcast\n");
    }

    /**
     * Broadcast a message to all clients in a quest
     *
     * @param int $questId Quest ID
     * @param array $message Message to broadcast
     */
    public function broadcastToQuest(int $questId, array $message): void {
        $this->logStart("broadcastToQuest");
        $this->log("Broadcasting message to quest {$questId}", $message);

        // Always queue the message for future clients
        if (!isset(self::$messageQueue[$questId])) {
            self::$messageQueue[$questId] = [];
        }

        // Add message to queue (limit to last 50 messages to prevent memory issues)
        self::$messageQueue[$questId][] = $message;
        if (count(self::$messageQueue[$questId]) > 50) {
            array_shift(self::$messageQueue[$questId]);
        }

        $this->logMaps($questId);

        // If no clients are registered for this quest, just queue the message
        if (!isset(self::$questMap[$questId]) || empty(self::$questMap[$questId])) {
            $this->log("Quest {$questId} has no connected clients, message queued", null, 'warning');
            $this->logEnd("broadcastToQuest\n");
            return;
        }

        // Update the quest map with only valid clients
        $validClientIds = $this->getValidClientIds($questId);
        if (empty($validClientIds)) {
            unset(self::$questMap[$questId]);
            $this->log("Quest {$questId} has no more valid clients and was removed from the map", null, 'warning');
            $this->logEnd("broadcastToQuest\n");
            return;
        }

        // Now send the message to all valid clients
        $questClientCount = 0;
        $encodedMessage = json_encode($message);
        foreach ($validClientIds as $clientId) {
            self::$clients[$clientId]->send($encodedMessage);
            $questClientCount++;
        }

        $this->log("Message broadcasted to {$questClientCount} quest clients");
        $this->logEnd("broadcastToQuest\n");
    }

    private function getValidClientIds(int $questId): array {
        $this->logStart("getValidClientIds");
        $this->log("questId={$questId}");
        $validClientIds = [];

        // First, check which clients are actually connected
        foreach (self::$questMap[$questId] as $sessionId) {
            $clientId = self::$sessionMap[$sessionId] ?? 'unknown';
            if (isset(self::$clients[$clientId])) {
                $validClientIds[] = $clientId;
            } else {
                $this->log("Client {$clientId} not found for session {$sessionId}", $sessionMap, 'warning');
            }
        }

        $this->log("List of valid IDs", $validClientIds);
        $this->logEnd("getValidClientIds\n");
        return $validClientIds;
    }

    /**
     * Shutdown the server gracefully
     */
    public function shutdown() {
        $this->log("Shutting down WebSocket server...\n");

        // Close all client connections
        foreach (self::$clients as $client) {
            $client->close();
        }

        // Clear client maps
        self::$clients = [];
        self::$sessionMap = [];
        self::$playerMap = [];
        self::$questMap = [];
        self::$messageQueue = [];
        // Stop the event loop
        Loop::get()->stop();
    }
}
