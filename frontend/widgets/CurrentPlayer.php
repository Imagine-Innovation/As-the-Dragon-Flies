<?php

namespace frontend\widgets;

use yii\base\Widget;
use common\components\AppStatus;
use common\models\Player;
use common\models\User;

class CurrentPlayer extends Widget
{

    public User $user;
    public ?string $mode = null;

    /**
     *
     * @return string
     */
    public function run(): string {
        $currentUser = $this->user;
        $displayMode = $this->mode ?? 'navbar';
        $render = ($displayMode === 'navbar') ? 'current-player-navbar' : 'current-player-modal';

        $selectedPlayerId = $currentUser?->current_player_id;

        $players = $this->loadPlayers($currentUser->id);
        if ($players) {
            return $this->render($render, [
                        'players' => $players,
                        'selectedPlayerId' => $selectedPlayerId,
                        'userId' => $currentUser->id,
            ]);
        } else {
            $html = $displayMode == 'navbar' ? '' : $this->render('current-player-empty');
            return $html;
        }
    }

    /**
     *
     * @param int $userId
     * @return array<string, mixed>
     */
    private function loadPlayers(int $userId): array {
        $players = $this->getPlayers($userId);
        $data = [];
        foreach ($players as $player) {
            $names = explode(' ', $player->name);
            if (count($names) > 1) {
                $initial = $names[0][0] . $names[1][0];
            } else {
                $initial = $player->name[0];
            }

            $data[$player->id] = [
                'id' => $player->id,
                'name' => $player->name,
                'initial' => $initial,
                'tooltip' => $this->setTooltip($player),
            ];
        }
        return $data;
    }

    /**
     *
     * @param Player $player
     * @return string
     */
    private function setTooltip(Player &$player): string {
        $genders = ['F' => 'female', 'M' => 'male'];

        return $genders[$player->gender] . ' '
                . mb_strtolower($player->race->name) . ' '
                . mb_strtolower($player->class->name);
    }

    /**
     * Gets query for [[Players]].
     *
     * @param int $userId
     * @return Player[]
     */
    private function getPlayers(int $userId): array {
        return Player::find()
                        ->with(['class', 'race'])
                        ->where([
                            'user_id' => $userId,
                            'status' => AppStatus::ACTIVE->value,
                        ])
                        ->All();
    }
}
