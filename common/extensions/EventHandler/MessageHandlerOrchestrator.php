<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\MessageHandlerInterface; // Updated
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface; // Updated
use Ratchet\ConnectionInterface;

// Assuming LoggerService and BroadcastService will be in this namespace or properly aliased
// use common\extensions\EventHandler\LoggerService;
// use common\extensions\EventHandler\BroadcastService;

class MessageHandlerOrchestrator implements MessageHandlerInterface {

    private LoggerService $logger;
    private BroadcastService $broadcastService; // Updated
    private array $specificHandlers;

    public function __construct(
            LoggerService $logger,
            BroadcastService $broadcastService,
            array $specificHandlers = [] // Added
    ) {
        $this->logger = $logger;
        $this->specificHandlers = $specificHandlers;
        $this->broadcastService = $broadcastService;
    }

    public function handle(ConnectionInterface $conn, string $clientId, string $message): void {
        $this->logger->logStart("Orchestrator: handle message from clientId=[{$clientId}]", $message);
        try {
            $data = json_decode($message, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->logger->log("Orchestrator: Message is JSON. Processing...", $data);
                $this->handleJsonMessage($conn, $clientId, $data);
            } else {
                $this->logger->log("Orchestrator: Message is not valid JSON. Treating as plain text.");
                $this->handleTextMessage($conn, $clientId, $message);
            }
        } catch (\Exception $e) {
            $this->logger->log("Orchestrator: Exception during message handling for clientId=[{$clientId}]", $e->getMessage(), 'error');
            $this->error($conn, $clientId, $e);
        }
        $this->logger->logEnd("Orchestrator: handle message from clientId=[{$clientId}]");
    }

    public function open(ConnectionInterface $conn, string $clientId): void {
        $this->logger->log("Orchestrator: New connection opened for clientId=[{$clientId}]", ['remoteAddress' => $conn->remoteAddress]);
        // Future on-open logic here
    }

    public function close(string $clientId): void {
        $this->logger->log("Orchestrator: Connection closed for clientId=[{$clientId}]");
        // Future on-close logic here
    }

    public function error(ConnectionInterface $conn, string $clientId, \Exception $e): void {
        $this->logger->log("Orchestrator: Error for clientId=[{$clientId}]: " . $e->getMessage(), [
            'exception_class' => get_class($e),
            'trace' => $e->getTraceAsString()
                ], 'error');
        // Send error back to client
        $this->broadcastService->sendBack($conn, 'error', "An internal error occurred: " . $e->getMessage());
    }

    private function handleTypedMessage(string $type, ConnectionInterface $conn, string $clientId, string $sessionId, array $data): void {
        if (
                isset($this->specificHandlers[$type]) &&
                $this->specificHandlers[$type] instanceof SpecificMessageHandlerInterface
        ) {
            $this->logger->log("Orchestrator: Delegating to handler for type '{$type}' for client {$clientId}");
            $this->specificHandlers[$type]->handle($conn, $clientId, $sessionId, $data);
        } else {
            $this->logger->log("Orchestrator: No specific handler for type '{$type}'. Calling handleUnknownType for client {$clientId}");
            $this->handleUnknownType($conn, $clientId, $sessionId, $type ?? 'implicit registration', $data);
        }
    }

    /**
     * Handle a JSON message
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     * @param array $data
     */
    private function handleJsonMessage(ConnectionInterface $conn, string $clientId, array $data): void {
        $this->logger->logStart("Orchestrator: handleJsonMessage for clientId=[{$clientId}]", $data);

        $sessionId = $data['sessionId'] ?? null; // Used by some original handlers, kept for context
        if (!$sessionId) {
            $this->logger->log("Orchestrator: Missing SessionId in JSON message from clientId=[{$clientId}]", $data, 'warning');
            $this->broadcastService->sendBack($conn, 'error', 'Missing SessionId in source message');
            $this->logger->logEnd("Orchestrator: handleJsonMessage for clientId=[{$clientId}]");
            return;
        }

        $type = $data['type'] ?? null;

        if ($type || isset($data['playerId'])) {
            $handlerKey = $type ?? 'register';
            $this->handleTypedMessage($handlerKey, $conn, $clientId, $sessionId, $data);
        } else {
            // No type and no playerId (already handled by registration logic) -> generic JSON
            $this->logger->log("Orchestrator: Message has no 'type' or specific registration trigger. Calling handleGenericJsonMessage for clientId={$clientId}");
            $this->handleGenericJsonMessage($conn, $clientId, $sessionId, $data);
        }
        $this->logger->logEnd("Orchestrator: handleJsonMessage for clientId=[{$clientId}]");
    }

    /**
     * Handle a generic JSON message (no specific type or registration identified)
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     * @param string $sessionId
     * @param array $data
     */
    private function handleGenericJsonMessage(ConnectionInterface $conn, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("Orchestrator: handleGenericJsonMessage for clientId=[{$clientId}], sessionId=[{$sessionId}]", $data);

        // Use BroadcastService to send echo back
        $this->broadcastService->sendBack($conn, 'echo', $data);
        $this->logger->logEnd("Orchestrator: handleGenericJsonMessage for clientId=[{$clientId}]");
    }

    /**
     * Handle a plain text message
     *
     * @param ConnectionInterface $conn
     * @param string $clientId
     * @param string $message
     */
    private function handleTextMessage(ConnectionInterface $conn, string $clientId, string $message): void {
        $this->logger->logStart("Orchestrator: handleTextMessage for clientId=[{$clientId}]", ['message' => $message]);

        // Use BroadcastService to send echo back
        $this->broadcastService->sendBack($conn, 'echo', $message);
        $this->logger->logEnd("Orchestrator: handleTextMessage for clientId=[{$clientId}]");
    }

    /**
     * Handle an unknown message type
     * Logic from EventHandler::handleUnknownType
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param string $type
     * @param array $data
     */
    private function handleUnknownType(ConnectionInterface $from, string $clientId, string $sessionId, string $type, array $data): void {
        $this->logger->logStart("Orchestrator: handleUnknownType for clientId=[{$clientId}], sessionId=[{$sessionId}]", ['type' => $type, 'data' => $data]);
        $this->logger->log("Orchestrator: Unknown message type '{$type}' from session [{$sessionId}]", $data, 'warning');

        // Use BroadcastService to send echo for unknown type
        $this->broadcastService->sendBack($from, 'echo', ['message' => "Unknown type: {$type}", 'original_payload' => $data]);
        $this->logger->logEnd("Orchestrator: handleUnknownType for clientId=[{$clientId}]");
    }
}
