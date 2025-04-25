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
     * @var array Connected clients
     */
    private $clients = [];

    /**
     * @var array Map of player IDs to client connections
     */
    private $playerMap = [];

    /**
     * @var array Map of quest IDs to client connections
     */
    private $questMap = [];

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
    public $debug = false;

    /**
     * Log a message
     *
     * @param string $message
     * @param string $level
     */
    private function log($message, $level = 'info') {
        if ($this->debug) {
            // Only output to console if in CLI mode
            if (php_sapi_name() === 'cli') {
                $this->log("[" . date('Y-m-d H:i:s') . "] {$level}: {$message}\n");
            }
        }

        // Always log to Yii's logger
        switch ($level) {
            case 'error':
                Yii::error($message, 'websocket');
                break;
            case 'warning':
                Yii::warning($message, 'websocket');
                break;
            case 'info':
            default:
                Yii::info($message, 'websocket');
                break;
        }
    }

    public function run() {
        // Set error reporting to ignore deprecation warnings
        $previousErrorLevel = error_reporting();
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        try {
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

            $this->log("WebSocket server running at {$this->host}:{$this->port}\n");
            $this->log("Server start time: " . date('Y-m-d H:i:s', $this->startTime) . "\n");
            $this->log("---------------------------\n");
            $this->log("\n");

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
        $this->clients[$clientId] = $conn;
        $this->log("Client added: {$clientId}\n");
        $this->log("---------------------------\n");
        $this->log("\n");
    }

    /**
     * Remove a client connection
     *
     * @param string $clientId
     */
    public function removeClient($clientId) {
        if (isset($this->clients[$clientId])) {
            unset($this->clients[$clientId]);
            $this->log("Client removed: {$clientId}\n");

            // Remove from player map if exists
            foreach ($this->playerMap as $playerId => $cId) {
                if ($cId === $clientId) {
                    unset($this->playerMap[$playerId]);
                    $this->log("Player {$playerId} disconnected\n");
                    break;
                }
            }

            // Remove from quest map if exists
            foreach ($this->questMap as $questId => $cId) {
                if ($cId === $clientId) {
                    unset($this->questMap[$questId]);
                    $this->log("Quest {$questId} disconnected\n");
                    break;
                }
            }
        }
    }

    /**
     * Get client ID from connection
     *
     * @param ConnectionInterface $conn
     * @return string|null
     */
    public function getClientIdFromConnection($conn) {
        foreach ($this->clients as $clientId => $connection) {
            if ($connection === $conn) {
                return $clientId;
            }
        }

        return null;
    }

    /**
     * Handle a message from a client
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $msg
     */
    public function handleMessage($from, $clientId, $msg) {
        $this->log("Processing message from {$clientId}: {$msg}\n");

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
    }

    /**
     * Handle a JSON message
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleJsonMessage($from, $clientId, $data) {
        match (true) {
            isset($data['type']) => $this->handleTypedMessage($from, $clientId, $data),
            isset($data['playerId']) => $this->handleRegistration($from, $clientId, $data),
            default => $this->handleGenericJsonMessage($from, $clientId, $data)
        };
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
        $this->log("Unknown message type '{$type}' from {$clientId}\n", 'warning');
        $this->sendEcho($from, $data);
    }

    /**
     * Handle player registration
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param array $data
     */
    private function handleRegistration($from, $clientId, $data) {
        $playerId = $data['playerId'] ?? null;

        if (!$playerId) {
            $this->sendError($from, 'Missing playerId in registration');
            return;
        }

        $this->playerMap[$playerId] = $clientId;
        $this->log("Registered player {$playerId} with client {$clientId}\n");

        // If quest ID is also provided, register it
        if (isset($data['questId'])) {
            $questId = $data['questId'];
            $this->questMap[$questId] = $clientId;
            $this->log("Registered quest {$questId} with client {$clientId}\n");
        }

        // Send acknowledgment
        $from->send(json_encode([
            'type' => 'connected',
            'playerId' => $playerId,
            'timestamp' => time()
        ]));

        $this->log("Sent connection acknowledgment to {$clientId}\n");
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
        $this->log("Error processing message from {$clientId}: " . $e->getMessage() . "\n", 'error');

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
        if (isset($this->playerMap[$playerId])) {
            $clientId = $this->playerMap[$playerId];

            if (isset($this->clients[$clientId])) {
                $this->clients[$clientId]->send(json_encode($data));
                $this->log("Sent message to player {$playerId}\n");
                return true;
            }
        }

        $this->log("Player {$playerId} not found or not connected\n", 'warning');
        return false;
    }

    /**
     * Send a message to a specific quest
     *
     * @param string $questId
     * @param array $data
     * @return bool
     */
    public function sendToQuest($questId, $data) {
        if (isset($this->questMap[$questId])) {
            $clientId = $this->questMap[$questId];

            if (isset($this->clients[$clientId])) {
                $this->clients[$clientId]->send(json_encode($data));
                $this->log("Sent message to quest {$questId}\n");
                return true;
            }
        }

        $this->log("Quest {$questId} not found or not connected\n", 'warning');
        return false;
    }

    /**
     * Broadcast a message to all connected clients
     *
     * @param array $data
     */
    public function broadcast($data) {
        $this->log("Broadcasting " . var_dump($data) . "\n");
        $json = json_encode($data);

        foreach ($this->clients as $client) {
            $client->send($json);
        }

        $this->log("Broadcast message to " . count($this->clients) . " clients");
    }

    /**
     * Broadcast a message to all clients in a quest
     *
     * @param int $questId Quest ID
     * @param array $message Message to broadcast
     */
    public function broadcastToQuest(int $questId, array $message): void {
        $this->log("Broadcasting message " . var_dump($message) . " to quest {$questId}\n");
        if (!isset($this->questMap[$questId])) {
            $this->log("--> Quest {$questId} is unknown or disconnected\n", 'warning');
            return;
        }

        $encodedMessage = json_encode($message);

        foreach ($this->questMap[$questId] as $clientId) {
            if (isset($this->clients[$clientId])) {
                $this->clients[$clientId]->send($encodedMessage);
            }
        }

        $this->log("Broadcast message to " . count($this->questMap[$questId]) . " clients");
    }

    /**
     * Shutdown the server gracefully
     */
    public function shutdown() {
        $this->log("Shutting down WebSocket server...\n");

        // Close all client connections
        foreach ($this->clients as $client) {
            $client->close();
        }

        // Clear client maps
        $this->clients = [];
        $this->playerMap = [];
        $this->questMap = [];

        // Stop the event loop
        Loop::get()->stop();
    }
}
