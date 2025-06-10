<?php

namespace frontend\components;

use common\components\AppStatus;
use common\models\QuestPlayer;
use Yii;

class QuestOnboarding {

    const WELLCOME_MESSAGES = [
        "There's nobody here!",
        "For the moment, it looks like you're the first one",
        "Look, there are two of you now",
        "Ah! but there are three of you! Wait, I'll get a chair",
        "Now that there are four of you, I'm going to put you on a bigger table",
        "With five guys like you, it's going to be quite a team!"
    ];

    /**
     * Add a new record to quest_player table
     *
     * @param int $questId
     * @param int $playerId
     * @param bool $isInitiator
     * @return bool
     */
    public static function newQuestPlayer(int $questId, int $playerId, bool $isInitiator = false): bool {
        $questPlayer = new QuestPlayer([
            'quest_id' => $questId,
            'player_id' => $playerId,
            'onboarded_at' => time(),
            'is_initiator' => ($isInitiator ? 1 : 0)
        ]);

        return $questPlayer->save();
    }

    /**
     * Returns a wellcone message for a new joiner
     *
     * @param int $questId
     * @return string
     */
    public static function wellcomeMessage(int $questId): string {

        $count = self::playerCount($questId);

        $builtinMessages = count(self::WELLCOME_MESSAGES);
        $message = $count < $builtinMessages ? self::WELLCOME_MESSAGES[$count] : "Boy, that's quite a team!";

        return $message;
    }

    /**
     * Check if a player can join a quest.
     * Returns an array with a deny status and a reason messge.
     *
     * @param \common\models\Player $player
     * @param \common\models\Quest $quest
     * @return array
     */
    public static function canPlayerJoinQuest(\common\models\Player $player, \common\models\Quest $quest): array {

        $questStatus = self::isQuestValid($player, $quest);
        if ($questStatus['error']) {
            return $questStatus;
        }

        // Check if player is already in another quest
        if ($player->quest_id === $quest->id) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Player {$player->name} is already involved in another quest"];
        }

        $story = $quest->story;

        if (!self::isPlayerLevelValid($player, $story)) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Player {$player->name}'s level ({$player->level->name}) is not within story requirements ({$story->min_level} to {$story->max_level})"];
        }

        $playersCount = $quest->getCurrentPlayers()->count();
        if ($playersCount >= $story->max_players) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Quest has reached maximum players ({$story->max_players})"];
        }

        if (!self::shouldAllowPlayerClass($player, $quest)) {
            return ['denied' => true, "'reason' => 'canPlayerJoinQuest->Cannot welcome more players of class {$player->class->name}"];
        }

        return ['denied' => false, 'reason' => "canPlayerJoinQuest->Player {$player->name} can join the quest"];
    }

    /**
     * Check if a quest is valid and if a player is willing to join it.
     * return an array with an error status, a deny status and a reason message.
     *
     * @param \common\models\Player $player
     * @param \common\models\Quest $quest
     * @return array
     */
    private static function isQuestValid(\common\models\Player $player, \common\models\Quest $quest): array {
        // Check if player is selected
        if (!$player) {
            return ['error' => true, 'denied' => true, 'reason' => "isQuestValid->You must select a player first"];
        }

        // Check if quest exists
        if (!$quest) {
            // Check if player is already in another quest
            if ($player->quest_id) {
                return ['error' => true, 'denied' => true, 'reason' => "isQuestValid->{$player->name} is already involved in another quest"];
            }
            return ['error' => true, 'denied' => false, 'reason' => "isQuestValid->You can create a new quest"];
        }

        if ($quest->status !== AppStatus::WAITING->value) {
            return ['error' => true, 'denied' => true, 'reason' => "isQuestValid->Quest is not waiting to start anymore"];
        }

        return ['error' => false, 'denied' => false, 'reason' => "isQuestValid->Quest #{$quest->id} is valid quest"];
    }

    /**
     * Check is the player's level is compatible with the story requirements
     *
     * @param \common\models\Player $player
     * @param \common\models\Story $story
     * @return bool
     */
    private static function isPlayerLevelValid(\common\models\Player $player, \common\models\Story $story): bool {
        return $player->level_id >= $story->min_level && $player->level_id <= $story->max_level;
    }

    /**
     * Check is the player's class is allowed with the story requirements.
     * If nothing is specified, every class can join.
     *
     * @param \common\models\Player $player
     * @param \common\models\Quest $quest
     * @return bool
     */
    private static function shouldAllowPlayerClass(\common\models\Player $player, \common\models\Quest $quest): bool {
        $story = $quest->story;
        $requiredClasses = $story->classes;

        if (!$requiredClasses) {
            return true;
        }

        // Extract class IDs from the required classes
        $requiredClassIds = array_column($requiredClasses, 'id');

        $currentPlayers = $quest->getPlayers()->with('class')->all();
        $presentClasses = array_unique(array_column($currentPlayers, 'class_id'));

        // If player's class is required and not yet present. Now comparing IDs with IDs
        if (in_array($player->class_id, $requiredClassIds) && !in_array($player->class_id, $presentClasses)) {
            return true;
        }

        // Check if we need to reserve spots for missing required classes
        $remainingSpots = $story->max_players - count($currentPlayers);
        $missingClassesCount = count(array_diff($requiredClassIds, $presentClasses));

        return $remainingSpots > $missingClassesCount;
    }
}
