<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
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
                                    'dialog', 'npc-type', 'damage-type', 'item', 'creature',
                                ],
                                'allow' => true,
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                ]
        );
    }

    private function normalizeSearchString(string $inputString): string {
        Yii::debug("*** debug *** normalizeSearchString - inputStrind={$inputString}");
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
        Yii::debug("*** debug *** normalizeSearchString - normalizedString={$normalizedString}");
        return $normalizedString;
    }

    public function actionDialog() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $request = Yii::$app->request;
        $userEntry = $request->get('q');
        $searchString = $this->normalizeSearchString($userEntry);

        $searchResult = \common\models\Dialog::find()
                ->select(['id', 'text'])
                ->where(['like', 'text', "%{$searchString}%", false]) // The 'false' parameter prevents Yii from adding extra escaping
                ->asArray()
                ->all();

        Yii::debug($searchResult);
        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    private function standardSearch(string $modelName): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $request = Yii::$app->request;
        $userEntry = $request->get('q');
        $searchString = $this->normalizeSearchString($userEntry);

        $fullModelName = "\\common\\models\\{$modelName}";
        $searchResult = $fullModelName::find()
                ->select(['id', 'name', 'description as text'])
                ->where(['like', 'description', "%{$searchString}%", false]) // The 'false' parameter prevents Yii from adding extra escaping
                ->asArray()
                ->all();

        Yii::debug($searchResult);
        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    public function actionNpcType() {
        return $this->standardSearch('NpcType');
    }

    public function actionDamageType() {
        return $this->standardSearch('DamageType');
    }

    public function actionItem() {
        return $this->standardSearch('Item');
    }

    public function actionCreature() {
        return $this->standardSearch('Creature');
    }
}
