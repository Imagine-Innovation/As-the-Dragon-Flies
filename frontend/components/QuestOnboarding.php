<?php

namespace frontend\components;

use common\models\Story;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\Player;
use common\models\events\EventFactory;
use frontend\components\QuestNotification;
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

    public static function findValidStory($storyId) {
        if ($storyId) {
            return Story::findOne(['id' => $storyId, 'status' => Story::STATUS_PUBLISHED]);
        }
        return null;
    }

    public static function findTavern($story) {

        if (!$story) {
            return null;
        }

        $tavern = $story->tavern;
        $questId = Yii::$app->session->get('questId');

        if ($questId && (!$tavern || $tavern->id !== $questId)) {
            Yii::debug("*** Debug *** findTavern  ===>  Tavern is not the current quest");
            return false;
        }

        if (!$tavern) {
            Yii::debug("*** Debug *** findTavern  ===>  Create a new Tavern");
            $tavern = self::newQuest($story->id);
            if (!$tavern) {
                return null;
            }
        }

        return $tavern;
    }

    private static function newQuest($storyId) {
        $quest = new Quest([
            'story_id' => $storyId,
            'status' => Quest::STATUS_WAITING,
            'created_at' => time(),
            'local_time' => time(),
        ]);
        if (!$quest->save()) {
            Yii::debug("*** Debug *** newQuest  ===>  Could not save new Quest");
            return null;
        }
        Yii::debug("*** Debug *** newQuest  ===>  new Quest is saved");
        return $quest;
    }

    private static function addQuestPlayer($questId, $playerId, $isInitiator = false) {
        $questPlayer = new QuestPlayer([
            'quest_id' => $questId,
            'player_id' => $playerId,
            'onboarded_at' => time(),
            'is_initiator' => ($isInitiator == true ? 1 : 0)
        ]);

        return $questPlayer->save();
    }

    public static function wellcomePlayer($player, $quest) {

        // Player is already onboarded in the quest => do nothing
        if ($player->quest_id === $quest->id) {
            return true;
        }

        $questPlayer = QuestPlayer::findOne(['quest_id' => $quest->id, 'player_id' => $player->id]);

        if (!$questPlayer) {
            $alreadyInQuest = self::playerCount($quest->id);
            $success = self::addQuestPlayer($quest->id, $player->id, $alreadyInQuest > 0 ? false : true);
            if (!$success) {
                return false;
            }
        }

        $player->quest_id = $quest->id;
        if (!$player->save()) {
            $questPlayer->delete();
            return false;
        }
        /*
          $message = 'Joined the quest "' . $quest->story->name . '"';
          QuestNotification::push('new-player', $quest->id, $player->id, $message, $player->id);
         *
         */
        $event = EventFactory::createEvent('new-player', $player, $quest);
        $event->process();
        return true;
    }

    public static function wellcomeMessage($questId) {

        $count = self::playerCount($questId);

        $builtinMessages = count(self::WELLCOME_MESSAGES);
        $message = $count < $builtinMessages ? self::WELLCOME_MESSAGES[$count] : "Boy, that's quite a team!";

        return $message;
    }

    public static function canPlayerJoinQuest($player, $quest) {

        $questStatus = self::isQuestValid($player, $quest);
        if ($questStatus['error']) {
            return $questStatus;
        }

        // Check if player is already in another quest
        if ($player->quest_id === $quest->id) {
            return ['denied' => true, 'reason' => 'canPlayerJoinQuest->' . $player->name . ' is already involved in another quest'];
        }

        $story = $quest->story;

        if (!self::isPlayerLevelValid($player, $story)) {
            return ['denied' => true, 'reason' => 'canPlayerJoinQuest->' . 'Player ' . $player->name . '\'s level (' . $player->level->name . ') is not within story requirements (' . $story->min_level . ' to ' . $story->max_level . ')'];
        }

        $playersCount = $quest->getCurrentPlayers()->count();
        if ($playersCount >= $story->max_players) {
            return ['denied' => true, 'reason' => 'canPlayerJoinQuest->' . 'Quest has reached maximum players (' . $story->max_players . ')'];
        }

        if (!self::shouldAllowPlayerClass($player, $quest)) {
            return ['denied' => true, 'reason' => 'canPlayerJoinQuest->' . 'Cannot welcome more players of class ' . $player->class->name];
        }

        return ['denied' => false, 'reason' => 'canPlayerJoinQuest->' . 'Player can join the quest'];
    }

    private static function isQuestValid($player, $quest) {
        // Check if player is selected
        if (!$player) {
            return ['error' => true, 'denied' => true, 'reason' => 'isQuestValid->' . 'You must select a player first'];
        }

        // Check if quest exists
        if (!$quest) {
            // Check if player is already in another quest
            if ($player->quest_id) {
                return ['error' => true, 'denied' => true, 'reason' => 'isQuestValid->' . $player->name . ' is already involved in another quest'];
            }
            return ['error' => true, 'denied' => false, 'reason' => 'isQuestValid->' . 'You can create a new quest'];
        }

        if ($quest->status !== Quest::STATUS_WAITING) {
            return ['error' => true, 'denied' => true, 'reason' => 'isQuestValid->' . 'Quest is not waiting to start anymore'];
        }

        return ['error' => false, 'denied' => false, 'reason' => 'isQuestValid->' . 'All clear'];
    }

    public static function canStartQuest($quest) {
        $story = $quest->story;
        $playersCount = $quest->getCurrentPlayers()->count();

        if ($playersCount < $story->min_players) {
            return ['denied' => true, 'reason' => 'canStartQuest->' . 'Not enough players to start the quest'];
        }

        if (!self::areRequiredClassesPresent($quest)) {
            return ['denied' => true, 'reason' => 'canStartQuest->' . 'Missing required player classes'];
        }

        $event = EventFactory::createEvent('start-quest', null, $quest);
        $event->process();
        return ['denied' => false, 'reason' => 'canStartQuest->' . 'Quest can be initiated'];
    }

    private static function isPlayerLevelValid($player, $story) {
        return $player->level_id >= $story->min_level && $player->level_id <= $story->max_level;
    }

    private static function shouldAllowPlayerClass($player, $quest) {
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

    private static function areRequiredClassesPresent($quest) {
        $story = $quest->story;
        //$requiredClasses = $story->getRequiredClasses();
        $storyClasses = $story->storyClasses;

        /*
          if (empty($requiredClasses)) {
          return true;
          }
         *
         */
        $requiredClasses = [];
        foreach ($storyClasses as $storyClass) {
            $requiredClasses[] = $storyClass->class_id;
        }

        $presentClasses = $quest->getPlayers()
                ->select('class_id')
                ->column();

        return empty(array_diff($requiredClasses, $presentClasses));
    }

    private static function playerCount($questId) {

        if ($questId) {
            $count = Player::find()
                    ->where(['quest_id' => $questId])
                    ->count();
        } else {
            $count = 0;
        }

        return $count;
    }
}
