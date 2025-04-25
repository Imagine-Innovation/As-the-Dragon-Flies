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
        $clientId = uniqid('client_');
        $this->clients[$conn->resourceId] = $clientId;
        $this->eventHandler->addClient($conn, $clientId);

        $this->eventHandler->log("New connection! ({$conn->remoteAddress}) assigned ID: {$clientId}\n");
    }

    /**
     * When a message is received
     *
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $clientId = $this->clients[$from->resourceId] ?? null;

        if ($clientId) {
            $this->eventHandler->log("Received message from client {$clientId}: {$msg}\n");
            $this->eventHandler->handleMessage($from, $clientId, $msg);
        } else {
            $this->eventHandler->log("Received message from unknown client: {$msg}\n");
        }
    }

    /**
     * When a connection is closed
     *
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn) {
        $clientId = $this->clients[$conn->resourceId] ?? null;

        if ($clientId) {
            $this->eventHandler->removeClient($clientId);
            unset($this->clients[$conn->resourceId]);
            $this->eventHandler->log("Connection {$clientId} has disconnected\n");
        } else {
            $this->eventHandler->log("Unknown connection has disconnected\n");
        }
    }

    /**
     * When an error occurs
     *
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $clientId = $this->clients[$conn->resourceId] ?? 'unknown';
        $this->eventHandler->log("An error occurred with client {$clientId}: {$e->getMessage()}\n");

        $conn->close();
    }
}
