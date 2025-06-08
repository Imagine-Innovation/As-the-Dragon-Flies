<?php

namespace common\extensions\EventHandler;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;
use common\extensions\EventHandler\MessageHandlerInterface;
use common\extensions\EventHandler\LoggerService; // Added use statement
use common\extensions\EventHandler\QuestSessionManager; // Added use statement
use Ratchet\ConnectionInterface; // Added because it's used in addClient

class WebSocketServerManager {
    private array $clients = []; // Changed from static
    // private MessageHandlerInterface $messageHandler; // Removed as property
    private LoggerService $logger; // Property for LoggerService
    private QuestSessionManager $questSessionManager; // Property for QuestSessionManager
    private LoopInterface $loop;
    private ?SocketServer $socket = null;
    private ?IoServer $server = null;

    // Constructor updated
    public function __construct(
        LoggerService $logger,
        LoopInterface $loop,
        QuestSessionManager $questSessionManager
    ) {
        // $this->messageHandler = $messageHandler; // Removed
        $this->logger = $logger;
        $this->loop = $loop;
        $this->questSessionManager = $questSessionManager;
    }

    // addClient now needs MessageHandlerInterface passed in, or WebSocketHandler needs direct access to it
    // For now, this change assumes WebSocketHandler will be given the orchestrator directly.
    // So addClient and removeClient in WebSocketServerManager itself might not need to call messageHandler.open/close
    // if WebSocketHandler (Ratchet's MessageComponentInterface adapter) does it.
    // Let's assume WebSocketHandler handles the open/close/error calls to the MessageHandlerOrchestrator for now.

    public function addClient(ConnectionInterface $conn, string $clientId): void {
        $this->logger->log("Adding client: {$clientId}");
        $this->clients[$clientId] = $conn;
        // $messageHandler->open($conn, $clientId); // This call is likely done by WebSocketHandler
    }

    public function removeClient(string $clientId): void {
        $this->logger->log("Removing client: {$clientId}");
        if (isset($this->clients[$clientId])) {
            unset($this->clients[$clientId]);
            // $messageHandler->close($clientId); // This call is likely done by WebSocketHandler
        }
        $this->questSessionManager->clearClientId($clientId);
    }

    // run method signature changed
    public function run(MessageHandlerInterface $messageHandler, string $host, int $port): void {
        // $this->logger->logStart("Starting WebSocket server on {$host}:{$port}"); // Logging moved to EventHandler

        // Create your WebSocket handler
        // Note: WebSocketHandler class needs to be adjusted to use the passed $messageHandler
        // and $this (WebSocketServerManager instance for addClient/removeClient calls from WebSocketHandler)
        $webSocketHandler = new WebSocketHandler($messageHandler, $this->logger, $this);

        // Create a Ratchet WsServer
        $wsServer = new WsServer($webSocketHandler);

        // Create an HTTP server to handle the WebSocket handshake
        $httpServer = new HttpServer($wsServer);

        // Create the socket server
        $this->socket = new SocketServer("{$host}:{$port}", [], $this->loop);

        // Create the IO server
        $this->server = new IoServer($httpServer, $this->socket, $this->loop);

        $this->logger->log("WebSocket server running at {$host}:{$port}"); // Use LoggerService
        $this->loop->run();
        // logEnd for server start will be in shutdown or if run completes/exits.
    }

    public function shutdown(): void {
        $this->logger->logStart("Shutting down WebSocket server..."); // Use LoggerService

        // Close all client connections
        foreach ($this->clients as $clientId => $client) { // Use $this->clients
            if ($client instanceof ConnectionInterface) { // Check if $client is a ConnectionInterface
                $client->close();
            }
            // Optionally, inform the message handler about the closure if not already handled by removeClient
            // $this->messageHandler->close($clientId); 
        }
        $this->clients = []; // Use $this->clients

        if ($this->socket !== null) {
            $this->socket->close();
        }
        
        // IoServer typically doesn't have a 'stop' method itself.
        // Stopping the loop is the standard way to halt the server.
        // if ($this->server !== null && method_exists($this->server, 'stop')) {
        // $this->server->stop(); 
        // }

        $this->loop->stop();
        $this->logger->logEnd("WebSocket server shut down."); // Use LoggerService
    }

    public function getClient(string $clientId): ?ConnectionInterface {
        return $this->clients[$clientId] ?? null;
    }

    public function getAllClients(): array {
        return $this->clients;
    }
}
