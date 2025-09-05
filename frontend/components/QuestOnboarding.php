<?php

namespace frontend\components;

use common\components\AppStatus;
use common\components\ContextManager;
use common\models\CharacterClass;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\Player;
use common\models\Story;
use common\models\StoryClass;
use Yii;

class QuestOnboarding
{

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
     * @param int $playerCount Number of already on boarded players
     * @return string Welcome message
     */
    public static function welcomeMessage(int $playerCount): string {

        $message = array_key_exists($playerCount, self::WELLCOME_MESSAGES) ?
                self::WELLCOME_MESSAGES[$playerCount] :
                self::DEFAULT_MESSAGE;

        return $message;
    }

    /**
     * Returns a message given the missing number of players before the quest can start
     *
     * @param Quest $quest reference to a Quest object
     * @param int|null $playerCount Number of already on boarded players, null when quest is beeing created
     * @return string Missing player message
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
     * null if no class is required
     *
     * @param Quest $quest reference to a Quest object
     * @return string|null Missing classes message, null if no class is required
     */
    public static function missingClasses(Quest &$quest): ?string {
        $missingClassIds = self::getMissingClassIds($quest);

        if ($missingClassIds === null) {
            return null;
        }

        $classNames = array_map(
                fn($class) => $class->name,
                CharacterClass::findAll(['id' => $missingClassIds])
        );

        return match (count($classNames)) {
            0 => "Every expected class is represented in the company!",
            1 => "We still need a {$classNames[0]} to meet all the conditions.",
            2 => "We still need a {$classNames[0]} and a {$classNames[1]} to meet all the conditions.",
            default => "We still need a " . implode(', a ', array_slice($classNames, 0, -1)) . " and a " . end($classNames) . " to meet all the conditions."
        };
    }

    /**
     * Get the list of missing class IDs, null if bo class is required
     *
     * @param Quest $quest reference to a Quest object
     * @return array|null Missing class IDs, null if bo class is required
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
     *
     * @param Player|null $player
     * @param Quest|null $quest a Quest object, null if the player is not in a quest
     * @return array Associative array with a deny status and the reason why
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

        if (!self::isPlayerClassValid($player, $quest)) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Cannot welcome more players of class {$player->class->name}"];
        }

        return ['denied' => false, 'reason' => "canPlayerJoinQuest->Player {$player->name} can join the quest"];
    }

    /**
     * Check if a quest is valid
     *
     * @param Quest|null $quest a Quest object, null if the quest does not exist
     * @return array Associative array with an error status, a deny status and the reason why
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
     * Check if a player is valid to join a new quest
     *
     * @param Player|null $player reference to a Player object
     * @param Quest|null $quest a Quest object, null if the player is not in a quest
     * @return array Associative array with an error status, a deny status and the reason why
     */
    private static function isPlayerValid(Player|null $player, Quest|null $quest = null): array {
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
     * @param Player $player reference to a Player object
     * @param Story $story reference to a Story object
     * @return bool
     */
    private static function isPlayerLevelValid(Player &$player, Story &$story): bool {
        return $player->level_id >= $story->min_level && $player->level_id <= $story->max_level;
    }

    /**
     * Check is the player's class is allowed and fulfills the story requirements.
     * If nothing is specified, every class can join.
     *
     * @param Player $player reference to a Player object
     * @param Quest $quest reference to a Quest object
     * @return bool
     */
    private static function isPlayerClassValid(Player &$player, Quest &$quest): bool {
        $requiredClassIds = self::getRequiredClassIds($quest->story_id);

        // If no specific class is required, consider that they are all present
        if (count($requiredClassIds) === 0) {
            return true;
        }

        $actualPlayerClasses = self::getActualPlayerClassIds($quest->id);

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
     * @param Quest $quest reference to a Quest object
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

    /**
     * Returns an array of class IDs defined in the story used to create a quest
     *
     * @param int $storyId
     * @return array|null
     */
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

    /**
     * Returns an array of class IDs that are already present in the quest
     *
     * @param int $questId
     * @return array|null
     */
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

    /**
     * Returns the number of players that are actually onboarded on the quest
     *
     * @param int $questId
     * @return int
     */
    private static function getCurrentPlayerCount(int $questId): int {
        $playerCount = Player::find()
                ->where(['quest_id' => $questId])
                ->count();

        return $playerCount;
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
        ContextManager::updateQuestContext($questId);
        return ['error' => false, 'message' => "Player's quest_id updated to {$questId}"];
    }

    /**
     * Update or Insert a new QuestPlayer entry
     *
     * @param int $questId ID of the quest
     * @param int $playerId ID of the player
     * @param int $status Status of the player in the quest
     * @param string|null $reasonWhyPlayerQuit Reason why the player left the quest, null when inserting a new entry
     * @return array
     */
    private static function upsertQuestPlayer(int $questId, int $playerId, int $status, string|null $reasonWhyPlayerQuit = null): array {
        $questPlayer = QuestPlayer::findOne(['quest_id' => $questId, 'player_id' => $playerId]);

        if (!$questPlayer) {
            // If no record is previously existing, create a new entry
            $questPlayer = new QuestPlayer([
                'quest_id' => $questId,
                'player_id' => $playerId,
                'onboarded_at' => time(),
                'status' => $status,
            ]);
        }

        $questPlayer->left_at = $reasonWhyPlayerQuit ? time() : null;
        $questPlayer->reason = $reasonWhyPlayerQuit;

        if ($questPlayer->save()) {
            return ['error' => false, 'message' => "Player successfully " . ($reasonWhyPlayerQuit ? "left" : "joined") . " on the quest"];
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

        $result = self::upsertQuestPlayer($quest->id, $player->id, AppStatus::ONLINE->value);
        if ($result['error']) {
            return $result;
        }

        return $result;
    }

    /**
     *
     * @param Player $player reference to a Player object
     * @param Quest $quest
     * @param string $reason
     * @return array
     */
    public static function withdrawPlayerFromQuest(Player &$player, Quest &$quest, string $reason = null): array {

        // Player is already onboarded on a different quest => error
        if ($player->quest_id === null || $player->quest_id !== $quest->id) {
            return ['error' => true, 'message' => "player {$player->name} (id {$player->id}) is not in quest={$quest->id}, player->quest_id=" . ($player->quest_id ?? "null")];
        }

        $playerUpdate = self::updatePlayerQuestId($player, null);
        if ($playerUpdate['error']) {
            return $playerUpdate;
        }
        return self::upsertQuestPlayer($quest->id, $player->id, AppStatus::LEFT->value, $reason);
    }
}
