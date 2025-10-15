<?php

namespace frontend\controllers;

use common\helpers\FileHelper;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * MissionController implements the CRUD actions for Mission model.
 */
class SearchController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors() {
        return array_merge(
                parent::behaviors(),
                [
                    'access' => [
                        'class' => AccessControl::class,
                        'rules' => [
                            ['actions' => ['*'], 'allow' => false, 'roles' => ['?']],
                            ['actions' => ['values'], 'allow' => true, 'roles' => ['@']],
                        ],
                    ],
                ]
        );
    }

    private function setFileSearchFilter(string $search): array {
        $extensions = ['.png', '.jpg', '.jpeg', '.gif'];
        $filter = [];
        foreach ($extensions as $extension) {
            $filter[] = "*{$search}*{$extension}";
        }
        return $filter;
    }

    private function imageSearch(string $search, string $folder): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $path = Yii::getAlias('@frontend/web/img/') . $folder;
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
                'img' => "/frontend/web/img/{$folder}/{$fileName}",
                'text' => FileHelper::removeExtension($fileName),
            ];
        }
        return ['error' => false, 'msg' => '', 'results' => $results];
    }

    private function normalizeSearchString(string|null $inputString): string|null {
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
                "_", // single character SQL wildcard
                $inputString
        );
        return $normalizedString;
    }

    private function searchInDecor(string $modelName, int $missionId, string|null $userEntry): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $searchString = $this->normalizeSearchString($userEntry);

        $fullModelName = "\\common\\models\\{$modelName}";
        $query = $fullModelName::find()->select(['t.id', 't.name', 't.description as text'])
                ->from(['t' => $fullModelName::tableName()]);

        if ($searchString) {
            $query->where(['like', 'name', "%{$searchString}%", false]) // The 'false' parameter prevents Yii from adding extra escaping
                    ->orWhere(['like', 'description', "%{$searchString}%", false]);
        }
        $query->innerJoin('decor', 't.decor_id = decor.id')
                ->where(['decor.mission_id' => $missionId]);

        $searchResult = $query->asArray()->all();

        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    private function genericSearch(string $modelName, string|null $userEntry, array|null $filter = null): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $searchString = $this->normalizeSearchString($userEntry);

        $fullModelName = "\\common\\models\\{$modelName}";
        $query = $fullModelName::find()->select(['id', 'name', 'description as text']);

        if ($searchString) {
            $query->where(['like', 'name', "%{$searchString}%", false]) // The 'false' parameter prevents Yii from adding extra escaping
                    ->orWhere(['like', 'description', "%{$searchString}%", false]);
        }
        if ($filter) {
            $query->where($filter);
        }

        $searchResult = $query->asArray()->all();

        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    private function searchInTextColumn(string $modelName, string $search): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $searchString = $this->normalizeSearchString($search);

        $fullModelName = "\\common\\models\\{$modelName}";
        $searchResult = $fullModelName::find()
                ->select(['id', 'text'])
                ->where(['like', 'text', "%{$searchString}%", false]) // The 'false' parameter prevents Yii from adding extra escaping
                ->asArray()
                ->all();

        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    private function searchBrocker(string|null $valueType, string|null $search, int|null $parentId, string|null $folder): array {
        Yii::debug("*** Debug *** searchBrocker(valueType={$valueType}, search={$search}, parentId={$parentId}, folder={$folder})");
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
            'decor' => $this->genericSearch('Decor', $search, $search, ['mission_id' => $parentId]),
            'monster' => $this->genericSearch('Monster', $search, $search, ['mission_id' => $parentId]),
            'action' => $this->genericSearch('Action', $search, ['mission_id' => $parentId]),
            // Search in decor related data
            'nested-trap' => $this->searchInDecor('Trap', $parentId, $search),
            'nested-item' => $this->searchInDecor('DecorItem', $parentId, $search),
            // Search in text
            'dialog' => $this->searchInTextColumn('Dialog', $search),
            'reply' => $this->searchInTextColumn('Reply', $search),
            default => throw new \Exception("Unsupported type {$valueType}"),
        };
    }

    public function actionValues(): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $request = Yii::$app->request;
        $valueType = $request->get('valueType');
        $search = $request->get('search');
        $parentId = $request->get('parentId');
        $folder = $request->get('folder');

        return $this->searchBrocker($valueType, $search, $parentId, $folder);
    }
}
