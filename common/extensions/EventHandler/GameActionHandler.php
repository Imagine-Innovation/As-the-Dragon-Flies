<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\components\LoggerService; // Assuming LoggerService is in common\components
use common\components\RuleEngineService; // Assuming RuleEngineService is in common\components
use Ratchet\ConnectionInterface;
use common\models\Player; // Assuming Player model path
use common\models\Quest;   // Assuming Quest model path
// Potentially other models like Item, Character, etc. depending on 'action_type'

class GameActionHandler implements SpecificMessageHandlerInterface {

    private LoggerService $logger;
    private BroadcastServiceInterface $broadcastService;
    private BroadcastMessageFactory $messageFactory;
    private RuleEngineService $ruleEngineService;

    public function __construct(
        LoggerService $logger,
        BroadcastServiceInterface $broadcastService,
        BroadcastMessageFactory $messageFactory,
        RuleEngineService $ruleEngineService // Added RuleEngineService
    ) {
        $this->logger = $logger;
        $this->broadcastService = $broadcastService;
        $this->messageFactory = $messageFactory;
        $this->ruleEngineService = $ruleEngineService; // Store injected RuleEngineService
    }

    /**
     * Handles game action messages.
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("GameActionHandler: handle from clientId={$clientId}, sessionId={$sessionId}", $data);

        // Initial data validation (kept from original)
        if (!isset($data['action_type'], $data['details'], $data['quest_id'])) {
            $this->logger->log("GameActionHandler: Missing required data (action_type, details, quest_id).", $data, LoggerService::LEVEL_WARNING);
            $errorDto = $this->messageFactory->createErrorMessage("Invalid game action data provided.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("GameActionHandler: handle");
            return;
        }

        $actionType = (string)$data['action_type'];
        $questId = (int)$data['quest_id'];
        $details = (array)$data['details'];

        // Fetch relevant Yii models
        $player = Player::findOne(['client_id' => $clientId]); // Assuming 'client_id' is the field in Player table
        $quest = Quest::findOne($questId);

        if (!$player) {
            $this->logger->log("GameActionHandler: Player not found for clientId={$clientId}.", [], LoggerService::LEVEL_ERROR);
            // Optionally send error message back to client
            $errorDto = $this->messageFactory->createErrorMessage("Player not found.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("GameActionHandler: handle");
            return;
        }
        if (!$quest) {
            $this->logger->log("GameActionHandler: Quest not found for quest_id={$questId}.", [], LoggerService::LEVEL_ERROR);
            // Optionally send error message back to client
            $errorDto = $this->messageFactory->createErrorMessage("Quest not found.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("GameActionHandler: handle");
            return;
        }
        
        // Add more model fetching here based on $actionType or $details if needed
        // e.g., if $actionType is 'use_item', $details might contain 'item_id'
        // $item = ($actionType === 'use_item' && isset($details['item_id'])) ? Item::findOne($details['item_id']) : null;

        // Construct the $context array
        $context = [
            'player' => $player,
            'quest' => $quest,
            // 'item' => $item, // if item was fetched
            'eventData' => $data, // Include the original event data
            'clientId' => $clientId,
            'sessionId' => $sessionId,
        ];

        // Determine the $triggerName
        $triggerName = $actionType; // e.g., "player_move", "item_use", "dialogue_choice"

        $this->logger->log("GameActionHandler: Processing rules for trigger '{$triggerName}'.",
            ['quest_id' => $questId, 'player_id' => $player->id, 'action_type' => $actionType]
        );

        // Call the RuleEngineService
        $ruleOutcomes = [];
        try {
            $ruleOutcomes = $this->ruleEngineService->processTrigger($triggerName, $context);
            $this->logger->log("GameActionHandler: Rule engine processing completed for trigger '{$triggerName}'.", ['outcome_count' => count($ruleOutcomes)]);
        } catch (\Exception $e) {
            $this->logger->log("GameActionHandler: Error during rule engine processing for trigger '{$triggerName}': " . $e->getMessage(),
                ['trace' => $e->getTraceAsString()], LoggerService::LEVEL_ERROR
            );
            $errorDto = $this->messageFactory->createErrorMessage("An internal error occurred while processing your action.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            // Do not proceed further if rule engine itself failed catastrophically.
            $this->logger->logEnd("GameActionHandler: handle (terminated due to rule engine error)");
            return;
        }

        // Process and broadcast outcomes from the RuleEngineService
        if (!empty($ruleOutcomes)) {
            foreach ($ruleOutcomes as $outcome) {
                if (!is_array($outcome) || !isset($outcome['status'], $outcome['broadcast_type'], $outcome['data'])) {
                    $this->logger->log("GameActionHandler: Invalid outcome structure received from RuleEngine.", ['outcome' => $outcome], LoggerService::LEVEL_WARNING);
                    continue;
                }

                if ($outcome['status'] !== 'success') {
                    $this->logger->log("GameActionHandler: Rule action resulted in non-success status.", ['outcome' => $outcome], LoggerService::LEVEL_INFO);
                    // Optionally, handle non-success outcomes differently, e.g., send specific error to client
                    // For now, we only broadcast success outcomes that have a broadcast type and scope.
                    // Or, we could have a specific 'failure_outcome' broadcast_type.
                    // Let's assume for now only 'success' outcomes with valid scopes are broadcasted.
                    // If a different message needs to be sent for failure, the action should return that as a specific outcome.
                    // e.g. status: 'success', broadcast_type: 'ACTION_FAILED_NOTIFICATION', data: {reason: ...}
                }

                $dto = $this->messageFactory->createRuleOutcomeMessage(
                    (string)$outcome['broadcast_type'],
                    (array)$outcome['data'],
                    isset($outcome['message_key']) ? (string)$outcome['message_key'] : null
                );

                $scope = $outcome['broadcast_scope'] ?? 'quest'; // Default to 'quest'
                $targetId = $outcome['broadcast_target_id'] ?? null; // For player-specific scopes

                $this->logger->log("GameActionHandler: Broadcasting rule outcome.", ['type' => $dto['type'], 'scope' => $scope, 'target_id' => $targetId]);

                switch ($scope) {
                    case 'player':
                        if ($targetId) { // Target ID should be the client_id for 'player' scope
                            $this->broadcastService->sendToClient($targetId, $dto);
                        } else {
                             $this->logger->log("GameActionHandler: Missing target_id for 'player' scope broadcast.", ['outcome' => $outcome], LoggerService::LEVEL_WARNING);
                             // Fallback to sending to the originating client if no target_id and it's a player-specific message often meant for them
                             $this->broadcastService->sendToClient($clientId, $dto);
                        }
                        break;
                    case 'session': // Send back to the originating connection
                        $this->broadcastService->sendBack($from, $dto['type'], $dto['payload']); // sendBack might take type & payload separately
                        break;
                    case 'quest':
                        $this->broadcastService->broadcastToQuest($questId, $dto, $sessionId); // Exclude sender
                        break;
                    case 'all_in_quest_inclusive': // Example of a new scope
                        $this->broadcastService->broadcastToQuest($questId, $dto, null); // Include sender
                        break;
                    case 'all': // Broadcast to everyone connected to the server
                        $this->broadcastService->broadcast($dto, $sessionId); // Exclude sender by default
                        break;
                    case 'none':
                        // Do nothing, action is internal or outcome is not for broadcast
                        $this->logger->log("GameActionHandler: 'none' broadcast scope for outcome.", ['type' => $dto['type']]);
                        break;
                    default:
                        $this->logger->log("GameActionHandler: Unknown broadcast scope '{$scope}'. Defaulting to quest broadcast.", ['outcome' => $outcome], LoggerService::LEVEL_WARNING);
                        $this->broadcastService->broadcastToQuest($questId, $dto, $sessionId); // Exclude sender
                        break;
                }
            }
        }

        // Existing DTO broadcast (can remain for now, or be removed if outcomes cover all scenarios)
        // For now, let's comment it out to avoid duplicate/conflicting messages if rule actions are comprehensive.
        /*
        $gameActionDto = $this->messageFactory->createGameActionMessage($actionType, $details);
        $this->broadcastService->broadcastToQuest($questId, $gameActionDto, $sessionId);
        $this->logger->log("GameActionHandler: GameActionDto broadcasted (original logic)", ['quest_id' => $questId, 'action_type' => $actionType]);
        */

        // Send an acknowledgement back to the sender client (original logic)
        // This ack can also become a specific rule outcome if desired.
        $this->broadcastService->sendBack($from, 'action_ack', ['status' => 'success', 'action_type' => $actionType, 'processed_by_rules' => !empty($ruleOutcomes)]);
        $this->logger->logEnd("GameActionHandler: handle");
    }
}
