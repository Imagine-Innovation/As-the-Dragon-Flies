<?php

namespace frontend\controllers;

use common\helpers\FileHelper;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * MissionController implements the CRUD actions for Mission model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class SearchController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['*'], 'allow' => false, 'roles' => ['?']],
                    ['actions' => ['values'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
        ]);
    }

    /**
     *
     * @param string $search
     * @return array<string>
     */
    private function setFileSearchFilter(string $search): array
    {
        $extensions = ['.png', '.jpg', '.jpeg', '.gif'];
        $filter = [];
        foreach ($extensions as $extension) {
            $filter[] = "*{$search}*{$extension}";
        }
        return $filter;
    }

    /**
     *
     * @param string $search
     * @param string|null $folder
     * @return array{error: bool, msg: string, content?: string}
     */
    private function imageSearch(string $search, ?string $folder = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $path = Yii::getAlias('@frontend/web/') . ($folder ?? 'invalid');
        $results = [];

        if (!is_dir($path)) {
            Yii::debug("*** debug *** - actionimage - '{$path}' is not a valid directory");
            return ['error' => true, 'msg' => "'{$path}' is not a valid directory"];
        }
        $files = \yii\helpers\FileHelper::findFiles($path, [
            'only' => $this->setFileSearchFilter($search),
            'recursive' => false,
            'caseSensitive' => false,
        ]);

        foreach ($files as $file) {
            $fileName = basename($file);
            $results[] = [
                'id' => $fileName,
                'img' => "/frontend/web/{$folder}/{$fileName}",
                'text' => FileHelper::removeExtension($fileName),
            ];
        }
        return ['error' => false, 'msg' => '', 'results' => $results];
    }

    /**
     *
     * @param string|null $inputString
     * @return string|null
     */
    private function normalizeSearchString(?string $inputString): ?string
    {
        if (!$inputString) {
            return null;
        }

        $normalizedString = str_replace(
                [
                    "'", // Single quote
                    '’', // Right single quotation mark
                    '‘', // Left single quotation mark
                    '´', // Acute accent
                    '`', // Grave accent
                ],
                '_', // single character SQL wildcard
                $inputString,
        );
        return $normalizedString;
    }

    /**
     * Resolve a short model name or FQCN to a fully-qualified class name.
     *
     * @param string $modelName The class name in short or fully qualified form
     * @return class-string<\yii\db\ActiveRecord>
     */
    private function fullyQualifiedClassName(string $modelName): string
    {
        $className = str_contains($modelName, '\\') ? $modelName : "\\common\\models\\{$modelName}";

        if (!class_exists($className) || !is_subclass_of($className, \yii\db\ActiveRecord::class)) {
            throw new \InvalidArgumentException("Class {$className} does not exist or is not an ActiveRecord.");
        }
        return $className;
    }

    /**
     *
     * @param string $modelName
     * @param string|null $userEntry
     * @param int|null $missionId
     * @return array{error: bool, msg: string, results?: array<int, array<string, mixed>>}
     */
    private function searchInDecor(string $modelName, ?string $userEntry, ?int $missionId = 0): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $searchString = $this->normalizeSearchString($userEntry);

        $className = $this->fullyQualifiedClassName($modelName);
        $query = $className::find()
                ->select(['t.id', 't.name', 't.description as text'])
                ->from(['t' => $className::tableName()]);

        if ($searchString) {
            $query->where(['like', 'name', "%{$searchString}%", false])->orWhere(['like', 'description', "%{$searchString}%", false]); // The 'false' parameter prevents Yii from adding extra escaping
        }
        $query->innerJoin('decor', 't.decor_id = decor.id')->where(['decor.mission_id' => $missionId]);

        $searchResult = $query->asArray()->all();

        /** @phpstan-ignore-next-line */
        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    /**
     *
     * @param string $modelName
     * @param string|null $userEntry
     * @param array<string, int|null>|null $filter
     * @return array{error: bool, msg: string, results?: mixed}
     */
    private function genericSearch(string $modelName, ?string $userEntry, ?array $filter = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $searchString = $this->normalizeSearchString($userEntry);

        $className = $this->fullyQualifiedClassName($modelName);
        $query = $className::find()->select(['id', 'name', 'description as text']);

        if ($searchString) {
            $query->where(['like', 'name', "%{$searchString}%", false])->orWhere(['like', 'description', "%{$searchString}%", false]); // The 'false' parameter prevents Yii from adding extra escaping
        }
        if ($filter) {
            $query->where($filter);
        }

        $searchResult = $query->asArray()->all();

        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    /**
     *
     * @param string $modelName
     * @param string $search
     * @return array{error: bool, msg: string, results?: mixed}
     */
    private function searchInTextColumn(string $modelName, string $search): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $searchString = $this->normalizeSearchString($search);

        $className = $this->fullyQualifiedClassName($modelName);
        $searchResult = $className::find()
                ->select(['id', 'text'])
                ->where(['like', 'text', "%{$searchString}%", false]) // The 'false' parameter prevents Yii from adding extra escaping
                ->asArray()
                ->all();

        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    /**
     *
     * @param string $valueType
     * @param string $search
     * @param int|null $parentId
     * @param string|null $folder
     * @return array{error: bool, msg: string, results?: mixed}
     */
    private function searchBrocker(string $valueType, string $search, ?int $parentId, ?string $folder): array
    {
        Yii::debug(
                "*** Debug *** searchBrocker(valueType={$valueType}, search={$search}, parentId={$parentId}, folder={$folder})",
        );
        return match ($valueType) {
            'image' => $this->imageSearch($search, $folder),
            // Search in a global repository
            'npc-type' => $this->genericSearch('NpcType', $search),
            'damage-type' => $this->genericSearch('DamageType', $search),
            'action-type' => $this->genericSearch('ActionType', $search),
            'item' => $this->genericSearch('Item', $search),
            'skill' => $this->genericSearch('Skill', $search),
            'creature' => $this->genericSearch('Creature', $search),
            'language' => $this->genericSearch('Language', $search),
            // Search in story related data
            'mission' => $this->genericSearch('Mission', $search, ['chapter_id' => $parentId]),
            // Search in mission related data
            'npc' => $this->genericSearch('Npc', $search, ['mission_id' => $parentId]),
            'passage' => $this->genericSearch('Passage', $search, ['mission_id' => $parentId]),
            'decor' => $this->genericSearch('Decor', $search, ['mission_id' => $parentId]),
            'monster' => $this->genericSearch('Monster', $search, ['mission_id' => $parentId]),
            'action' => $this->genericSearch('Action', $search, ['mission_id' => $parentId]),
            // Search in decor related data
            'nested-trap' => $this->searchInDecor('Trap', $search, $parentId),
            'nested-item' => $this->searchInDecor('DecorItem', $search, $parentId),
            // Search in text
            'dialog' => $this->searchInTextColumn('Dialog', $search),
            'reply' => $this->searchInTextColumn('Reply', $search),
            default => throw new \Exception("Unsupported type {$valueType}"),
        };
    }

    /**
     *
     * @return array{error: bool, msg: string, results?: mixed}
     */
    public function actionValues(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $request = Yii::$app->request;
        $valueType = $request->get('valueType');
        $search = $request->get('search');
        $parentId = (int) $request->get('parentId');
        $folder = $request->get('folder');

        return $this->searchBrocker($valueType, $search ?? '', $parentId, $folder);
    }
}
