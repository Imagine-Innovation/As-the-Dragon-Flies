<?php

namespace common\extensions\EventHandler;

use Ratchet\ConnectionInterface;
// use common\extensions\EventHandler\LoggerService;
// use common\extensions\EventHandler\BroadcastService; // Placeholder

class GameActionHandler implements SpecificMessageHandlerInterface {

    private LoggerService $logger;
    private BroadcastService $broadcastService; // Updated

    public function __construct(
        LoggerService $logger,
        BroadcastService $broadcastService // Added
    ) {
        $this->logger = $logger;
        $this->broadcastService = $broadcastService;
    }

    /**
     * Handles game action messages.
     * Original logic from EventHandler::handleGameAction
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("GameActionHandler: handle for sessionId=[{$sessionId}], clientId=[{$clientId}]", $data);

        $action = $data['action'] ?? 'unknown_action';
        $this->logger->log("GameActionHandler: Received game action '{$action}' from session [{$sessionId}]", $data);

        // Use BroadcastService to send echo back
        $this->broadcastService->sendBack($from, 'echo', $data);
        
        // In a real application, you would process the action based on its type and payload.
        // For example:
        // switch ($action) {
        //     case 'move_player':
        //         // ... logic to move player
        //         break;
        //     case 'use_item':
        //         // ... logic to use item
        //         break;
        //     default:
        //         $this->logger->log("GameActionHandler: Unknown action '{$action}'", $data, 'warning');
        //         // $this->broadcastService->sendBack($from, 'error', "Unknown action: {$action}");
        //         $this->logger->log("GameActionHandler: Would send 'error' for unknown action", ['clientId' => $clientId, 'action' => $action]);
        // }

        $this->logger->logEnd("GameActionHandler: handle for sessionId=[{$sessionId}]");
    }
}
