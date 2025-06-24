<?php

namespace common\services;

use common\extensions\EventHandler\LoggerService;
use common\models\Player;
use common\models\Quest;
use common\models\Race;
use common\models\Skill;
use common\models\Lighting;
use common\models\Tile;
use common\models\Room;
use Yii; // Assuming Yii framework for potential DB access or utilities

class DmService {

    private LoggerService $logger;

    public function __construct(LoggerService $logger) {
        $this->logger = $logger;
    }

    /**
     * Generates a scene description for a specific player.
     * @param int $playerId
     * @param int $questId
     * @return string The generated description for the player.
     */
    private function _getPlayerForDescription(int $playerId): ?Player
    {
        $player = Player::findOne($playerId);
        if (!$player) {
            $this->logger->log("DmService: Player $playerId not found for description.", null, 'error');
        }
        return $player;
    }

    private function _getRoomForDescriptionContext(int $questId): ?Room
    {
        $questProgress = QuestProgress::findOrCreate($questId);
        if (!$questProgress) {
            $this->logger->log("DmService: QuestProgress for quest $questId not found/created.", null, 'error');
            return null;
        }
        if (!$questProgress->current_room_id) {
            // Fallback logic or error if no current room is set in QuestProgress
            $this->logger->log("DmService: QuestProgress for $questId has no current_room_id.", null, 'warning');
            // Consider a more robust fallback if necessary, for now, null indicates an issue.
            return null;
        }
        // Ensure relation is loaded if not already
        if (!$questProgress->isRelationPopulated('currentRoom')) {
            $questProgress->populateRelation('currentRoom', $questProgress->getCurrentRoom()->one());
        }
        return $questProgress->currentRoom;
    }

    public function generateSceneDescriptionForPlayer(int $playerId, int $questId): string {
        $this->logger->log("DmService: Generating scene description for player $playerId in quest $questId");

        $player = $this->_getPlayerForDescription($playerId);
        if (!$player) return "Error: Your character details could not be found.";

        $room = $this->_getRoomForDescriptionContext($questId);
        if (!$room) return "You find yourself in an indistinct location; the details are hazy.";

        $roomDescription = $room->name . ". " . ($room->description ?? "It's a room.");

    private function _getLightingNameForRoom(Room $room): string
    {
        // Simplification: Use lighting of the first tile of the room's first grid.
        $lightingName = "Bright Light"; // Default
        $grid = $room->getGrs()->orderBy('id')->one();
        if ($grid) {
            $tile = $grid->getTiles()->orderBy('id')->one();
            if ($tile && $tile->lighting) {
                $lightingName = $tile->lighting->name;
            }
        }
        $this->logger->log("DmService: Determined lighting for room {$room->id} as '{$lightingName}'");
        return $lightingName;
    }

    private function _getPlayerPerceptionScore(Player $player): int
    {
        // Placeholder for actual skill calculation (Wisdom based + proficiency if applicable)
        $perceptionScore = 10; // Passive perception base
        $wisdomAbility = $player->getPlayerAbilities()->joinWith('ability')->where(['ability.name' => 'Wisdom'])->one();
        $wisModifier = 0;
        if($wisdomAbility && isset($wisdomAbility->value)){
            $wisModifier = floor(($wisdomAbility->value - 10) / 2);
            $perceptionScore += $wisModifier;
        }

        $perceptionSkillModel = Skill::findOne(['name' => 'Perception']);
        if ($perceptionSkillModel) {
            $playerSkill = $player->getPlayerSkills()->where(['skill_id' => $perceptionSkillModel->id])->one();
            if ($playerSkill && isset($playerSkill->value)) {
                // If PlayerSkill.value stores the total score or just proficiency
                // Assuming it stores total bonus for now for simplicity
                // This needs alignment with how skills are actually stored/calculated
                $perceptionScore = $playerSkill->value; // This might override passive, or add to it.
            } else if ($playerSkill) { // Proficient, but no specific value, add proficiency
                // Simplified proficiency bonus, actual calculation depends on level
                // $proficiencyBonus = 2; // Example
                // $perceptionScore = 10 + $wisModifier + $proficiencyBonus;
            }
        }
        $this->logger->log("DmService: Player {$player->id} ({$player->name}) calculated Perception score: {$perceptionScore}");
        return $perceptionScore;
    }

    public function generateSceneDescriptionForPlayer(int $playerId, int $questId): string {
        $this->logger->log("DmService: Generating scene description for player $playerId in quest $questId");

        $player = $this->_getPlayerForDescription($playerId);
        if (!$player) return "Error: Your character details could not be found.";

        $room = $this->_getRoomForDescriptionContext($questId);
        if (!$room) return "You find yourself in an indistinct location; the details are hazy.";

        $baseRoomDescription = $room->name . ". " . ($room->description ?? "It's a room.");
        $lightingName = $this->_getLightingNameForRoom($room);
        $perceptionScore = $this->_getPlayerPerceptionScore($player);

    private function _applyLightingEffectsToDescription(string $baseDesc, string $lightingName, Player $player, int $perceptionScore): string
    {
        $race = $player->race;
        $descWithLighting = $baseDesc;

        if ($lightingName === "Darkness") {
            if ($race && $race->darkvision > 0) {
                $descWithLighting .= " Even in the oppressive darkness, your {$race->name} eyes allow you to make out shapes and details within {$race->darkvision} feet.";
            } else {
                $dc = 18; $roll = rand(1, 20); // Simple perception check
                if (($roll + $perceptionScore) >= $dc) {
                    // For a player without darkvision in darkness, the base description might be too revealing.
                    // So, we replace it or make it very generic.
                    $descWithLighting = "It is pitch black, but by straining your eyes, you manage to make out vague shapes and outlines.";
                } else {
                    $descWithLighting = "It's pitch black. You can barely see anything around you.";
                }
            }
        } elseif ($lightingName === "Dim Light") {
            if ($race && $race->darkvision > 0) { // Player with darkvision sees normally in dim light
                $descWithLighting .= " The dim light poses no challenge to your {$race->name} vision.";
            } else {
                $descWithLighting .= " The area is dimly lit. Shadows cling to the corners, making details hard to discern. (Perception checks relying on sight are at disadvantage).";
            }
        } else { // Bright Light or other
            $descWithLighting .= " The area is currently lit by {$lightingName}.";
        }
        return $descWithLighting;
    }

    public function generateSceneDescriptionForPlayer(int $playerId, int $questId): string {
        $this->logger->log("DmService: Generating scene description for player $playerId in quest $questId");

        $player = $this->_getPlayerForDescription($playerId);
        if (!$player) return "Error: Your character details could not be found.";

        $room = $this->_getRoomForDescriptionContext($questId);
        if (!$room) return "You find yourself in an indistinct location; the details are hazy.";

        $baseRoomDescription = $room->name . ". " . ($room->description ?? "It's a room.");
        $lightingName = $this->_getLightingNameForRoom($room);
        $perceptionScore = $this->_getPlayerPerceptionScore($player);

        $initialDescription = "You are in the " . $baseRoomDescription;
        $fullDescription = $this->_applyLightingEffectsToDescription($initialDescription, $lightingName, $player, $perceptionScore);

        // TODO: List interactive elements, other characters based on perception.
        return $fullDescription;
    }

    /**
     * Evaluates a player's action and logs it.
     * @param int $playerId
     * @param int $gameRoundId ID of the current GameRound
     * @param array $actionData Example: ['type' => 'look_around', 'details' => {}]
     * @return array ['text' => "Outcome description", 'is_successful' => bool, 'state_changes' => []]
     */
    /**
     * Evaluates a player's action and logs it.
     * Signature is correct as per previous step.
     * This method creates the PlayerAction record.
     * @param int $playerId
     * @param int $gameRoundId ID of the current GameRound
     * @param array $actionData Example: ['type' => 'look_around', 'details' => {}]
     * @return array ['text' => "Outcome description", 'is_successful' => bool, 'state_changes' => [], 'error' => null|string]
     */
    private function _evaluateLookAroundAction(Player $player, int $questId): array
    {
        $outcomeText = $this->generateSceneDescriptionForPlayer($player->id, $questId);
        return ['text' => $outcomeText, 'is_successful' => true];
    }

    private function _evaluateSkillCheckAction(Player $player, array $actionDetails, int $gameRoundId): array
    {
        $skillName = $actionDetails['skill'] ?? 'Perception';
        $dc = isset($actionDetails['dc']) ? (int)$actionDetails['dc'] : 10;
        $roll = rand(1,20);
        $skillBonus = 0;
        // This skill bonus calculation can be further refactored if it exceeds 20 lines with full logic.
        $playerSkillModel = Skill::findOne(['name' => $skillName]);
        if ($playerSkillModel) {
            $playerSkillAssoc = $player->getPlayerSkills()->where(['skill_id' => $playerSkillModel->id])->one();
            if ($playerSkillAssoc && isset($playerSkillAssoc->value)) {
                $skillBonus = (int)$playerSkillAssoc->value;
            } else {
                $abilityName = $playerSkillModel->ability->name ?? 'Wisdom';
                $ability = $player->getPlayerAbilities()->joinWith('ability')->where(['ability.name' => $abilityName])->one();
                if ($ability && isset($ability->value)) {
                    $skillBonus = floor(((int)$ability->value - 10) / 2);
                }
            }
        } else {
             $this->logger->log("DmService: Skill '{$skillName}' not found.", ['game_round_id' => $gameRoundId], 'warning');
        }

        $totalRoll = $roll + $skillBonus;
        $isSuccessful = $totalRoll >= $dc;
        $outcomeText = "{$player->name} attempts a {$skillName} check (DC {$dc}) and " .
                       ($isSuccessful ? "succeeds!" : "fails.") .
                       " (Rolled {$roll} + {$skillBonus} = {$totalRoll})";
        return ['text' => $outcomeText, 'is_successful' => $isSuccessful];
    }

    private function _logPlayerAction(int $gameRoundId, int $playerId, string $actionType, array $actionDetails, bool $isSuccessful, string $outcomeDescription): void
    {
        $playerAction = new PlayerAction();
        $playerAction->game_round_id = $gameRoundId;
        $playerAction->player_id = $playerId;
        $playerAction->action_type = $actionType;
        $playerAction->action_details = json_encode($actionDetails);
        $playerAction->is_successful = $isSuccessful;
        $playerAction->outcome_description = $outcomeDescription;

        if (!$playerAction->save()) {
            $this->logger->log("DmService: Failed to save PlayerAction. Errors: " . print_r($playerAction->getErrors(), true), ['gr_id' => $gameRoundId]);
        } else {
            $this->logger->log("DmService: PlayerAction {$playerAction->id} saved for GR {$gameRoundId}.", ['type' => $actionType, 'p_id' => $playerId]);
        }
    }

    public function evaluatePlayerAction(int $playerId, int $gameRoundId, array $actionData): array {
        $this->logger->log("DmService: Evaluating action for player $playerId in GameRound $gameRoundId", $actionData);

        $player = Player::findOne($playerId);
        if (!$player) { /* ... error handling ... */
            return ['text' => "Error: Player not found.", 'is_successful' => false, 'state_changes' => [], 'error' => 'Player not found'];
        }

        $gameRound = GameRound::findOne($gameRoundId);
        if (!$gameRound) { /* ... error handling ... */
            return ['text' => "Error: GameRound not found.", 'is_successful' => false, 'state_changes' => [], 'error' => 'GameRound not found'];
        }
        $questId = $gameRound->quest_id;

        $actionType = $actionData['type'] ?? 'unknown';
        $actionDetails = $actionData['details'] ?? [];
        $result = ['text' => "{$player->name} attempts {$actionType}.", 'is_successful' => false, 'state_changes' => [], 'error' => null];

        switch ($actionType) {
            case 'look_around':
                $evalResult = $this->_evaluateLookAroundAction($player, $questId);
                $result['text'] = $evalResult['text'];
                $result['is_successful'] = $evalResult['is_successful'];
                break;
            case 'skill_check':
                $evalResult = $this->_evaluateSkillCheckAction($player, $actionDetails, $gameRoundId);
                $result['text'] = $evalResult['text'];
                $result['is_successful'] = $evalResult['is_successful'];
               break;
            default:
                $result['text'] = "{$player->name} attempts to '{$actionType}', but this action is not yet understood.";
                $result['is_successful'] = false;
                $this->logger->log("DmService: Unknown action type: {$actionType}", ['gr_id' => $gameRoundId]);
                break;
        }

        $this->_logPlayerAction($gameRoundId, $playerId, $actionType, $actionDetails, $result['is_successful'], $result['text']);
        return $result;
    }

    /**
     * Retrieves the active GameRound for a given quest and optionally a room.
     * If no active round exists, it can attempt to create one.
     *
     * @param int $questId
     * @param int|null $roomId If null, will try to get room from QuestProgress.
     * @param bool $autoCreateIfMissing If true, creates a new GameRound if none active is found.
     * @return GameRound|null
     */
    /**
     * Ensures an active GameRound exists for the given QuestProgress, creating one if necessary.
     *
     * @param QuestProgress $questProgress The progress context for the round.
     * @param bool &$newRoundJustStarted Passed by reference, set to true if a new round was created.
     * @param string|null &$newRoundDescription Passed by reference, set to the new round's start description.
     * @return GameRound|null The active or newly created GameRound, or null on error.
     */
    private function _ensureActiveGameRound(QuestProgress $questProgress, bool &$newRoundJustStarted, ?string &$newRoundDescription): ?GameRound
    {
        $questId = $questProgress->quest_id;
        // GameRound::findActive now takes quest_id and quest_progress_id
        $activeGameRound = GameRound::findActive($questId, $questProgress->id);

        $newRoundJustStarted = false;
        $newRoundDescription = null;

        if (!$activeGameRound) {
            $this->logger->log("DmService: No active GameRound for quest_progress_id {$questProgress->id}. Creating new round.", null, 'info');
            $activeGameRound = new GameRound();
            $activeGameRound->quest_id = $questId;
            $activeGameRound->quest_progress_id = $questProgress->id;

            $previousMaxRound = GameRound::find()->where(['quest_progress_id' => $questProgress->id])->max('round_number');
            $activeGameRound->round_number = $previousMaxRound ? ((int)$previousMaxRound) + 1 : 1;

            $activeGameRound->status = AppStatus::PLAYING->value;

            // Ensure currentRoom relation is loaded for $questProgress before accessing its name
            if (!$questProgress->isRelationPopulated('currentRoom') && $questProgress->current_room_id) {
                $questProgress->populateRelation('currentRoom', $questProgress->getCurrentRoom()->one());
            }
            $roomName = $questProgress->currentRoom ? $questProgress->currentRoom->name : "this area";
            $activeGameRound->round_start_description = "Round {$activeGameRound->round_number} begins in {$roomName}!";

            if (!$activeGameRound->save()) {
                $this->logger->log("DmService: Failed to save new GameRound. Errors: " . print_r($activeGameRound->getErrors(), true), null, 'error');
                return null; // Indicate error by returning null
            }
            $newRoundJustStarted = true;
            $newRoundDescription = $activeGameRound->round_start_description;
            $this->logger->log("DmService: New GameRound {$activeGameRound->id} created. Round {$activeGameRound->round_number}.", ['description' => $newRoundDescription]);
        }
        return $activeGameRound;
    }

    /**
     * Advances to the next player's turn or starts a new round within a GameRound.
     * Updates the GameRound model with the new turn state.
     * @param int $gameRoundId
     * @param int $actingPlayerId Player who just finished their turn
     * @return array ['next_player_id' => ID|null, 'new_round_started' => bool, 'scene_update_needed_for_next_player' => bool, 'round_start_description' => string|null, 'error' => string|null]
     */
    public function advanceTurn(int $gameRoundId, int $actingPlayerId): array {
        $this->logger->log("DmService: Advancing turn for GameRound $gameRoundId after player $actingPlayerId's action.");

        $gameRound = GameRound::findOne($gameRoundId);
        if (!$gameRound || $gameRound->status !== AppStatus::PLAYING->value) { // Check against AppStatus::PLAYING
            $this->logger->log("DmService: Active GameRound $gameRoundId not found for advanceTurn (Status: " . ($gameRound ? $gameRound->status : 'N/A') . ").", null, 'error');
            return ['next_player_id' => null, 'new_round_started' => false, 'scene_update_needed_for_next_player' => false, 'round_start_description' => null, 'error' => "Active game round not found."];
        }

        $turnOrder = !empty($gameRound->player_turn_order) ? json_decode($gameRound->player_turn_order, true) : [];
        $actionsTaken = !empty($gameRound->actions_taken_this_round) ? json_decode($gameRound->actions_taken_this_round, true) : [];

        if (json_last_error() !== JSON_ERROR_NONE) { // Check after decoding both
            $this->logger->log("DmService: Invalid JSON in GameRound $gameRoundId state.", null, 'error');
            // Attempt to re-initialize the round's players? Or mark round as corrupted.
            // For now, return error.
            return ['next_player_id' => null, 'new_round_started' => false, 'scene_update_needed_for_next_player' => false, 'error' => "Corrupted turn state in game round."];
        }
        $turnOrder = is_array($turnOrder) ? $turnOrder : [];
        $actionsTaken = is_array($actionsTaken) ? $actionsTaken : [];


        if ($gameRound->current_player_id !== $actingPlayerId) {
            $this->logger->log("DmService: Warning - advanceTurn called for player $actingPlayerId but current player is {$gameRound->current_player_id} for GameRound $gameRoundId.", null, 'warning');
        }

        if (empty($turnOrder)) {
            $this->logger->log("DmService: Turn order is empty for GameRound $gameRoundId. Attempting to re-initialize.", null, 'warning');
            $playerModels = $gameRound->quest->getCurrentPlayers()->all();
            $playerIds = array_map(function($p) { return $p->id; }, $playerModels);
            if(!$gameRound->initializePlayersForRound($playerIds, true)) { // true = save now
                 return ['next_player_id' => null, 'new_round_started' => false, 'scene_update_needed_for_next_player' => false, 'error' => "Turn order empty and could not be re-initialized."];
            }
            // Re-fetch turn state after initialization
            $turnOrder = !empty($gameRound->player_turn_order) ? json_decode($gameRound->player_turn_order, true) : [];
            $actionsTaken = !empty($gameRound->actions_taken_this_round) ? json_decode($gameRound->actions_taken_this_round, true) : [];
            $turnOrder = is_array($turnOrder) ? $turnOrder : [];
            $actionsTaken = is_array($actionsTaken) ? $actionsTaken : [];
        }

        if (!in_array($actingPlayerId, $actionsTaken)) {
            $actionsTaken[] = $actingPlayerId;
        }

        $nextPlayerId = null;
        $newRoundStarted = false;
        $newRoundStartDescription = null; // Initialize

        $remainingPlayersInOrder = array_diff($turnOrder, $actionsTaken);

        if (!empty($remainingPlayersInOrder)) {
            foreach($turnOrder as $pIdInOrder) {
                if (in_array($pIdInOrder, $remainingPlayersInOrder)) {
                    $nextPlayerId = $pIdInOrder;
                    break;
                }
            }
        } else {
            // All players in turn_order have acted, start a new round
            $newRoundStarted = true;
            $gameRound->round_number++;
            $actionsTaken = []; // Reset for the new round
            if (!empty($turnOrder)) {
                $nextPlayerId = $turnOrder[0]; // First player in the original order
            }
            // Generate round start description for the new round
            // Ensure questProgress and currentRoom are loaded for the description
            $gameRound->load('questProgress.currentRoom'); // Eager load if not already
            $roomName = $gameRound->questProgress && $gameRound->questProgress->currentRoom ? $gameRound->questProgress->currentRoom->name : "the current area";
            $newRoundStartDescription = "Round {$gameRound->round_number} begins in {$roomName}!";
            $gameRound->round_start_description = $newRoundStartDescription;
            $this->logger->log("DmService: New round {$gameRound->round_number} starting for GameRound {$gameRoundId}. Description: {$newRoundStartDescription}");
        }

        $gameRound->current_player_id = $nextPlayerId;
        $gameRound->actions_taken_this_round = json_encode(array_values($actionsTaken));

        // Attributes to save
        $attributesToSave = ['current_player_id', 'actions_taken_this_round', 'round_number'];
        if ($newRoundStarted) {
            $attributesToSave[] = 'round_start_description';
        }

        if (!$gameRound->save(false, $attributesToSave)) {
            $this->logger->log("DmService: Failed to save GameRound $gameRoundId after advancing turn. Errors: " . print_r($gameRound->getErrors(), true), null, 'error');
            return ['next_player_id' => null, 'new_round_started' => false, 'scene_update_needed_for_next_player' => false, 'error' => "Failed to save game round state."];
        }

        $this->logger->log("DmService: Turn advanced for GameRound $gameRoundId. New current player: $nextPlayerId. Round: {$gameRound->round_number}. New round: " . ($newRoundStarted ? 'Yes' : 'No'));

        return [
            'next_player_id' => $nextPlayerId,
            'new_round_started' => $newRoundStarted,
            'scene_update_needed_for_next_player' => true, // Always true for now, as next player needs scene
            'error' => null
        ];
    }
}
