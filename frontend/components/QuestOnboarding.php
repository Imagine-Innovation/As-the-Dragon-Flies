<?php

namespace frontend\components;

use common\components\AppStatus;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\Player;
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
     * @param int $questId
     * @return string
     */
    public static function wellcomeMessage(int $questId): string {

        $count = self::playerCount($questId);

        $message = array_key_exists($count, self::WELLCOME_MESSAGES) ?
                self::WELLCOME_MESSAGES[$count] :
                self::DEFAULT_MESSAGE;

        return $message;
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

        // Check if player is selected
        if (!$player) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->No player is selected"];
        }

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
     * @param Player $player
     * @param Quest|null $quest
     * @return array
     */
    private static function isQuestValid(Player $player, Quest|null $quest = null): array {
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
     * @param Player $player
     * @param \common\models\Story $story
     * @return bool
     */
    private static function isPlayerLevelValid(Player $player, \common\models\Story $story): bool {
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
    private static function shouldAllowPlayerClass(Player $player, Quest $quest): bool {
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

    /**
     * Check of every required classes defined in the story
     * have onboarded in the quest
     *
     * @param Quest $quest
     * @return bool
     */
    public static function areRequiredClassesPresent(Quest $quest): bool {
        $requiredClasses = StoryClass::find()
                ->select('class_id')
                ->where(['story_id' => $quest->story_id])
                ->all();

        // If no specific class is required, consider that they are all present
        if (count($requiredClasses) === 0) {
            return true;
        }

        $presentClasses = Player::find()
                ->select('class_id')
                ->where(['quest_id' => $quest->id])
                ->all();

        return empty(array_diff($requiredClasses, $presentClasses));
    }

    public static function addPlayerToQuest(Player $player, Quest $quest): bool {

        // Player is already onboarded in the quest => do nothing
        if ($player->quest_id === $quest->id) {
            return true;
        }

        /*
          $player->quest_id = $quest->id;
          if (!$player->save()) {
          throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($player->errors, 0, false)));
          }

          $questPlayer = QuestPlayer::findOne(['quest_id' => $quest->id, 'player_id' => $player->id]);
         *
         */
        Player::updateAll(['quest_id' => $quest->id], ['id' => $player->id]);

        // Try to update potentially existing records
        $updates = QuestPlayer::updateAll(
                ['left_at' => null, 'reason' => null],
                ['quest_id' => $quest->id, 'player_id' => $player->id]
        );

        //if (!$questPlayer) {
        // If no record is actually updated, create a new entry
        if ($updates === 0) {
            $questPlayer = new QuestPlayer([
                'quest_id' => $quest->id,
                'player_id' => $player->id,
                'onboarded_at' => time()
            ]);

            if ($questPlayer->save()) {
                return true;
            }
            throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($questPlayer->errors, 0, false)));
        }

        return true;
    }

    public static function withdrawPlayer(Player $player, Quest $quest, string $reason = null): bool {

        // Player is already onboarded on a different quest => error
        if ($player->quest_id === null || $player->quest_id !== $quest->id) {
            Yii::debug("*** Debug *** withdrawPlayer - Player {$player->name} not in quest id {$quest->id}");
            return false;
        }

        /*
          $player->quest_id = null;
          if (!$player->save()) {
          throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($player->errors, 0, false)));
          }

          $questPlayer = QuestPlayer::findOne(['quest_id' => $quest->id, 'player_id' => $player->id]);

          if (!$questPlayer) {
          Yii::debug("*** Debug *** withdrawPlayer - QuestPlayer does not exist");
          return false;
          }
          $questPlayer->left_at = time();
          $questPlayer->reason = $reason ?? "Player's choice to leave";

          if ($questPlayer->save()) {
          Yii::debug("*** Debug *** withdrawPlayer - QuestPlayer successfully updated");
          return true;
          }

          throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($questPlayer->errors, 0, false)));
         *          *
         */
        Player::updateAll(['quest_id' => null], ['id' => $player->id]);

        $updates = QuestPlayer::updateAll(
                ['left_at' => time(), 'reason' => $reason ?? "Player's choice to leave"],
                ['quest_id' => $quest->id, 'player_id' => $player->id]
        );
        if ($updates === 0) {
            Yii::debug("*** Debug *** withdrawPlayer - QuestPlayer does not exist");
            return false;
        }
        return true;
    }
}
