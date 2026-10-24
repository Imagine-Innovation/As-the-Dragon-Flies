<?php

namespace common\helpers;

use Yii;

final class LocalizeMessageHelper
{

    /**
     *
     * @param string|null $currentPlayerName
     * @param string|null $currentMissionName
     * @param string|null $nextPlayerName
     * @param string|null $nextMissionName
     * @return array{current: string, other: string}
     */
    public static function nextMissionToastMessage(
            ?string $currentPlayerName,
            ?string $currentMissionName,
            ?string $nextPlayerName,
            ?string $nextMissionName): array
    {
        $currentString = Yii::t('app/game', '{playerName} completed {missionName}. Your turn: {nextMissionName}', [
            'playerName' => $currentPlayerName ?? '',
            'missionName' => $currentMissionName ?? '',
            'nextMissionName' => $nextMissionName ?? '',
        ]);

        $otherString = Yii::t('app/game', '{playerName} completed {missionName}. {nextPlayerName} turn: {nextMissionName}', [
            'playerName' => $currentPlayerName ?? '',
            'missionName' => $currentMissionName ?? '',
            'nextPlayerName' => $nextPlayerName ?? '',
            'nextMissionName' => $nextMissionName ?? '',
        ]);

        return [
            'current' => $currentString,
            'other' => $otherString,
        ];
    }

    /**
     *
     * @param string|null $oldPlayerName
     * @param string|null $nextPlayerName
     * @return array{current: string, other:string}
     */
    public static function nextPlayerToastMessage(?string $oldPlayerName, ?string $nextPlayerName): array
    {
        $currentString = Yii::t('app/game', '{playerName} finished turn. Your turn to play', [
            'playerName' => $oldPlayerName ?? '',
        ]);

        $otherString = Yii::t('app/game', '{playerName} finished turn. {nextPlayerName} turn', [
            'playerName' => $oldPlayerName ?? '',
            'nextPlayerName' => $nextPlayerName ?? '',
        ]);

        return [
            'current' => $currentString,
            'other' => $otherString,
        ];
    }

    /**
     *
     * @param string|null $playerName
     * @param string|null $questName
     * @param string $status
     * @return array{current: string, other: string}
     */
    public static function gameOverToastMessage(?string $playerName, ?string $questName, string $status): array
    {
        // ${detail.playerName} has ended quest “${detail.questName}” with status ${detail.status}
        $currentString = Yii::t('app/game', 'You ended {questName} with status {status}', [
            'questName' => $questName ?? '',
            'status' => $status,
        ]);

        $otherString = Yii::t('app/game', '{playerName} ended {questName} with status {status}', [
            'playerName' => $playerName ?? '',
            'questName' => $questName ?? '',
            'status' => $status,
        ]);

        return [
            'current' => $currentString,
            'other' => $otherString,
        ];
    }

    /**
     *
     * @param string|null $nextPlayerName
     * @return array{current: string, other: string}
     */
    public static function nextTurnMessage(?string $nextPlayerName): array
    {
        $turnCurrentString = Yii::t('app/game', 'Your turn to play');
        $turnOtherString = Yii::t('app/game', '{nextPlayerName} turn to play', [
            'nextPlayerName' => $nextPlayerName ?? ''
                ]
        );

        return [
            'current' => $turnCurrentString,
            'other' => $turnOtherString,
        ];
    }
}
