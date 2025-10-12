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
                            [
                                'actions' => ['*'], 'allow' => false, 'roles' => ['?'],
                            ],
                            [
                                'actions' => [
                                    'npc-type', 'damage-type', 'action-type', 'item', 'creature', 'image', 'skill',
                                    'npc', 'passage', 'decor', 'action', 'monter',
                                    'nested-trap', 'nested-item',
                                    'dialog', 'reply',
                                ],
                                'allow' => true,
                                'roles' => ['@'],
                            ],
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

    public function actionImage(string $search, string $folder): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $path = Yii::getAlias('@frontend/web/img/') . $folder;
        $results = [];

        if (is_dir($path)) {
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
        Yii::debug("*** debug *** - actionimage - '{$path}' is not a valid directory");
        return ['error' => true, 'msg' => "'{$path}' is not a valid directory"];
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

    private function decorSearch(string $modelName, int $missionId, string|null $userEntry): array {
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

    /**
     * Search in a global repository
     */
    public function actionNpcType(string|null $search = null): array {
        return $this->genericSearch('NpcType', $search);
    }

    public function actionDamageType(string|null $search = null): array {
        return $this->genericSearch('DamageType', $search);
    }

    public function actionActionType(string|null $search = null): array {
        return $this->genericSearch('ActionType', $search);
    }

    public function actionItem(string|null $search = null): array {
        return $this->genericSearch('Item', $search);
    }

    public function actionSkill(string|null $search = null): array {
        return $this->genericSearch('Skill', $search);
    }

    public function actionCreature(string|null $search = null): array {
        return $this->genericSearch('Creature', $search);
    }

    /**
     * Search in mission related data
     */
    public function actionNpc(int $parentId, string|null $search = null): array {
        return $this->genericSearch('Npc', $search, ['mission_id' => $parentId]);
    }

    public function actionPassage(int $parentId, string|null $search = null): array {
        return $this->genericSearch('Passage', $search, ['mission_id' => $parentId]);
    }

    public function actionDecor(int $parentId, string|null $search = null): array {
        return $this->genericSearch('Decor', $search, $search, ['mission_id' => $parentId]);
    }

    public function actionMonster(int $parentId, string|null $search = null): array {
        return $this->genericSearch('Monster', $search, $search, ['mission_id' => $parentId]);
    }

    public function actionAction(int $parentId, string|null $search = null): array {
        return $this->genericSearch('Action', $search, ['mission_id' => $parentId]);
    }

    /**
     * Search in decor related data
     */
    public function actionNestedTrap(int $parentId, string|null $search = null): array {
        return $this->decorSearch('Trap', $parentId, $search);
    }

    public function actionNestedItem(int $parentId, string|null $search = null): array {
        return $this->decorSearch('DecorItem', $parentId, $search);
    }

    /**
     * Search in text
     */
    private function genericTextSearch(string $modelName, string $search): array {
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

    public function actionDialog(string $search): array {
        return $this->genericTextSearch('Dialog', $search);
    }

    public function actionReply(string $search): array {
        return $this->genericTextSearch('Reply', $search);
    }
}
