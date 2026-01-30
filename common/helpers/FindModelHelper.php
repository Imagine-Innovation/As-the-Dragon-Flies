<?php

namespace common\helpers;

use common\models\Action;
use common\models\Chapter;
use common\models\Decor;
use common\models\Item;
use common\models\Mission;
use common\models\Player;
use common\models\PlayerBody;
use common\models\PlayerItem;
use common\models\Quest;
use common\models\QuestAction;
use common\models\QuestProgress;
use common\models\Race;
use common\models\Reply;
use common\models\Skill;
use common\models\Story;
use InvalidArgumentException;
use Yii;
use yii\web\NotFoundHttpException;

class FindModelHelper
{

    /**
     * Resolve a short model name or FQCN to a fully-qualified class name.
     *
     * @template T of \yii\db\ActiveRecord
     * @param class-string<T> $modelName The class name (e.g., Action::class or 'Action')
     * @return class-string<T>
     */
    private static function fullyQualifiedClassName(string $modelName): string {
        /** @var class-string<T> $className */
        $className = str_contains($modelName, '\\') ?
                $modelName :
                "\\common\\models\\{$modelName}";

        return $className;
    }

    /**
     * Get primary key columns declared by the model class.
     *
     * @template T of \yii\db\ActiveRecord
     * @param class-string<T> $className Fully qualified class name
     * @return array<string>
     * @throws InvalidArgumentException when primaryKey() returns empty
     */
    private static function getPrimaryKeyColumns(string $className): array {
        /** @var array<string> $pkColumns */
        $pkColumns = $className::primaryKey();
        if (empty($pkColumns)) {
            throw new InvalidArgumentException("Model {$className} does not declare primary key columns via primaryKey().");
        }
        return $pkColumns;
    }

    /**
     *
     * @param array<string, mixed> $param
     * @param array<string> $pkColumns
     * @return void
     * @throws InvalidArgumentException
     */
    private static function invalidArgumentException(array $param, array $pkColumns): void {
        $colums = empty($pkColumns) ? 'int' : implode(', ', $pkColumns);
        $dumpedParam = print_r($param, true);
        $errorMessage = "Expected [{$colums}] as primary key, {$dumpedParam} provided";
        throw new InvalidArgumentException($errorMessage);
    }

    /**
     *
     * @param int $param
     * @param array<string> $pkColumns
     * @return array<string, mixed>
     */
    private static function getValidPkIntParam(int $param, array $pkColumns): array {
        $intParam = ['id' => $param];
        if (count($pkColumns) !== 1 || $pkColumns[0] !== 'id') {
            self::invalidArgumentException($intParam, $pkColumns);
        }
        return $intParam;
    }

    /**
     *
     * @param array<string, mixed> $param
     * @param array<string> $pkColumns
     * @return array<string, mixed>
     */
    private static function getValidPkArrayParam(array $param, array $pkColumns): array {
        if (empty($pkColumns)) {
            // If there is no PK to check, return the parameter as is
            return $param;
        }

        // Basic test: do param and PK have the same number of keys?
        if (count($param) !== count($pkColumns)) {
            self::invalidArgumentException($param, $pkColumns);
        }

        /** @var array<string, int> */
        $validPkArrayParam = [];
        foreach ($param as $key => $value) {
            if (!in_array($key, $pkColumns, true) || !is_numeric($value)) {
                self::invalidArgumentException($param, $pkColumns);
            } else {
                $validPkArrayParam[$key] = (int) $value;
            }
        }
        return $validPkArrayParam;
    }

    /**
     * Returns a valid paramter array for a ClassName::finOne() function call
     *
     * @param int|array<string, mixed> $param
     * @param array<string> $pkColumns
     * @param bool|null $withPk
     * @return array<string, mixed>
     */
    private static function findOneFunctionParam(int|array $param, array $pkColumns, ?bool $withPk = true): array {
        if (is_numeric($param)) {
            return self::getValidPkIntParam((int) $param, $pkColumns);
        }
        return $withPk ? self::getValidPkArrayParam((array) $param, $pkColumns) : (array) $param;
    }

    /**
     * @template T of \yii\db\ActiveRecord
     * @param class-string<T> $modelName The class name (e.g., Action::class or 'Action')
     * @param int|array<string, mixed> $param
     * @param bool|null $withPk
     * @return T
     * @throws NotFoundHttpException
     */
    public static function findModel(string $modelName, int|array $param, ?bool $withPk = true): \yii\db\ActiveRecord {
        /** @var class-string<T> $className */
        $className = self::fullyQualifiedClassName($modelName);
        $pkColumns = $withPk ? self::getPrimaryKeyColumns($className) : [];
        $findOneFunctionParam = self::findOneFunctionParam($param, $pkColumns, $withPk);

        /** @var T|null $model */
        $model = $className::findOne($findOneFunctionParam);

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

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Story
     */
    public static function findStory(int|array $param): Story {
        return self::findModel(Story::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Skill
     */
    public static function findSkill(int|array $param): Skill {
        return self::findModel(Skill::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Item
     */
    public static function findItem(int|array $param): Item {
        return self::findModel(Item::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return PlayerItem
     */
    public static function findPlayerItem(int|array $param): PlayerItem {
        return self::findModel(PlayerItem::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return PlayerBody
     */
    public static function findPlayerBody(int|array $param): PlayerBody {
        return self::findModel(PlayerBody::class, $param);
    }

    /**
     *
     * @param int|array<string, mixed> $param
     * @return Race
     */
    public static function findRace(int|array $param): Race {
        return self::findModel(Race::class, $param);
    }
}
