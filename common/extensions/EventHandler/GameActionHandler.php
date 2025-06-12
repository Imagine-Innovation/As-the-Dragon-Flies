<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\components\LoggerService;
use common\components\RuleEngineService;
use common\extensions\EventHandler\dtos\RuleOutcomeDto; // Import the new DTO
use Ratchet\ConnectionInterface;
use common\models\Player;
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
                // Validate the structure of the outcome array from RuleEngineService
                if (!is_array($outcome) ||
                    !isset($outcome['outcomeType'], $outcome['outcomeData'], $outcome['outcomeStatus'], $outcome['broadcastScope'])) {
                    $this->logger->log("GameActionHandler: Invalid or incomplete outcome structure received from RuleEngine.",
                        ['outcome' => $outcome], LoggerService::LEVEL_WARNING);
                    continue;
                }

                // Extract details from outcome using the new keys
                $outcomeType = (string)$outcome['outcomeType'];
                $outcomeData = (array)$outcome['outcomeData'];
                $outcomeStatus = (string)$outcome['outcomeStatus'];
                $messageKey = isset($outcome['messageKey']) ? (string)$outcome['messageKey'] : null;
                $broadcastScope = (string)$outcome['broadcastScope'];
                $broadcastTargetId = isset($outcome['broadcastTargetId']) ? (string)$outcome['broadcastTargetId'] : null;

                // Log non-success status, but still proceed to create DTO and broadcast
                // The DTO and client can decide how to handle non-success statuses.
                if ($outcomeStatus !== 'success') {
                    $this->logger->log("GameActionHandler: Rule action resulted in non-success status.",
                        [
                            'type' => $outcomeType,
                            'status' => $outcomeStatus,
                            'data' => $outcomeData,
                            'scope' => $broadcastScope
                        ], LoggerService::LEVEL_INFO);
                }

                // Skip broadcasting if the scope is 'none'
                if ($broadcastScope === 'none') {
                    $this->logger->log("GameActionHandler: 'none' broadcast scope for outcome. Skipping broadcast.",
                        ['type' => $outcomeType, 'status' => $outcomeStatus]);
                    continue;
                }
                // If we only want to broadcast 'success' statuses, we can add this check:
                // if ($outcomeStatus !== 'success') {
                //     $this->logger->log("GameActionHandler: Skipping broadcast for non-success outcome.", ['type' => $outcomeType, 'status' => $outcomeStatus]);
                //     continue;
                // }


                $dto = $this->messageFactory->createRuleOutcomeMessage(
                    $outcomeType,
                    $outcomeData,
                    $outcomeStatus,
                    $messageKey,
                    $broadcastScope,
                    $broadcastTargetId
                );

                $this->logger->log("GameActionHandler: Broadcasting rule outcome.", [
                    'dto_type' => $dto->type,
                    'dto_scope' => $dto->broadcastScope,
                    'dto_target' => $dto->broadcastTargetId,
                    'dto_status' => $dto->status
                ]);

                // Use properties from the DTO for broadcasting decisions
                switch ($dto->broadcastScope) {
                    case 'player':
                        if ($dto->broadcastTargetId) {
                            $this->broadcastService->sendToClient($dto->broadcastTargetId, $dto);
                        } else {
                             $this->logger->log("GameActionHandler: Missing target_id for 'player' scope broadcast. Fallback to originating client.",
                                ['outcome_type' => $dto->type], LoggerService::LEVEL_WARNING);
                             $this->broadcastService->sendToClient($clientId, $dto);
                        }
                        break;
                    case 'session': // Send back to the originating connection
                        // Assuming sendBack takes the DTO directly, or adjust if it needs specific parts.
                        // If sendBack expects type & payload: $this->broadcastService->sendBack($from, $dto->type, get_object_vars($dto));
                        // For now, assuming it can handle the DTO object.
                        $this->broadcastService->sendBack($from, $dto->type, [
                            'status' => $dto->status,
                            'data' => $dto->data,
                            'message_key' => $dto->messageKey,
                            'timestamp' => $dto->timestamp
                        ]);
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
                    default: // Handles 'none' and any unknown scopes
                        $this->logger->log("GameActionHandler: Unknown or 'none' broadcast scope '{$dto->broadcastScope}'. No broadcast performed by default.",
                            ['outcome_type' => $dto->type], LoggerService::LEVEL_WARNING);
                        // Previously, unknown defaulted to quest. Explicit 'none' or specific handling is better.
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
