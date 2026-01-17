<?php

namespace common\components\gameplay;

use common\components\AppStatus;
use common\components\ContextManager;
use common\models\CharacterClass;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\Player;
use common\models\Story;
use common\models\StoryClass;
use Yii;
use yii\web\NotFoundHttpException;
use Exception;
use RuntimeException;

class TavernManager extends BaseManager
{

    public ?Story $story = null;
    public ?Quest $quest = null;

    /**
     *
     * @param array<string, mixed> $config
     */
    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        if ($this->quest) {
            $this->story = $this->quest->story;
        }
    }

    /**
     *
     * @return Quest
     * @throws RuntimeException
     */
    private function getQuest(): Quest {
        if ($this->quest === null) {
            throw new RuntimeException("TavernManager context error: Quest is missing.");
        }
        return $this->quest;
    }

    /**
     *
     * @return Story
     * @throws RuntimeException
     */
    private function getStory(): Story {
        if ($this->story === null) {
            throw new RuntimeException("TavernManager context error: Story is missing.");
        }
        return $this->story;
    }

    /**
     * Returns a wellcone message for a new joiner
     *
     * @param int $playerCount Number of already on boarded players
     * @return string Welcome message
     */
    public function welcomeMessage(int $playerCount): string {
        return match ($playerCount) {
            0 => 'There\'s nobody here!',
            1 => 'For the moment, it looks like you\'re the first one',
            2 => 'Look, there are two of you now',
            3 => 'Ah! but there are three of you! Wait, I\'ll get a chair',
            4 => 'Now that there are four of you, I\'m going to put you on a bigger table',
            5 => 'With five guys like you, it\'s going to be quite a team!',
            default => 'Boy, that\'s quite a team!'
        };
    }

    /**
     * Returns a message given the missing number of players before the quest can start
     *
     * @param ?int $playerCount Number of already on boarded players, null when quest is beeing created
     * @return string Missing player message
     */
    public function missingPlayers(?int $playerCount = null): string {
        $quest = $this->getQuest();
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
     * @return string|null Missing classes message, null if no class is required
     */
    public function missingClasses(): ?string {
        $missingClassIds = $this->getMissingClassIds();

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
     * @return array<int>|null Missing class IDs, null if bo class is required
     */
    private function getMissingClassIds(): ?array {
        // Get the required classes
        $quest = $this->getQuest();
        $requiredClassIds = $this->getRequiredClassIds($quest->story_id);

        // If no specific class is required, consider that they are all present
        if (!$requiredClassIds) {
            return null;
        }

        // Fetch the actual player classes
        $actualPlayerClasses = $this->getActualPlayerClassIds($quest->id);

        return array_diff($requiredClassIds, $actualPlayerClasses);
    }

    /**
     * Check if a player can join a quest.
     *
     * @param Player $player
     * @return array{
     *     denied: bool,
     *     reason: string
     * } Associative array with a deny status and the reason why
     */
    public function canPlayerJoinQuest(Player &$player): array {
        // Check if the player is eligible to join a quest
        $playerStatus = $this->isPlayerValid($player);
        if ($playerStatus['error']) {
            return $playerStatus;
        }

        $quest = $this->quest; // quest can be null. Don't call the getter getQuest()
        $story = $this->story;

        // Check if the quest is a valid one
        $questStatus = $this->isQuestValid();
        if ($questStatus['error']) {
            return $questStatus;
        }

        if (!$this->isPlayerLevelValid($player)) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Player {$player->name}'s level ({$player->level->name}) is not within story requirements ({$story?->min_level} to {$story?->max_level})"];
        }

        $playerCount = $quest?->getCurrentPlayers()->count();
        if ($playerCount >= $story?->max_players) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Quest has reached maximum players ({$story?->max_players})"];
        }

        if (!$this->isPlayerClassValid($player)) {
            return ['denied' => true, 'reason' => "canPlayerJoinQuest->Cannot welcome more players of class {$player->class->name}"];
        }

        return ['denied' => false, 'reason' => "canPlayerJoinQuest->Player {$player->name} can join the quest"];
    }

    /**
     * Check if a quest is valid
     *
     * @return array{error: bool, denied: bool, reason: string} Associative array with an error status, a deny status and the reason why
     */
    private function isQuestValid(): array {
        // Check if quest exists
        if (!$this->quest) {
            return ['error' => true, 'denied' => false, 'reason' => "canPlayerJoinQuest->You can create a new quest"];
        }

        if ($this->quest->status !== AppStatus::WAITING->value) {
            return ['error' => true, 'denied' => true, 'reason' => "canPlayerJoinQuest->Quest is not waiting to start anymore"];
        }

        return ['error' => false, 'denied' => false, 'reason' => "canPlayerJoinQuest->Quest #{$this->quest->id} is valid quest"];
    }

    /**
     * Check if a player is valid to join a new quest
     *
     * @param Player|null $player reference to a Player object
     * @return array{error: bool, denied: bool, reason: string} Associative array with an error status, a deny status and the reason why
     */
    private function isPlayerValid(?Player $player): array {
        // Check if player is selected
        if (!$player) {
            return ['error' => true, 'denied' => true, 'reason' => "isPlayerValid->No player is selected"];
        }

        // Check if player is in another quest
        if ($this->quest === null) {
            if ($player->quest_id) {
                Yii::debug("*** Debug *** isPlayerValid - quest is null and player->quest_id is {$player->quest_id}");
                return ['error' => true, 'denied' => true, 'reason' => "isPlayerValid->Player {$player->name} is already involved in another quest"];
            }
        } elseif ($player->quest_id && $player->quest_id !== $this->quest->id) {
            Yii::debug("*** Debug *** isPlayerValid - quest is not null but player->quest_id is {$player->quest_id} <> quest->id {$this->quest->id}");
            return ['error' => true, 'denied' => true, 'reason' => "isPlayerValid->Player {$player->name} is already involved in another quest"];
        }

        return ['error' => false, 'denied' => false, 'reason' => "isPlayerValid->Player {$player->name} is eligible to join a quest"];
    }

    /**
     * Check is the player's level is compatible with the story requirements
     *
     * @param Player $player reference to a Player object
     * @return bool
     */
    private function isPlayerLevelValid(Player &$player): bool {
        $story = $this->getStory();
        return $story->min_level <= $player->level_id && $player->level_id <= $story->max_level;
    }

    /**
     * Check is the player's class is allowed and fulfills the story requirements.
     * If nothing is specified, every class can join.
     *
     * @param Player $player reference to a Player object
     * @return bool
     */
    private function isPlayerClassValid(Player &$player): bool {
        $quest = $this->getQuest();
        $requiredClassIds = $this->getRequiredClassIds($quest->story_id);

        // If no specific class is required, consider that they are all present
        if (count($requiredClassIds) === 0) {
            return true;
        }

        $actualPlayerClasses = $this->getActualPlayerClassIds($quest->id);

        // If player's class is required and not yet present
        if (in_array($player->class_id, $requiredClassIds) && !in_array($player->class_id, $actualPlayerClasses)) {
            return true;
        }

        // Check if we need to reserve slots for missing required classes
        $remainingSlots = $quest->story->max_players - $this->getCurrentPlayerCount();
        $missingClassesCount = count(array_diff($requiredClassIds, $actualPlayerClasses));

        return $remainingSlots > $missingClassesCount;
    }

    /**
     * Check of every required classes defined in the story
     * have onboarded in the quest
     *
     * @return bool
     */
    public function areRequiredClassesPresent(): bool {
        // Get the missing classes
        $missingClassIds = $this->getMissingClassIds();

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
     * @return array<int>
     */
    private function getRequiredClassIds(int $storyId): array {
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
     * @return array<int>
     */
    private function getActualPlayerClassIds(int $questId): array {
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
     * @return int
     */
    private function getCurrentPlayerCount(): int {
        $quest = $this->getQuest();
        $playerCount = Player::find()
                ->where(['quest_id' => $quest->id])
                ->count();

        /** @phpstan-ignore-next-line */
        return $playerCount;
    }

    /**
     * Update the player's quest_id
     *
     * @param Player $player reference to a Player object
     * @param int|null $questId ID of the quest in which the player is engaged. Null if he leaves the quest
     * @return array{error: bool, message: string}
     */
    private function updatePlayerQuestId(Player &$player, ?int $questId = null): array {
        $player->quest_id = $questId;
        if (!$player->save()) {
            return ['error' => true, 'message' => 'Could not save Player : ' . implode('\n', \yii\helpers\ArrayHelper::getColumn($player->errors, 0, false))];
        }
        ContextManager::updateQuestContext($questId);
        return ['error' => false, 'message' => "Player's quest_id updated to {$questId}"];
    }

    /**
     *
     * @param int $questId
     * @return int
     */
    private function getNextPlayerTurn(int $questId): int {
        $nextTurn = QuestPlayer::find()
                ->where(['quest_id' => $questId])
                ->max('player_turn');
        return is_scalar($nextTurn) ? (int) $nextTurn + 1 : 1;
    }

    /**
     * Update or Insert a new QuestPlayer entry
     *
     * @param int $questId ID of the quest
     * @param int $playerId ID of the player
     * @param int $status Status of the player in the quest
     * @param string|null $reasonWhyPlayerQuit Reason why the player left the quest, null when inserting a new entry
     * @return array{error: bool, message: string}
     */
    private function upsertQuestPlayer(int $questId, int $playerId, int $status, ?string $reasonWhyPlayerQuit = null): array {
        $questPlayer = QuestPlayer::findOne(['quest_id' => $questId, 'player_id' => $playerId]);

        if (!$questPlayer) {
            // If no record is previously existing, create a new entry
            $questPlayer = new QuestPlayer([
                'quest_id' => $questId,
                'player_id' => $playerId,
                'onboarded_at' => time(),
                'player_turn' => $this->getNextPlayerTurn($questId),
                'status' => $status,
            ]);
        }

        $questPlayer->left_at = $reasonWhyPlayerQuit ? time() : null;
        $questPlayer->reason = $reasonWhyPlayerQuit;

        if ($questPlayer->save()) {
            return ['error' => false, 'message' => 'Player successfully ' . ($reasonWhyPlayerQuit ? 'left' : 'joined') . ' on the quest'];
        }
        return ['error' => true, 'message' => 'Could not save QuestPlayer : ' . implode("\n", \yii\helpers\ArrayHelper::getColumn($questPlayer->errors, 0, false))];
    }

    /**
     * Add a player to a quest
     *
     * @param Player $player reference to a Player object
     * @return array{error: bool, message: string}
     */
    public function addPlayerToQuest(Player &$player): array {
        $quest = $this->getQuest();

        if ($player->quest_id === $quest->id) {
            return ['error' => false, 'message' => "Player is already onboarded in the quest"];
        }

        $playerUpdate = $this->updatePlayerQuestId($player, $quest->id);
        if ($playerUpdate['error']) {
            return $playerUpdate;
        }

        $result = $this->upsertQuestPlayer($quest->id, $player->id, AppStatus::ONLINE->value);
        if ($result['error']) {
            return $result;
        }

        return $result;
    }

    /**
     *
     * @param Player $player reference to a Player object
     * @param string $reason
     * @return array{error: bool, message: string}
     */
    public function withdrawPlayerFromQuest(Player &$player, string $reason = null): array {
        $quest = $this->getQuest();

        // Player is already onboarded on a different quest => error
        if ($player->quest_id === null || $player->quest_id !== $quest->id) {
            return ['error' => true, 'message' => "player {$player->name} (id {$player->id}) is not in quest={$quest->id}, player->quest_id=" . ($player->quest_id ?? "null")];
        }

        $playerUpdate = $this->updatePlayerQuestId($player, null);
        if ($playerUpdate['error']) {
            return $playerUpdate;
        }
        return $this->upsertQuestPlayer($quest->id, $player->id, AppStatus::LEFT->value, $reason);
    }

    /**
     *
     * @return Quest
     * @throws Exception
     */
    private function newTavern(): Quest {
        Yii::debug("*** Debug *** findTavern  ===>  Create a new Tavern");
        $story = $this->getStory();
        $newTavern = new Quest([
            'story_id' => $story->id,
            'initiator_id' => Yii::$app->session->get('playerId'),
            'current_chapter_id' => $story->firstChapter?->id,
            'name' => $story->name,
            'description' => $story->description,
            'image' => $story->image,
            'status' => AppStatus::WAITING->value,
            'created_at' => time(),
            'local_time' => time(),
        ]);

        if (!$newTavern->save()) {
            throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($newTavern->errors, 0, false)));
        }

        return $newTavern;
    }

    /**
     *
     * @return Quest
     * @throws NotFoundHttpException
     */
    public function findTavern(): Quest {

        $tavern = $this->story->tavern ?? null;
        $questId = Yii::$app->session->get('questId');

        if ($questId && (!$tavern || $tavern->id !== $questId)) {
            throw new NotFoundHttpException("Tavern is not the current quest for story {$this->story?->name}");
        }

        $foundTavern = $tavern ?? $this->newTavern();

        // Update the current context
        $this->quest = $foundTavern;
        return $foundTavern;
    }

    /**
     *
     * @param int $playerId
     * @return array{canStart: bool, msg: string}
     */
    public function questCanStart(int $playerId): array {
        $quest = $this->getQuest();
        Yii::debug("*** debug *** - questCanStart - questId={$quest->id}, initiatorId={$quest->initiator_id}, playerId={$playerId}");
        if ($playerId !== $quest->initiator_id) {
            return ['canStart' => false, 'msg' => "Your are the quest initiator"];
        }

        if ($quest->status !== AppStatus::WAITING->value) {
            return ['canStart' => false, 'msg' => "Quest {$quest->name} is not in wating state."];
        }

        $playersCount = $this->getCurrentPlayerCount();
        $story = $this->getStory();

        if ($playersCount < $story->min_players) {
            return ['canStart' => false, 'msg' => "Quest can start once {$story->min_players} joined. Current count is {$playersCount}"];
        }

        if (!$this->areRequiredClassesPresent()) {
            return ['canStart' => false, 'msg' => "Missing required player classes"];
        }

        return ['canStart' => true, 'msg' => "Quest can start", 'questName' => $quest->name, 'questId' => $quest->id];
    }
}
