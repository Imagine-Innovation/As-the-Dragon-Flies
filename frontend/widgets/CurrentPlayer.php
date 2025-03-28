<?php

namespace frontend\widgets;

use yii\base\Widget;
use common\models\Player;

class CurrentPlayer extends Widget {

    public $user;
    public $mode;

    public function run() {
        $currentUser = $this->user;
        $displayMode = $this->mode ?? 'navbar';
        $render = $displayMode == 'navbar' ? 'current-player-navbar' : 'current-player-modal';

        $selectedPlayerId = $currentUser->current_player_id ? $currentUser->currentPlayer->id : null;

        $players = $this->loadPlayers($currentUser->id);
        if ($players) {
            return $this->render($render, [
                        'players' => $players,
                        'selectedPlayerId' => $selectedPlayerId,
                        'user_id' => $currentUser->id,
            ]);
        } else {
            $html = $displayMode == 'navbar' ? '' : $this->render('current-player-empty');
            return $html;
        }
    }

    private function loadPlayers($userId) {
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

    private function setTooltip($player) {
        $genders = ['F' => 'female', 'M' => 'male'];

        return $genders[$player->gender] . ' '
                . mb_strtolower($player->race->name) . ' '
                . mb_strtolower($player->class->name);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    private function getPlayers($userId) {
        return Player::find()
                        ->with(['class', 'race'])
                        ->where([
                            'user_id' => $userId,
                            'status' => Player::STATUS_ACTIVE,
                        ])
                        ->All();
    }
}
