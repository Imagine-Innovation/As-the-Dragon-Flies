<?php

namespace common\helpers;

use common\models\Action;
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

    protected static function searchParams(int|array $param): int|string {
        return is_int($param) ? "'{$param}'" : print_r($param, true);
    }

    public static function findModel(string $modelName, int|array $param) {
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

    public static function findAction(int|array $param): Action {
        return self::findModel('Action', $param);
    }

    public static function findChapter(int|array $param): Chapter {
        return self::findModel('Chapter', $param);
    }

    public static function findDecor(int|array $param): Decor {
        return self::findModel('Decor', $param);
    }

    public static function findMission(int|array $param): Mission {
        return self::findModel('Mission', $param);
    }

    public static function findPlayer(int|array $param): Player {
        return self::findModel('Player', $param);
    }

    public static function findQuest(int|array $param): Quest {
        return self::findModel('Quest', $param);
    }

    public static function findQuestAction(int|array $param): QuestAction {
        return self::findModel('QuestAction', $param);
    }

    public static function findQuestProgress(int|array $param): QuestProgress {
        return self::findModel('QuestProgress', $param);
    }

    public static function findReply(int|array $param): Reply {
        return self::findModel('Reply', $param);
    }
}
