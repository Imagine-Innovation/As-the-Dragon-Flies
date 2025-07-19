<?php

namespace frontend\components;

use common\components\AppStatus;
use common\models\CharacterClass;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\Player;
use common\models\Story;
use common\models\StoryClass;
use Yii;

class QuestOnboarding {

    const WELLCOME_MESSAGES = [
        0 => "There's nobody here!",
        1 => "For the moment, it looks like you're the first one",
        2 => "Look, there are two of you now",
        3 => "Ah! but there are three of you! Wait, I'll get a chair",
        4 => "Now that there are four of you, I'm going to put you on a bigger table",
        5 => "With five guys like you, it's going to be quite a team!"
    ];
    const DEFAULT_MESSAGE = "Boy, that's quite a team!";

    /**
     * Returns a wellcone message for a new joiner
     *
     * @param int $count
     * @return string
     */
    public static function welcomeMessage(int $count): string {

        $message = array_key_exists($count, self::WELLCOME_MESSAGES) ?
                self::WELLCOME_MESSAGES[$count] :
                self::DEFAULT_MESSAGE;

        Yii::debug("*** Debug *** welcomeMessage({$count})={$message}");
        return $message;
    }

    /**
     * Returns a message given the missing number of players before the quest can start
     *
     * @param Quest $quest
     * @param int|null $playerCount
     * @return string
     */
    public static function missingPlayers(Quest &$quest, int|null $playerCount = null): string {
        $actualPlayerCount = $playerCount ?? 0;
        $storyMinPlayers = $quest->story->min_players;
        $missingCount = $storyMinPlayers - $actualPlayerCount;

        if ($missingCount > 1) {
            return "We're still waiting for {$missingCount} other members to join us before starting";
        }

        if ($missingCount === 1) {
            return "One more member to join and we can start";
        }
        return "The whole company is there, we can start!";
    }

    /**
     * returns a message with the list of missing classes before the quest can start,
     * null if ne class is required
     *
     * @param Quest $quest
     * @return string|null
     */
    public static function missingClasses(Quest &$quest): string|null {
        // Get the missing classes
        $missingClassIds = self::getMissingClassIds($quest);

        // If no specific class is required, consider that they are all present
        if ($missingClassIds === null) {
            return null;
        }

        $classes = CharacterClass::findAll(['id' => $missingClassIds]);
        $classNames = [];

        foreach ($classes as $class) {
            $classNames[] = $class->name;
        }
        $count = count($classNames);
        if ($count === 1) {
            return $classNames[0];
        }
        if ($count === 2) {
            return "{$classNames[0]} and a {$classNames[1]}";
        }

        $lastClassName = $classNames[$count - 1];
        $otherClassNames = array_pop($classNames);
        $missingClasses = implode(', ', $otherClassNames) . " and {$lastClassName}";
        return $missingClasses;
    }

    /**
     * Returns an array of missing class IDs, null if bo class is required
     *
     * @param Quest $quest
     * @return array|null
     */
    private static function getMissingClassIds(Quest &$quest): array|null {
        // Get the required classes
        $requiredClassIds = self::getRequiredClassIds($quest->story_id);

        // If no specific class is required, consider that they are all present
        if (!$requiredClassIds) {
            return null;
        }

        // Fetch the actual player classes
        $actualPlayerClasses = self::getActualPlayerClassIds($quest->id);

        return array_diff($requiredClassIds, $actualPlayerClasses);
    }

    /**
     * Check if a player can join a quest.
     * Returns an array with a deny status and a reason messge.
     *
     * @param Player|null $player
     * @param Quest|null $quest
     * @return array
     */
    public static function canPlayerJoinQuest(Player|null $player, Quest|null $quest = null): array {
        // Check if the player is eliible to join a quest
        $playerStatus = self::isPlayerValid($player, $quest);
        if ($playerStatus['error']) {
            return $playerStatus;
        }

        // Check if the quest is a valid one
        $questStatus = self::isQuestValid($quest);
        if ($questStatus['error']) {
            return $questStatus;
        }

        $story = $quest->story;

        if (!self::isPlayerLevelValid($player, $story)) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Player {$player->name}'s level ({$player->level->name}) is not within story requirements ({$story->min_level} to {$story->max_level})"];
        }

        $playerCount = $quest->getCurrentPlayers()->count();
        if ($playerCount >= $story->max_players) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Quest has reached maximum players ({$story->max_players})"];
        }

        if (!self::shouldAllowPlayerClass($player, $quest)) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Cannot welcome more players of class {$player->class->name}"];
        }

        return ['denied' => false, 'reason' => "canPlayerJoinQuest->Player {$player->name} can join the quest"];
    }

    /**
     * Check if a quest is valid.
     * returns an array with an error status, a deny status and a reason message.
     *
     * @param Quest|null $quest
     * @return array
     */
    private static function isQuestValid(Quest|null $quest = null): array {
        // Check if quest exists
        if (!$quest) {
            return ['error' => true, 'denied' => false, 'reason' => "canPlayerJoinQuest->You can create a new quest"];
        }

        if ($quest->status !== AppStatus::WAITING->value) {
            return ['error' => true, 'denied' => true, 'reason' => "canPlayerJoinQuest->Quest is not waiting to start anymore"];
        }

        return ['error' => false, 'denied' => false, 'reason' => "canPlayerJoinQuest->Quest #{$quest->id} is valid quest"];
    }

    /**
     * Check if a player can join a new quest
     * return an array with an error status, a deny status and a reason message
     *
     * @param Player $player
     * @param Quest|null $quest
     * @return array
     */
    private static function isPlayerValid(Player &$player, Quest|null $quest = null): array {
        // Check if player is selected
        if (!$player) {
            return ['error' => true, 'denied' => true, 'reason' => "canPlayerJoinQuest->No player is selected"];
        }

        // Check if player is in another quest
        if ($quest === null) {
            if ($player->quest_id) {
                Yii::debug("*** Debug *** isPlayerValid - quest is null and player->quest_id is {$player->quest_id}");
                return ['error' => true, 'denied' => true, 'reason' => "canPlayerJoinQuest->Player {$player->name} is already involved in another quest"];
            }
        } elseif ($player->quest_id && $player->quest_id !== $quest->id) {
            Yii::debug("*** Debug *** isPlayerValid - quest is not null but player->quest_id is {$player->quest_id} <> quest->id {$quest->id}");
            return ['error' => true, 'denied' => true, 'reason' => "canPlayerJoinQuest->Player {$player->name} is already involved in another quest"];
        }

        return ['error' => false, 'denied' => false, 'reason' => "canPlayerJoinQuest->Player {$player->name} is eligible to join a quest"];
    }

    /**
     * Check is the player's level is compatible with the story requirements
     *
     * @param Player $player
     * @param \common\models\Story $story
     * @return bool
     */
    private static function isPlayerLevelValid(Player &$player, Story &$story): bool {
        return $player->level_id >= $story->min_level && $player->level_id <= $story->max_level;
    }

    /**
     * Check is the player's class is allowed with the story requirements.
     * If nothing is specified, every class can join.
     *
     * @param Player $player
     * @param Quest $quest
     * @return bool
     */
    private static function shouldAllowPlayerClass(Player &$player, Quest &$quest): bool {
        $requiredClassIds = self::getRequiredClassIds($quest->story_id);

        // If no specific class is required, consider that they are all present
        if (count($requiredClassIds) === 0) {
            return true;
        }

        $actualPlayerClasses = self::getActualPlayerClassIds($quest->id);

        Yii::debug($requiredClassIds);
        Yii::debug($actualPlayerClasses);
        // If player's class is required and not yet present
        if (in_array($player->class_id, $requiredClassIds) && !in_array($player->class_id, $actualPlayerClasses)) {
            return true;
        }

        // Check if we need to reserve slots for missing required classes
        $remainingSlots = $quest->story->max_players - self::getCurrentPlayerCount($quest->id);
        $missingClassesCount = count(array_diff($requiredClassIds, $actualPlayerClasses));

        return $remainingSlots > $missingClassesCount;
    }

    /**
     * Check of every required classes defined in the story
     * have onboarded in the quest
     *
     * @param Quest $quest
     * @return bool
     */
    public static function areRequiredClassesPresent(Quest &$quest): bool {
        // Get the missing classes
        $missingClassIds = self::getMissingClassIds($quest);

        // If no specific class is required, consider that they are all present
        if ($missingClassIds === null) {
            return true;
        }
        // If every required classes is present within the selected players, return true
        return empty($missingClassIds);
    }

    private static function getRequiredClassIds(int $storyId): array|null {
        // Get the required classes
        $classes = StoryClass::find()
                ->select('class_id')
                ->where(['story_id' => $storyId])
                ->all();

        $classIds = [];

        foreach ($classes as $class) {
            $classIds[] = $class->class_id;
        }

        return $classIds;
    }

    private static function getActualPlayerClassIds(int $questId): array|null {
        // Fetch the actual player classes
        $classes = Player::find()
                ->select('class_id')
                ->where(['quest_id' => $questId])
                ->all();

        $classIds = [];

        foreach ($classes as $class) {
            $classIds[] = $class->class_id;
        }

        return $classIds;
    }

    private static function getCurrentPlayerCount(int $questId): int {
        return Player::find()
                        ->where(['quest_id' => $questId])
                        ->count();
    }

    /**
     * Update the player's quest_id
     *
     * @param Player $player reference to a Player object
     * @param int|null $questId ID of the quest in which the player is engaged. Null if he leaves the quest
     * @return array
     */
    private static function updatePlayerQuestId(Player &$player, int|null $questId = null): array {
        $player->quest_id = $questId;
        if (!$player->save()) {
            return ['error' => true, 'message' => "Could not save Player : " . implode("\n", \yii\helpers\ArrayHelper::getColumn($player->errors, 0, false))];
        }
        return ['error' => false, 'message' => "Player's quest_id updated to {$questId}"];
    }

    /**
     * Update or Insert a new QuestPlayer entry
     *
     * @param int $questId ID of the quest
     * @param int $playerId ID of the player
     * @param string|null $reasonWhyPlayerLeft Reason why the player left the quest
     * @return array
     */
    private static function upsertQuestPlayer(int $questId, int $playerId, string|null $reasonWhyPlayerLeft = null): array {
        $questPlayer = QuestPlayer::findOne(['quest_id' => $questId, 'player_id' => $playerId]);

        if (!$questPlayer) {
            // If no record is previously existing, create a new entry
            $questPlayer = new QuestPlayer([
                'quest_id' => $questId,
                'player_id' => $playerId,
                'onboarded_at' => time()
            ]);
        }

        $questPlayer->left_at = $reasonWhyPlayerLeft ? time() : null;
        $questPlayer->reason = $reasonWhyPlayerLeft;

        if ($questPlayer->save()) {
            return ['error' => false, 'message' => "Player successfully onboarded on the quest"];
        }
        return ['error' => true, 'message' => "Could not save QuestPlayer : " . implode("\n", \yii\helpers\ArrayHelper::getColumn($questPlayer->errors, 0, false))];
    }

    /**
     * Add a player to a quest
     *
     * @param Player $player reference to a Player object
     * @param Quest $quest reference to a Quest object
     * @return array
     */
    public static function addPlayerToQuest(Player &$player, Quest &$quest): array {

        if ($player->quest_id === $quest->id) {
            return ['error' => false, 'message' => "Player is already onboarded in the quest"];
        }

        $playerUpdate = self::updatePlayerQuestId($player, $quest->id);
        if ($playerUpdate['error']) {
            return $playerUpdate;
        }

        return self::upsertQuestPlayer($quest->id, $player->id);
    }

    /**
     *
     * @param Player $player
     * @param Quest $quest
     * @param string $reason
     * @return array
     */
    public static function withdrawPlayerFromQuest(Player $player, Quest $quest, string $reason = null): array {

        // Player is already onboarded on a different quest => error
        if ($player->quest_id === null || $player->quest_id !== $quest->id) {
            return ['error' => true, 'message' => "player {$player->name} (id {$player->id}) is not in quest={$quest->id}, player->quest_id=" . ($player->quest_id ?? "null")];
        }

        $playerUpdate = self::updatePlayerQuestId($player, null);
        if ($playerUpdate['error']) {
            return $playerUpdate;
        }
        return self::upsertQuestPlayer($quest->id, $player->id, $reason);
    }
}
