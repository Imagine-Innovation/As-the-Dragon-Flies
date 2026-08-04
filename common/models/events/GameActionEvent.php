<?php

namespace common\models\events;

use common\components\AppStatus;
use common\helpers\LanguageHelper;
use common\models\Player;
use common\models\Quest;
use Yii;

/**
 * Event for game actions
 */
class GameActionEvent extends Event
{

    /** @var string The story language */
    public string $language;

    /** @var string The action type */
    public string $action;

    /** @var array<string, mixed> Additional action data */
    public array $detail;

    /**
     * Constructor
     *
     * @param Player $player The player who performed the action
     * @param Quest $quest The quest context
     * @param string $action The action type
     * @param array<string, mixed> $detail Additional action data
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, string $action, array $detail = [])
    {
        parent::__construct($sessionId, $player, $quest);
        $this->action = $action;
        $this->detail = $detail;
        $this->language = $this->quest->story->language ?? 'en';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType(): string
    {
        return 'game-action';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'New action';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string
    {
        /** @var AppStatus */
        $status = $this->detail['status'];

        $playerName = LanguageHelper::defaultName('Player', $this->player->name);

        return Yii::t('app/game', 'action status',
                        [
                            'playerName' => $playerName,
                            'actionName' => $this->action,
                            'status' => $status->name,
                        ],
                        $this->language
                );
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        $detail = $this->detail;
        $detail['timestamp'] = $this->timestamp;
        return [
            'playerName' => $this->player->name,
            'action' => $this->action,
            'questName' => $this->quest->name,
            'detail' => $detail,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void
    {
        Yii::debug('*** Debug *** GameActionEvent - process');

        $notification = $this->createNotification();
        $this->broadcast();

        /*
          // Dungeon master says hello
          $dungeonMaster = Player::findOne(1);
          if ($dungeonMaster) {
          $message = $this->getMessage();
          $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
          $sendingMessageEvent->process();
          }
         *
         */
    }
}
