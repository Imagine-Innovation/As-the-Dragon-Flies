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
                                    'dialog', 'npc-type', 'damage-type', 'item', 'creature', 'image', 'npc', 'passage', 'reply', 'skill', 'mission-item', 'trap',
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

            if ($filter) {
                $query->andWhere($filter);
            }
        } else {
            if ($filter) {
                $query->where($filter);
            }
        }

        $searchResult = $query->asArray()->all();

        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    public function actionNpcType(string $search): array {
        return $this->genericSearch('NpcType', $search);
    }

    public function actionDamageType(string $search): array {
        return $this->genericSearch('DamageType', $search);
    }

    public function actionItem(string $search): array {
        return $this->genericSearch('Item', $search);
    }

    public function actionMissionItem(int $missionId, string|null $search = null): array {
        return $this->genericSearch('MissionItem', $search, ['mission_id' => $missionId]);
    }

    public function actionCreature(string $search): array {
        return $this->genericSearch('Creature', $search);
    }

    public function actionNpc(int $missionId, string|null $search = null): array {
        return $this->genericSearch('Npc', $search, ['mission_id' => $missionId]);
    }

    public function actionPassage(int $missionId, string|null $search = null): array {
        return $this->genericSearch('Passage', $search, ['mission_id' => $missionId]);
    }

    public function actionTrap(int $missionId, string|null $search = null): array {
        return $this->genericTrap('Passage', $search, ['mission_id' => $missionId]);
    }

    public function actionSkill(string $search): array {
        return $this->genericSearch('Skill', $search);
    }

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
