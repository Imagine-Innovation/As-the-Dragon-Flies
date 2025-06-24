<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\services\DmService; // Added DmService
use Ratchet\ConnectionInterface;
use Yii; // For DI or service locator if needed

class GameActionHandler implements SpecificMessageHandlerInterface {

    private LoggerService $logger;
    private BroadcastServiceInterface $broadcastService;
    private BroadcastMessageFactory $messageFactory;
    private DmService $dmService; // Added DmService instance

    public function __construct(
        LoggerService $logger,
        BroadcastServiceInterface $broadcastService,
        BroadcastMessageFactory $messageFactory,
        DmService $dmService // Injected DmService
    ) {
        $this->logger = $logger;
        $this->broadcastService = $broadcastService;
        $this->messageFactory = $messageFactory;
        $this->dmService = $dmService;
    }

    /**
     * Handles game action messages, now with DM logic.
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("GameActionHandler: handle from clientId={$clientId}, sessionId={$sessionId}", $data);

        if (!isset($data['action_type'], $data['details'], $data['quest_id'], $data['player_id'])) {
            $this->logger->log("GameActionHandler: Missing required data (action_type, details, quest_id, player_id).", $data, 'warning');
            // Send error back to the specific client
            $errorDto = $this->messageFactory->createErrorMessage("Invalid game action data provided. Ensure action_type, details, quest_id, and player_id are present.");
            // $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId); // Original way
            $from->send(json_encode($errorDto)); // Send directly to connection
            $this->logger->logEnd("GameActionHandler: handle due to missing data");
            return;
        }

        $questId = (int)$data['quest_id'];
        $playerId = (int)$data['player_id']; // Assuming player_id is sent in data
        $actionData = [
            'type' => (string)$data['action_type'],
            'details' => (array)$data['details']
            // Pass other relevant parts of $data['details'] if necessary
        ];

        // 1. Process the action and manage round state using DmService
        $result = $this->dmService->processActionAndManageRound($questId, $playerId, $actionData);
        $this->logger->log("GameActionHandler: DmService::processActionAndManageRound result", $result);

        // Error handling from DmService
        if (isset($result['error']) && $result['error'] !== null) {
            $this->logger->log("GameActionHandler: Error from DmService: " . $result['error'], $data, 'error');
            $errorDto = $this->messageFactory->createErrorMessage($result['error']);
            $from->send(json_encode($errorDto)); // Send error back to the acting client
            $this->logger->logEnd("GameActionHandler: handle due to DmService error");
            return;
        }

        // 2. Broadcast the action outcome (DM narrative)
        if (isset($result['action_outcome_text'])) {
            $outcomeDto = $this->messageFactory->createGenericMessage(
                'dm_narrative',
                [
                    'text' => $result['action_outcome_text'],
                    'is_successful' => $result['action_is_successful'] ?? false,
                    'action_type' => $actionData['type'], // Echo back the action type
                    'player_id' => $playerId // Identify who performed the action
                ]
            );
            $this->broadcastService->broadcastToQuest($questId, $outcomeDto);
            $this->logger->log("GameActionHandler: DM narrative for player $playerId action broadcasted to quest $questId");
        }
        
        // TODO: Apply $result['state_changes'] if any (e.g. from evaluatePlayerAction) - this key isn't currently populated by DmService

        // 3. If a new round just started, broadcast its starting description
        if ($result['new_round_just_started'] && !empty($result['new_round_description'])) {
            $roundStartDto = $this->messageFactory->createGenericMessage(
                'round_start_description',
                [
                    'text' => $result['new_round_description'],
                    'round_number' => $result['round_number'] ?? null
                ]
            );
            $this->broadcastService->broadcastToQuest($questId, $roundStartDto);
            $this->logger->log("GameActionHandler: New round start description broadcasted for round {$result['round_number']}");
        }

        // 4. If the current round was just completed, optionally notify
        if ($result['current_round_just_completed']) {
            $roundEndText = "Round {$result['round_number']} has ended.";
             // Potentially add more details if needed, e.g., who acted.
            $roundEndDto = $this->messageFactory->createGenericMessage(
                'dm_narrative', // Can use dm_narrative or a specific 'round_end' type
                ['text' => $roundEndText, 'type_detail' => 'round_completed']
            );
            $this->broadcastService->broadcastToQuest($questId, $roundEndDto);
            $this->logger->log("GameActionHandler: Round {$result['round_number']} completion notice broadcasted.");
        }

        // 5. Send updated scene description to the acting player
        // This reflects the state of the world *after* their action.
        if (isset($result['scene_description_for_acting_player'])) {
            $sceneDto = $this->messageFactory->createGenericMessage(
                'scene_description',
                ['text' => $result['scene_description_for_acting_player'], 'is_acting_player' => true]
            );
            // This should go ONLY to the acting player ($from connection)
            $from->send(json_encode($sceneDto));
            $this->logger->log("GameActionHandler: Scene description sent back to acting player $playerId.", ['description' => $result['scene_description_for_acting_player']]);
        }
        // $this->broadcastService->sendBack($from, 'action_processed', ['status' => 'success', 'action_type' => $actionData['type']]);
        // The main DM narrative should serve as ack.

        $this->logger->logEnd("GameActionHandler: handle");
    }
}
