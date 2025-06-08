<?php

namespace common\extensions\EventHandler;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketHandler implements MessageComponentInterface {

    /**
     * @var EventHandler
     */
    protected $eventHandler;

    /**
     * @var array Map of connections to client IDs
     */
    protected $clients = [];

    /**
     * Constructor
     *
     * @param EventHandler $eventHandler
     */
    public function __construct(EventHandler $eventHandler) {
        $this->eventHandler = $eventHandler;
    }

    /**
     * When a new connection is opened
     *
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->eventHandler->logStart("WebSocketHandler onOpen");
        $clientId = uniqid('client_');

        // Store the client ID on the connection object for easy reference
        $conn->clientId = $clientId;

        // Map the connection resource ID to the client ID
        $this->clients[$conn->resourceId] = $clientId;

        // Add the client to the event handler
        $this->eventHandler->addClient($conn, $clientId);

        $this->eventHandler->log("New connection! ({$conn->remoteAddress}) assigned ID: {$clientId}", $this->clients);
        $this->eventHandler->logEnd("WebSocketHandler onOpen");
    }

    /**
     * When a message is received
     *
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $this->eventHandler->logStart("WebSocketHandler onMessage", $msg);
        $clientId = $from->clientId ?? $this->clients[$from->resourceId] ?? null;

        if ($clientId) {
            $this->eventHandler->log("Received message from client {$clientId}: {$msg}");
            $this->eventHandler->handleMessage($from, $clientId, $msg);
        } else {
            $this->eventHandler->log("Received message from unknown client: {$msg}", $from, 'warning');
        }
        $this->eventHandler->logEnd("WebSocketHandler onMessage");
    }

    /**
     * When a connection is closed
     *
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn) {
        $this->eventHandler->logStart("WebSocketHandler onClose");
        $clientId = $conn->clientId ?? $this->clients[$conn->resourceId] ?? null;

        if ($clientId) {
            $this->eventHandler->removeClient($clientId);
            unset($this->clients[$conn->resourceId]);
            $this->eventHandler->log("Connection {$clientId} has disconnected", $this->clients);
        } else {
            $this->eventHandler->log("Unknown connection has disconnected", $this->clients, 'warning');
        }
        $this->eventHandler->logEnd("WebSocketHandler onClose");
    }

    /**
     * When an error occurs
     *
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->eventHandler->logStart("WebSocketHandler onError");
        $clientId = $conn->clientId ?? $this->clients[$conn->resourceId] ?? 'unknown';
        $this->eventHandler->log("An error occurred with client {$clientId}", $e->getMessage(), 'error');

        $conn->close();
        $this->eventHandler->logEnd("WebSocketHandler onError");
    }
}
