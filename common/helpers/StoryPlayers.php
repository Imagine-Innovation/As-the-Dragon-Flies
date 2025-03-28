<?php

namespace common\helpers;

class StoryPlayers {

    public static function exists($story, $players) {
        $html = ($story->tavern) ? self::playerList($story->tavern, $players) : "";
        return $html;
    }

    private static function playerList($quest, $players) {
        $playerNames = [];
        foreach ($quest->questPlayers as $questPlayer) {
            foreach ($players as $player) {
                if ($questPlayer->player_id === $player->id) {
                    $playerNames[] = $player->name;
                }
            }
        }
        $n = count($playerNames);
        if ($n > 0) {
            return '<h6 class="card-subtitle">Your player' .
                    ($n > 1 ? 's ' : ' ') . implode(" and ", $playerNames) . ' ' .
                    ($n > 1 ? ' are ' : ' is ') . 'already waiting to start the quest</h6>';
        }
        return "";
    }
}
