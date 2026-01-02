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
     * @template T of \yii\db\ActiveRecord
     * @param class-string<T> $modelName The class name (e.g., Action::class or 'Action')
     * @param int|array<string, mixed> $param
     * @return T
     * @throws NotFoundHttpException
     */
    public static function findModel(string $modelName, int|array $param): \yii\db\ActiveRecord {
        $searchParams = self::searchParams($param);
        Yii::debug("*** debug *** findModel modelName={$modelName}, param={$searchParams}");

        // Resolve the FQCN (Fully Qualified Class Name)
        /** @var class-string<T> $className */
        $className = str_contains($modelName, '\\') ? $modelName : "\\common\\models\\{$modelName}";

        // test if a primary key is used (integer value or 'id' field)
        $pk = is_int($param) ? $param : ($param['id'] ?? null);

        /** @var T|null $model */
        $model = $className::findOne($pk ? ['id' => $pk] : $param);

        if ($model instanceof $className) {
            return $model;
        }

        throw new NotFoundHttpException("The requested {$modelName} does not exist.");
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Action
     */
    public static function findAction(int|array $param): Action {
        return self::findModel(Action::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Chapter
     */
    public static function findChapter(int|array $param): Chapter {
        return self::findModel(Chapter::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Decor
     */
    public static function findDecor(int|array $param): Decor {
        return self::findModel(Decor::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Mission
     */
    public static function findMission(int|array $param): Mission {
        return self::findModel(Mission::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Player
     */
    public static function findPlayer(int|array $param): Player {
        return self::findModel(Player::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Quest
     */
    public static function findQuest(int|array $param): Quest {
        return self::findModel(Quest::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return QuestAction
     */
    public static function findQuestAction(int|array $param): QuestAction {
        return self::findModel(QuestAction::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return QuestProgress
     */
    public static function findQuestProgress(int|array $param): QuestProgress {
        return self::findModel(QuestProgress::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Reply
     */
    public static function findReply(int|array $param): Reply {
        return self::findModel(Reply::class, $param);
    }
}
