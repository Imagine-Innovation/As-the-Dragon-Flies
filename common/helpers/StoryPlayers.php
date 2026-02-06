<?php

namespace common\helpers;

use common\models\Player;
use common\models\Quest;
use common\models\Story;

class StoryPlayers
{
    /**
     *
     * @param Story $story
     * @param Player[] $players
     * @return string
     */
    public static function exists(Story $story, array $players): string
    {
        $html = $story->tavern ? self::playerList($story->tavern, $players) : '';
        return $html;
    }

    /**
     *
     * @param Quest $quest
     * @param Player[] $players
     * @return string
     */
    private static function playerList(Quest $quest, array $players): string
    {
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
            return (
                '<h6 class="card-subtitle">Your player'
                . ($n > 1 ? 's ' : ' ')
                . implode(' and ', $playerNames)
                . ' '
                . ($n > 1 ? ' are ' : ' is ')
                . 'already waiting to start the quest</h6>'
            );
        }
        return '';
    }
}
