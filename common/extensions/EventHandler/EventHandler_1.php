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
     */
    public $host = '0.0.0.0';  // Listen on all interfaces

    /**
     * @var int The port to bind the WebSocket server to
     */
    public $port = 8082;

    /**
     * @var array Connected clients (clientId => ConnectionInterface)
     */
    private static $clients = [];

    /**
     * @var array Map of session IDs to client IDs (sessionId => [clientId1, clientId2, ...])
     */
    private static $sessionMap = [];

    /**
     * @var array Map of player IDs to session IDs (playerId => [sessionId1, sessionId2, ...])
     */
    private static $playerMap = [];

    /**
     * @var array Map of quest IDs to session IDs (questId => [sessionId1, sessionId2, ...])
     */
    private static $questMap = [];

    /**
     * @var array Message queue for quests (questId => [message1, message2, ...])
     */
    private static $messageQueue = [];

    /**
     * @var array Map of player IDs to quest IDs they're registered for
     */
    private static $playerQuestMap = [];

    /**
     * @var Server start time
     */
    private $startTime;

    /**
     * @var IoServer The server instance
     */
    private $server;

    /**
     * @var bool Whether to output debug messages
     */
    public $debug = true;

    /**
     * @var self Singleton instance
     */
    private static $instance = null;

    /*
     * ********************************************************
     * Temporary functions for debug purpose
     */

    /**
     * Get the quest map (for debugging)
     *
     * @return array
     */
    public function getQuestMap() {
        return self::$questMap;
    }

    /**
     * Get the message queue (for debugging)
     *
     * @return array
     */
    public function getMessageQueue() {
        return self::$messageQueue;
    }

    /**
     * Manually register a client for testing
     *
     * @param string $clientId
     * @param int $playerId
     * @param int $questId
     */
    public function manuallyRegisterClient($clientId, $playerId, $questId) {
        // Register player
        if (!isset(self::$playerMap[$playerId])) {
            self::$playerMap[$playerId] = [];
        }
        if (!in_array($clientId, self::$playerMap[$playerId])) {
            self::$playerMap[$playerId][] = $clientId;
        }

        // Register quest
        if (!isset(self::$questMap[$questId])) {
            self::$questMap[$questId] = [];
        }
        if (!in_array($clientId, self::$questMap[$questId])) {
            self::$questMap[$questId][] = $clientId;
        }

        // Create a mock connection for testing
        self::$clients[$clientId] = new class {

            public function send($message) {
                echo "Mock client received: $message\n";
            }
        };

        $this->log("Manually registered client {$clientId} for player {$playerId} in quest {$questId}");
        //$this->log("Current quest map:", self::$questMap);
        $this->logMaps($questId);
    }

    /*
     * END of Temporary functions for debug purpose
     * ********************************************************
     */


    /*     * *
     * Vérify if this function is still usefull
     */

    /**
     * Get the singleton instance
     *
     * @param array $config Configuration array
     * @return self
     */
    public static function getInstance($config = []) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Log a message
     *
     * @param string $message
     * @param mixed $dump
     * @param string $level
     */
    public function log(string $message, mixed $dump = null, string $level = 'info') {
        if (!$this->debug) {
            return;
        }
        $filename = "c:/temp/EventHandler.log";
        $myfile = fopen($filename, "a") or die("Unable to open file {$filename}!");
        $txt = "[" . date('Y-m-d H:i:s') . "] {$level}: {$message}\n";
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

    private function logMaps(int $questId): void {
        $this->log("     --> sessionMap", self::$sessionMap);
        $this->log("     --> playerMap", self::$playerMap);
        $this->log("     --> questMap", self::$questMap);
        $this->log("     --> playerQuestMap", self::$playerQuestMap);
        $this->log("     --> messageQueue[{$questId}]", self::$messageQueue[$questId] ?? []);
    }

    public function run() {
        // Set error reporting to ignore deprecation warnings
        $previousErrorLevel = error_reporting();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        try {
            $this->log("Starting WebSocket server");
            $this->startTime = time();

            // Get the event loop
            $this->log("    Loop::get");
            $loop = Loop::get();

            // Create your WebSocket handler
            $this->log("    new WebSocketHandler");
            $webSocketHandler = new WebSocketHandler($this);

            // Create a Ratchet WsServer
            $this->log("    new WsServer");
            $wsServer = new WsServer($webSocketHandler);

            // Create an HTTP server to handle the WebSocket handshake
            $this->log("    new HttpServer");
            $httpServer = new HttpServer($wsServer);

            // Create the socket server - listen on all interfaces
            $this->log("    new SocketServer");
            $socketServer = new SocketServer("{$this->host}:{$this->port}");

            // Create the IO server
            $this->log("    new IoServer");
            $this->server = new IoServer($httpServer, $socketServer, $loop);

            $this->log("WebSocket server running at {$this->host}:{$this->port}");
            $this->log("Server start time: " . date('Y-m-d H:i:s', $this->startTime));
            $this->log("---------------------------\n");

            // Run the event loop
            $loop->run();
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
    public function addClient($conn, $clientId) {
        self::$clients[$clientId] = $conn;
        $this->log("---------------------------");
        $this->log("Client added: {$clientId}");
        $this->log("---------------------------\n");
    }

    /**
     * Remove client ID from a map
     *
     * @param array &$map
     * @param string $clientId
     */
    private function removeClientId(&$map, $clientId) {
        foreach ($map as $key => $values) {
            $map[$key] = array_filter($values, function ($value) use ($clientId) {
                return $value !== $clientId;
            });

            // Remove the key if its array becomes empty
            if (empty($map[$key])) {
                $this->log("--> id {$key} is disconnected");
                unset($map[$key]);
            }
        }
    }

    /**
     * Remove a client connection
     *
     * @param string $clientId
     */
    public function removeClient($clientId) {
        $this->log("--------------------------- start removeClient");
        if (isset(self::$clients[$clientId])) {
            unset(self::$clients[$clientId]);
            $this->log("Client removed: {$clientId}\n");
            $this->log("Remove from player map if exists");
            $this->log("    --> playerMap before:", self::$playerMap);
            $this->removeClientId(self::$playerMap, $clientId);
            $this->log("    --> playerMap after:", self::$playerMap);
            $this->log("Remove from quest map if exists");
            $this->log("    --> questMap before:", self::$questMap);
            $this->removeClientId(self::$questMap, $clientId);
            $this->log("    --> questMap after:", self::$questMap);
        }
        $this->log("---------------------------end removeClient\n");
    }

    /**
     * Register a client for a quest (can be called from outside the WebSocket server)
     *
     * @param int $playerId
     * @param int $questId
     * @return bool True if registration was successful, false otherwise
     */
    public function registerPlayerForQuest($playerId, $questId): bool {
        $this->log("\n--------------------------- start registerPlayerForQuest");

        // Validate input parameters
        if (!is_numeric($playerId) || $playerId <= 0 || !is_numeric($questId) || $questId <= 0) {
            $this->log("Invalid player ID or quest ID", null, 'error');
            $this->log("--------------------------- end registerPlayerForQuest\n");
            return false;
        }

        $this->log("Registering player {$playerId} for quest {$questId}");

        try {
            // Step 1: Update the player-quest relationship
            $this->addQuestToPlayerMap($playerId, $questId);

            // Step 2: Register any existing active connections for this player
            $activeConnections = $this->registerActiveConnectionsForQuest($playerId, $questId);

            // Step 3: Log the registration status
            $this->logRegistrationStatus($playerId, $questId, $activeConnections);

            $this->log("--------------------------- end registerPlayerForQuest\n");
            return true;
        } catch (\Exception $e) {
            $this->log("Error registering client for quest: " . $e->getMessage(), null, 'error');
            $this->log("--------------------------- end registerPlayerForQuest\n");
            return false;
        }
    }

    /**
     * Add a quest to a player's quest map
     *
     * @param int $playerId
     * @param int $questId
     */
    private function addQuestToPlayerMap(int $playerId, int $questId): void {
        if (!isset(self::$playerQuestMap[$playerId])) {
            self::$playerQuestMap[$playerId] = [];
        }

        if (!in_array($questId, self::$playerQuestMap[$playerId])) {
            self::$playerQuestMap[$playerId][] = $questId;
        }
    }

    /**
     * Register all active connections for a player to a quest
     *
     * @param int $playerId
     * @param int $questId
     * @return int Number of active connections registered
     */
    private function registerActiveConnectionsForQuest($playerId, $questId) {
        $activeConnections = 0;

        if (isset(self::$playerMap[$playerId])) {
            foreach (self::$playerMap[$playerId] as $clientId) {
                if (isset(self::$clients[$clientId])) {
                    $this->registerQuest($questId, $clientId);
                    $activeConnections++;
                }
            }
        }

        return $activeConnections;
    }

    /**
     * Log the registration status for a player and quest
     *
     * @param int $playerId
     * @param int $questId
     * @param int $activeConnections
     */
    private function logRegistrationStatus(int $playerId, int $questId, int $activeConnections) {
        $this->log("Player {$playerId} registered for quest {$questId}");

        if ($activeConnections > 0) {
            $this->log("Registered {$activeConnections} active WebSocket connection(s) for the quest");
        } else {
            $this->log("Player has no active WebSocket connections yet");
        }

        //$this->log("    -> Current quest map:", self::$questMap);
        $this->logMaps($questId);
    }

    /**
     * Handle a message from a client
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $msg
     */
    public function handleMessage($from, $clientId, $msg) {
        $this->log("\n--------------------------- start handleMessage");
        $this->log("Processing message from {$clientId}: {$msg}");

        try {
            $data = json_decode($msg, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->handleJsonMessage($from, $clientId, $data);
            } else {
                $this->handleTextMessage($from, $clientId, $msg);
            }
        } catch (\Exception $e) {
            $this->handleError($from, $clientId, $e);
        }
        $this->log("--------------------------- end handleMessage\n");
    }

    /**
     * Handle a JSON message
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleJsonMessage($from, $clientId, $data) {
        if (isset($data['type'])) {
            $this->handleTypedMessage($from, $clientId, $data);
        } elseif (isset($data['playerId'])) {
            $this->handleRegistration($from, $clientId, $data);
        } else {
            $this->handleGenericJsonMessage($from, $clientId, $data);
        }
    }

    /**
     * Handle a message with a type field
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleTypedMessage($from, $clientId, $data) {
        $type = $data['type'];

        match ($type) {
            'register' => $this->handleRegistration($from, $clientId, $data),
            'chat' => $this->handleChatMessage($from, $clientId, $data),
            'action' => $this->handleGameAction($from, $clientId, $data),
            default => $this->handleUnknownType($from, $clientId, $type, $data)
        };
    }

    /**
     * Handle an unknown message type
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $type
     * @param array $data
     */
    private function handleUnknownType($from, $clientId, $type, $data) {
        $this->log("--> Unknown message type '{$type}' from {$clientId}", $data, 'warning');
        $this->sendEcho($from, $data);
    }

    /**
     * Register a player with a client ID
     *
     * @param int $playerId
     * @param string $clientId
     * @return bool
     */
    private function registerPlayer(int $playerId, string $clientId): bool {
        if (!$playerId) {
            return false;
        }
        // Initialize the player array if it doesn't exist
        if (!isset(self::$playerMap[$playerId])) {
            self::$playerMap[$playerId] = [];
        }

        // Add the client ID to the player's array if not already there
        if (!in_array($clientId, self::$playerMap[$playerId])) {
            self::$playerMap[$playerId][] = $clientId;
            $this->log("Registered player {$playerId} with client {$clientId}\nPlayerMap:\n", self::$playerMap);
        }

        // Check if this player has any quests they should be registered for
        if (isset(self::$playerQuestMap[$playerId])) {
            foreach (self::$playerQuestMap[$playerId] as $questId) {
                $this->registerQuest($questId, $clientId);
                $this->log("Auto-registered client {$clientId} for quest {$questId} based on player-quest map");
            }
        }

        return true;
    }

    /**
     * Register a client for a quest
     *
     * @param int $questId
     * @param string $clientId
     */
    private function registerQuest(int $questId, string $clientId): void {
        // Initialize the quest array if it doesn't exist
        if (!isset(self::$questMap[$questId])) {
            self::$questMap[$questId] = [];
        }

        // Add the client ID to the quest's array if not already there
        if (!in_array($clientId, self::$questMap[$questId])) {
            self::$questMap[$questId][] = $clientId;
            $this->log("Registered quest {$questId} with client {$clientId}\nQuestMap:\n", self::$questMap);

            // Deliver any queued messages for this quest to the client
            $this->deliverQueuedMessages($questId, $clientId);
        }
    }

    /**
     * Deliver queued messages for a quest to a specific client
     *
     * @param int $questId
     * @param string $clientId
     */
    private function deliverQueuedMessages(int $questId, string $clientId): void {
        if (!isset(self::$messageQueue[$questId]) || empty(self::$messageQueue[$questId])) {
            $this->log("No queued messages for quest {$questId}");
            return;
        }

        if (!isset(self::$clients[$clientId])) {
            $this->log("Cannot deliver queued messages: client {$clientId} not found", null, 'warning');
            return;
        }

        $conn = self::$clients[$clientId];
        $messageCount = count(self::$messageQueue[$questId]);
        $this->log("Delivering {$messageCount} queued messages to client {$clientId} for quest {$questId}");

        foreach (self::$messageQueue[$questId] as $message) {
            $conn->send(json_encode($message));
            $this->log("Delivered queued message to client {$clientId}", $message);
        }
    }

    /**
     * Handle player registration
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleRegistration($from, $clientId, $data) {
        $this->log("--------------------------- handleRegistration");
        $playerId = $data['playerId'] ?? null;
        if (!$playerId) {
            $this->sendError($from, 'Missing playerId in registration');
            return;
        }

        // Register the player
        $this->registerPlayer($playerId, $clientId);

        // If quest ID is also provided, register it
        if (isset($data['questId'])) {
            $questId = $data['questId'];
            $this->registerQuest($questId, $clientId);
        }

        // Check if this player should be registered for any other quests
        if (isset(self::$playerQuestMap[$playerId])) {
            foreach (self::$playerQuestMap[$playerId] as $questId) {
                // Skip if it's the same quest ID that was already registered above
                if (isset($data['questId']) && $data['questId'] == $questId) {
                    continue;
                }
                $this->registerQuest($questId, $clientId);
                $this->log("Auto-registered client {$clientId} for quest {$questId} based on player-quest map");
            }
        }

        // Send acknowledgment
        $from->send(json_encode([
            'type' => 'connected',
            'playerId' => $playerId,
            'timestamp' => time()
        ]));

        $this->log("Sent connection acknowledgment to {$clientId}");
        $this->log("---------------------------");
    }

    /**
     * Handle a chat message
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleChatMessage($from, $clientId, $data) {
        $text = $data['text'] ?? '';
        $channel = $data['channel'] ?? 'global';

        $this->log("Received chat message from {$clientId} on channel {$channel}: {$text}\n");

        // Echo back the message for now
        // In a real app, you might broadcast to other clients
        $this->sendEcho($from, $data);
    }

    /**
     * Handle a game action
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleGameAction($from, $clientId, $data) {
        $action = $data['action'] ?? '';

        $this->log("Received game action from {$clientId}: {$action}\n");

        // Echo back the action for now
        // In a real app, you would process the action
        $this->sendEcho($from, $data);
    }

    /**
     * Handle a generic JSON message
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleGenericJsonMessage($from, $clientId, $data) {
        $this->log("Received generic JSON message from {$clientId}\n");
        $this->sendEcho($from, $data);
    }

    /**
     * Handle a plain text message
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $msg
     */
    private function handleTextMessage($from, $clientId, $msg) {
        $this->log("Received plain text message from {$clientId}: {$msg}\n");

        // Echo back the message
        $from->send(json_encode([
            'type' => 'echo',
            'text' => $msg,
            'timestamp' => time()
        ]));
    }

    /**
     * Handle an error during message processing
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param \Exception $e
     */
    private function handleError($from, $clientId, $e) {
        $this->log("Error processing message from {$clientId}:\n", $e->getMessage(), 'error');

        // Send error response
        $this->sendError($from, 'Failed to process your message');
    }

    /**
     * Send an echo response
     *
     * @param ConnectionInterface $to
     * @param mixed $originalMessage
     */
    private function sendEcho($to, $originalMessage) {
        $to->send(json_encode([
            'type' => 'echo',
            'originalMessage' => $originalMessage,
            'timestamp' => time()
        ]));
    }

    /**
     * Send an error response
     *
     * @param ConnectionInterface $to
     * @param string $message
     */
    private function sendError($to, $message) {
        $to->send(json_encode([
            'type' => 'error',
            'message' => $message,
            'timestamp' => time()
        ]));
    }

    /**
     * Send a message to a specific player
     *
     * @param string $playerId
     * @param array $data
     * @return bool
     */
    public function sendToPlayer($playerId, $data) {
        if (isset(self::$playerMap[$playerId])) {
            $clientIds = self::$playerMap[$playerId];
            $sent = false;

            foreach ($clientIds as $clientId) {
                if (isset(self::$clients[$clientId])) {
                    self::$clients[$clientId]->send(json_encode($data));
                    $this->log("Sent message to player {$playerId} via client {$clientId}");
                    $sent = true;
                }
            }

            if ($sent) {
                return true;
            }
        }

        $this->log("Player {$playerId} not found or not connected\n", null, 'warning');
        return false;
    }

    /**
     * Broadcast a message to all connected clients
     *
     * @param array $data
     */
    public function broadcast(array $data): void {
        $this->log("Broadcasting", $data);
        $json = json_encode($data);

        foreach (self::$clients as $client) {
            $client->send($json);
        }

        $clientCount = count(self::$clients);
        $this->log("Message broadcasted to {$clientCount} clients");
    }

    /**
     * Broadcast a message to all clients in a quest
     *
     * @param int $questId Quest ID
     * @param array $message Message to broadcast
     */
    public function broadcastToQuest(int $questId, array $message): void {
        $this->log("--------------------------- start broadcastToQuest");
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
        /*
          $this->log("     --> playerMap", self::$playerMap);
          $this->log("     --> questMap", self::$questMap);
          $this->log("     --> messageQueue[{$questId}]", self::$messageQueue[$questId] ?? []);
         *
         */

        // If no clients are registered for this quest, just queue the message
        if (!isset(self::$questMap[$questId]) || empty(self::$questMap[$questId])) {
            $this->log("--> Quest {$questId} has no connected clients, message queued", null, 'warning');
            $this->log("--------------------------- end broadcastToQuest\n");
            return;
        }

        $encodedMessage = json_encode($message);
        $validClientIds = $this->getValidClientIds($questId);

        // Update the quest map with only valid clients
        if (empty($validClientIds)) {
            unset(self::$questMap[$questId]);
            $this->log("Quest {$questId} has no more valid clients and was removed from the map");
            $this->log("--------------------------- end broadcastToQuest\n");
            return;
        }

        self::$questMap[$questId] = $validClientIds;

        // Now send the message to all valid clients
        $questClientCount = 0;
        foreach ($validClientIds as $clientId) {
            self::$clients[$clientId]->send($encodedMessage);
            $questClientCount++;
        }

        $this->log("Message broadcasted to {$questClientCount} quest clients");
        $this->log("--------------------------- end broadcastToQuest\n");
    }

    private function getValidClientIds(int $questId): array {
        $validClientIds = [];

        // First, check which clients are actually connected
        foreach (self::$questMap[$questId] as $clientId) {
            if (isset(self::$clients[$clientId])) {
                $validClientIds[] = $clientId;
            } else {
                $this->log("--> Client {$clientId} not found", null, 'warning');
            }
        }

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
        self::$playerMap = [];
        self::$questMap = [];
        self::$messageQueue = [];
        self::$playerQuestMap = [];

        // Stop the event loop
        Loop::get()->stop();
    }
}
