<?php

namespace common\helpers;

use common\models\Action;
use common\models\ActionFlow;
use common\models\Chapter;
use common\models\Decor;
use common\models\Mission;
use common\models\Player;
use common\models\Quest;
use common\models\QuestAction;
use common\models\QuestProgress;
use common\models\Reply;
use Yii;
use yii\web\NotFoundHttpException;

class FindModelHelper
{

    /**
     *
     * @param int|array<string, mixed> $param
     * @return string
     */
    protected static function searchParams(int|array $param): string {
        return is_int($param) ? "'{$param}'" : print_r($param, true);
    }

    /**
     *
     * @param string $modelName
     * @param int|array<string, mixed> $param
     * @return Action|Chapter|Decor|Mission|Player|Quest|QuestAction|QuestProgress|Reply|ActionFlow
     * @throws NotFoundHttpException
     */
    public static function findModel(string $modelName, int|array $param): Action|Chapter|Decor|Mission|Player|Quest|QuestAction|QuestProgress|Reply|ActionFlow {
        $searchParams = self::searchParams($param);
        Yii::debug("*** debug *** findModel modelName={$modelName}, param={$searchParams}");

        // test if a primary key is used (integer value or 'id' field)
        if (is_int($param)) {
            $pk = $param;
        } else {
            $pk = $param['id'] ?? null;
        }

        $activeRecord = "\\common\\models\\{$modelName}";
        $model = $activeRecord::findOne($pk ? ['id' => $pk] : $param);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException("The requested {$modelName} does not exist with search params: {$searchParams}");
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Action
     */
    public static function findAction(int|array $param): Action {
        return self::findModel('Action', $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Chapter
     */
    public static function findChapter(int|array $param): Chapter {
        return self::findModel('Chapter', $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Decor
     */
    public static function findDecor(int|array $param): Decor {
        return self::findModel('Decor', $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Mission
     */
    public static function findMission(int|array $param): Mission {
        return self::findModel('Mission', $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Player
     */
    public static function findPlayer(int|array $param): Player {
        return self::findModel('Player', $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Quest
     */
    public static function findQuest(int|array $param): Quest {
        return self::findModel('Quest', $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return QuestAction
     */
    public static function findQuestAction(int|array $param): QuestAction {
        return self::findModel('QuestAction', $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return QuestProgress
     */
    public static function findQuestProgress(int|array $param): QuestProgress {
        return self::findModel('QuestProgress', $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Reply
     */
    public static function findReply(int|array $param): Reply {
        return self::findModel('Reply', $param);
    }
}
