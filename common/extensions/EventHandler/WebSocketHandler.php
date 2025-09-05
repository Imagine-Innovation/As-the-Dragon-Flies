<?php

namespace common\extensions\EventHandler;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use common\extensions\EventHandler\contracts\MessageHandlerInterface;
use common\extensions\EventHandler\LoggerService;
use common\extensions\EventHandler\WebSocketServerManager;

class WebSocketHandler implements MessageComponentInterface
{

    private MessageHandlerInterface $messageHandler;
    private LoggerService $logger;
    private WebSocketServerManager $serverManager; // To call addClient/removeClient on the manager
    private array $clientsMap = []; // Maps $conn->resourceId to our generated $clientId

    public function __construct(
            MessageHandlerInterface $messageHandler,
            LoggerService $logger,
            WebSocketServerManager $serverManager
    ) {
        $this->messageHandler = $messageHandler;
        $this->logger = $logger;
        $this->serverManager = $serverManager;
        $this->logger->log("WebSocketHandler: Initialized");
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->logger->logStart("WebSocketHandler: onOpen from {$conn->remoteAddress}");

        $clientId = uniqid('client_'); // Generate a unique client ID
        $conn->appClientId = $clientId; // Attach our clientId to the connection object
        $this->clientsMap[$conn->resourceId] = $clientId; // Map Ratchet's resourceId to our clientId
        // Notify WebSocketServerManager to add this client to its list
        $this->serverManager->addClient($conn, $clientId);

        // Notify the MessageHandler (Orchestrator) about the new connection
        $this->messageHandler->open($conn, $clientId);

        $this->logger->log("WebSocketHandler: New connection! ({$conn->remoteAddress}) assigned appClientId: {$clientId}, resourceId: {$conn->resourceId}", $this->clientsMap);
        $this->logger->logEnd("WebSocketHandler: onOpen");
    }

    private function getClientId(ConnectionInterface $conn): ?string {
        // Retrieve our clientId
        if (isset($conn->appClientId)) {
            return $conn->appClientId;
        }
        if (isset($this->clientsMap[$conn->resourceId])) {
            $this->logger->log("WebSocketHandler: getClientId - Rely on clientsMap");
            return $this->clientsMap[$conn->resourceId];
        }
        return null;
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $clientId = $this->getClientId($from);
        if (!$clientId) {
            $this->logger->log("WebSocketHandler: Error - Could not determine client ID for message from resourceId: {$from->resourceId}", ['message' => $msg], 'error');
            $from->close(); // Close connection if client ID is unknown
            return;
        }

        $this->logger->logStart("WebSocketHandler: onMessage from clientId=[{$clientId}], resourceId=[{$from->resourceId}]", ['message_summary' => substr($msg, 0, 100) . (strlen($msg) > 100 ? '...' : '')]);

        // Delegate message handling to the MessageHandler (Orchestrator)
        $this->messageHandler->handle($from, $clientId, $msg);

        $this->logger->logEnd("WebSocketHandler: onMessage from clientId=[{$clientId}]");
    }

    public function onClose(ConnectionInterface $conn) {
        $clientId = $this->getClientId($conn);
        if (!$clientId) {
            // This might happen if onError closed the connection before appClientId was set or if it was never opened properly.
            $this->logger->log("WebSocketHandler: onClose - Could not determine client ID for resourceId: {$conn->resourceId}", null, 'warning');
            if (isset($this->clientsMap[$conn->resourceId])) {
                unset($this->clientsMap[$conn->resourceId]);
            }
            return;
        }

        $this->logger->logStart("WebSocketHandler: onClose for clientId=[{$clientId}], resourceId=[{$conn->resourceId}]");

        // Notify the MessageHandler (Orchestrator) about the connection closing
        $this->messageHandler->close($clientId);

        // Notify WebSocketServerManager to remove this client from its list
        // WebSocketServerManager's removeClient will also trigger QuestSessionManager->clearClientId
        $this->serverManager->removeClient($clientId);

        // Remove from local map
        if (isset($this->clientsMap[$conn->resourceId])) {
            unset($this->clientsMap[$conn->resourceId]);
        }
        if (isset($conn->appClientId)) { // Clean up our attached property
            unset($conn->appClientId);
        }

        $this->logger->log("WebSocketHandler: Connection clientId=[{$clientId}] has disconnected.", $this->clientsMap);
        $this->logger->logEnd("WebSocketHandler: onClose");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $clientId = $this->getClientId($conn);
        if (!$clientId) {
            $this->logger->log("WebSocketHandler: onError - Could not determine client ID for resourceId: {$conn->resourceId}. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
            // Ratchet usually calls onClose after onError, so cleanup might happen there.
            // If not, we might need to manually trigger cleanup.
            $conn->close(); // Ensure connection is closed.
            return;
        }

        $this->logger->logStart("WebSocketHandler: onError for clientId=[{$clientId}], resourceId=[{$conn->resourceId}]", ['error' => $e->getMessage()]);

        // Notify the MessageHandler (Orchestrator) about the error
        $this->messageHandler->error($conn, $clientId, $e);

        $this->logger->log("WebSocketHandler: An error occurred with client clientId=[{$clientId}]: " . $e->getMessage(), $e->getTraceAsString(), 'error');

        // Ratchet's documentation implies onClose will be called automatically by the server
        // when a connection drops or an error handler closes the connection.
        // Explicitly calling $conn->close() here ensures it's closed if the error isn't fatal enough for Ratchet to auto-close.
        if ($conn->getSocket()) { // Check if connection is still open before trying to close
            $conn->close();
        }

        $this->logger->logEnd("WebSocketHandler: onError for clientId=[{$clientId}]");
    }
}
