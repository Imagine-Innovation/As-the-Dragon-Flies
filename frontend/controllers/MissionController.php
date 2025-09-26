<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\models\Dialog;
use common\models\Mission;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * MissionController implements the CRUD actions for Mission model.
 */
class MissionController extends Controller
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
                                'actions' => ['*'],
                                'allow' => false,
                                'roles' => ['?'],
                            ],
                            [
                                'actions' => [
                                    'create', 'view', 'update',
                                    'add-detail', 'edit-detail',
                                    'ajax-search-dialog', 'ajax-search-npc-type',
                                ],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                    'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                            'delete' => ['POST'],
                        ],
                    ],
                ]
        );
    }

    /**
     * Displays a single Mission model.
     * @param int $id Primary key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel('Mission', ['id' => $id]),
        ]);
    }

    /**
     * Creates a new Mission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($chapterId) {
        // Check if $id is a valid Chapter ID
        $chapter = $this->findModel('Mission', ['Chapter' => $chapterId]);
        $model = new Mission();
        $model->chapter_id = $chapter->id;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * Updates an existing Mission model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id Primary key
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id) {
        $model = $this->findModel('Mission', ['id' => $id]);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     *
     * @param string $type
     * @return array
     */
    protected function getDetailFromType(string $type): array {
        return match ($type) {
            'NPC' => ['className' => 'Npc', 'snippet' => 'npc-form'],
            'Item' => ['className' => 'MissionItem', 'snippet' => 'item-form'],
            'Monster' => ['className' => 'MissionShape', 'snippet' => 'monster-form'],
            'Trap' => ['className' => 'Trap', 'snippet' => 'trap-form'],
            default => throw new \Exception("Unsupported type {$type}"),
        };
    }

    /**
     * Creates a new Mission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionAddDetail(int $missionId, string $type) {
        $detail = $this->getDetailFromType($type);
        $className = $detail['className'];
        $mission = $this->findModel('Mission', ['id' => $missionId]);

        $modelName = "\\common\\models\\{$className}";
        $model = new $modelName();
        $model->mission_id = $mission->id;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $mission->id]);
            }
            throw new \Exception(implode("<br/>", ArrayHelper::getColumn($model->errors, 0, false)));
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('add-detail', [
                    'model' => $model,
                    'mission' => $mission,
                    'type' => $type,
                    'snippet' => $detail['snippet'],
        ]);
    }

    public function actionEditDetail(string $jsonParams, string $type) {
        $detail = $this->getDetailFromType($type);
        $className = $detail['className'];
        $searchParams = json_decode($jsonParams, true);
        Yii::debug($jsonParams);
        Yii::debug($searchParams);
        $mission = $this->findModel('Mission', ['id' => $searchParams['mission_id']]);

        $model = $this->findModel($className, $searchParams);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $mission->id]);
            }
            throw new \Exception(implode("<br/>", ArrayHelper::getColumn($model->errors, 0, false)));
        }

        return $this->render('edit-detail', [
                    'model' => $model,
                    'mission' => $mission,
                    'type' => $type,
                    'snippet' => $detail['snippet'],
        ]);
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

    public function actionAjaxSearchDialog() {
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

        $dialogs = Dialog::find()
                ->select(['id', 'text as name', 'text'])
                ->where(['like', 'text', "%{$searchString}%", false]) // The 'false' parameter prevents Yii from adding extra escaping
                ->asArray()
                ->all();

        $searchResult = $dialogs;
        Yii::debug($searchResult);
        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    public function actionAjaxSearchNpcType() {
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

        $dialogs = \common\models\NpcType::find()
                ->select(['id', 'name', 'description as text'])
                ->where(['like', 'description', "%{$searchString}%", false]) // The 'false' parameter prevents Yii from adding extra escaping
                ->asArray()
                ->all();

        $searchResult = $dialogs;
        Yii::debug($searchResult);
        return ['error' => false, 'msg' => '', 'results' => $searchResult];
    }

    /**
     * Finds the model model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $modelName model type to load
     * @param array $param
     * @return common\models\modelName the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(string $modelName, array $param) {
        $activeRecord = "\\common\\models\\{$modelName}";
        if (($model = $activeRecord::findOne($param)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested {$modelName} does not exist.');
    }
}
