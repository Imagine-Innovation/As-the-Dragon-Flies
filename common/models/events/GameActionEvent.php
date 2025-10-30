<?php

namespace common\models\events;

use common\models\Player;
use common\models\Quest;
use Yii;

/**
 * Event for game actions
 */
class GameActionEvent extends Event
{

    /**
     * @var string The action type
     */
    public $action;

    /**
     * @var array Additional action data
     */
    public $outcomes;

    /**
     * Constructor
     *
     * @param Player $player The player who performed the action
     * @param Quest $quest The quest context
     * @param string $action The action type
     * @param array $outcomes Additional action data
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, $action, $outcomes = []) {
        parent::__construct($sessionId, $player, $quest);
        $this->action = $action;
        $this->outcomes = $outcomes;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string {
        return 'game-action';
    }

    public function getTitle(): string {
        return 'New action';
    }

    public function getMessage(): string {
        return "{$this->player->name} did the action {$this->action}";
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload(): array {
        return [
            'playerId' => $this->player->id,
            'playerName' => $this->player->name,
            'action' => $this->action,
            'outcomes' => $this->outcomes,
            'questId' => $this->quest->id,
            'questName' => $this->quest->name,
            //'timestamp' => date('Y-m-d H:i:s', $this->timestamp)
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(): void {
        Yii::debug("*** Debug *** GameActionEvent - process");
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            $message = "Player {$this->playerName} made the action \"{$this->action}\"";
            $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }

    public function xxxprocess(): void {
        // Process specific game actions using match expression
        match ($this->action) {
            'start-quest' => $this->processStartQuest(),
            'complete-quest' => $this->processCompleteQuest(),
            'move' => $this->processPlayerMovement(),
            'attack' => $this->processPlayerAttack(),
            'search' => $this->processPlayerSearch(),
            'rest' => $this->processPlayerRest(),
            default => null, // No action for unrecognized actions
        };

        // The broadcasting is handled by the EventHandler
    }

    /**
     * Process start quest action
     */
    private function processStartQuest() {
        $this->quest->status = 'playing';
        $this->quest->started_at = time();
        $this->quest->save();
    }

    /**
     * Process complete quest action
     */
    private function processCompleteQuest() {
        $this->quest->status = 'completed';
        $this->quest->completed_at = time();
        $this->quest->save();
    }

    /**
     * Process player movement action
     */
    private function processPlayerMovement() {
        // Handle player movement
        // This would update player position in a real implementation
    }

    /**
     * Process player attack action
     */
    private function processPlayerAttack() {
        // Handle player attack
        // This would calculate damage, etc. in a real implementation
    }

    /**
     * Process player search action
     */
    private function processPlayerSearch() {
        // Handle player search
        // This would determine what the player finds in a real implementation
    }

    /**
     * Process player rest action
     */
    private function processPlayerRest() {
        // Handle player rest
        // This would restore health, etc. in a real implementation
    }
}
