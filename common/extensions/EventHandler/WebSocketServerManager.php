<?php

namespace common\extensions\EventHandler;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;
use common\extensions\EventHandler\contracts\MessageHandlerInterface;
use common\extensions\EventHandler\LoggerService;
use common\extensions\EventHandler\QuestSessionManager;
use common\extensions\EventHandler\WebSocketHandler;
use Ratchet\ConnectionInterface;

class WebSocketServerManager
{

    /** @var array<int, ConnectionInterface> $clients */
    private array $clients = [];
    private LoggerService $logger;
    private QuestSessionManager $questSessionManager;
    private LoopInterface $loop;
    private ?SocketServer $socket = null;
    private IoServer $IOServer; // @phpstan-ignore-line

    /**
     *
     * @param LoggerService $logger
     * @param LoopInterface $loop
     * @param QuestSessionManager $questSessionManager
     */
    public function __construct(
            LoggerService $logger,
            LoopInterface $loop,
            QuestSessionManager $questSessionManager
    ) {
        $this->logger = $logger;
        $this->loop = $loop;
        $this->questSessionManager = $questSessionManager;
    }

    /**
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     * @return void
     */
    public function addClient(ConnectionInterface $conn, string $clientId): void {
        $this->logger->log("Adding client: {$clientId}");
        $this->clients[$clientId] = $conn;
    }

    /**
     *
     * @param string $clientId
     * @return void
     */
    public function removeClient(string $clientId): void {
        $this->logger->log("Removing client: {$clientId}");
        if (isset($this->clients[$clientId])) {
            unset($this->clients[$clientId]);
        }
        $this->questSessionManager->clearClientId($clientId);
    }

    /**
     *
     * @param MessageHandlerInterface $messageHandler
     * @param string $host
     * @param int $port
     * @return void
     * @throws \Throwable
     */
    public function setup(MessageHandlerInterface $messageHandler, string $host, int $port): void {
        $webSocketHandler = new WebSocketHandler($messageHandler, $this->logger, $this);
        $wsServer = new WsServer($webSocketHandler);
        $httpServer = new HttpServer($wsServer);

        $this->logger->log("WebSocketServerManager: Attempting to create SocketServer on {$host}:{$port}...");
        try {
            $this->socket = new SocketServer("{$host}:{$port}", [], $this->loop);
            $this->logger->log("WebSocketServerManager: SocketServer created successfully.");
        } catch (\Throwable $e) {
            $this->logger->log("WebSocketServerManager: Error creating SocketServer: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString(), null, 'error');
            throw $e;
        }

        $this->logger->log("WebSocketServerManager: Attempting to create IoServer...");
        try {
            $this->IOServer = new IoServer($httpServer, $this->socket, $this->loop);
            $this->logger->log("WebSocketServerManager: IoServer created successfully.");
        } catch (\Throwable $e) {
            $this->logger->log("WebSocketServerManager: Error creating IoServer: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString(), null, 'error');
            throw $e;
        }

        $this->logger->log("WebSocketServerManager: WebSocket server setup complete.");
    }

    /**
     *
     * @return void
     */
    public function shutdown(): void {
        $this->logger->logStart("Shutting down WebSocket server..."); // Use LoggerService
        // Close all client connections
        foreach ($this->clients as $clientId => $client) {
            $client->close();
        }
        $this->clients = []; // Use $this->clients

        if ($this->socket !== null) {
            $this->socket->close();
        }

        $this->loop->stop();
        $this->logger->logEnd("WebSocket server shut down."); // Use LoggerService
    }

    /**
     *
     * @param string $clientId
     * @return ConnectionInterface|null
     */
    public function getClient(string $clientId): ?ConnectionInterface {
        return $this->clients[$clientId] ?? null;
    }

    /**
     *
     * @return array<int, ConnectionInterface>
     */
    public function getAllClients(): array {
        return $this->clients;
    }
}
