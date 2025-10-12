<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
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
                                    'create', 'view', 'update', 'add-detail', 'edit-detail',
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
        $chapter = $this->findModel('Chapter', ['id' => $chapterId]);
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
    protected function getDetailInfoFromType(string $type): array {
        return match ($type) {
            'NPC' => ['className' => 'Npc', 'snippet' => 'npc-form', 'childOf' => 'mission'],
            'Monster' => ['className' => 'Monster', 'snippet' => 'monster-form', 'childOf' => 'mission'],
            'Passage' => ['className' => 'Passage', 'snippet' => 'passage-form', 'childOf' => 'mission'],
            'Action' => ['className' => 'Action', 'snippet' => 'action-form', 'childOf' => 'mission'],
            'Decor' => ['className' => 'Decor', 'snippet' => 'decor-form', 'childOf' => 'mission'],
            'Item' => ['className' => 'DecorItem', 'snippet' => 'item-form', 'childOf' => 'decor'],
            'Trap' => ['className' => 'Trap', 'snippet' => 'trap-form', 'childOf' => 'decor'],
            'Prerequisite' => ['className' => 'ActionInteraction', 'snippet' => 'interaction-form', 'childOf' => 'action'],
            'Trigger' => ['className' => 'ActionInteraction', 'snippet' => 'interaction-form', 'childOf' => 'action'],
            default => throw new \Exception("Unsupported type {$type}"),
        };
    }

    private function createDetailModel(\yii\db\ActiveRecord &$model, Mission $mission, string $type, string $snippet) {
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
                    'snippet' => $snippet,
        ]);
    }

    private function addMissionChild(int $missionId, string $type, string $className, string $snippet) {
        $mission = $this->findModel('Mission', ['id' => $missionId]);

        $modelName = "\\common\\models\\{$className}";
        $model = new $modelName();
        $model->mission_id = $mission->id;

        return $this->createDetailModel($model, $mission, $type, $snippet);
    }

    private function addDecorChild(int $decorId, string $type, string $className, string $snippet) {
        $decor = $this->findModel('Decor', ['id' => $decorId]);
        $mission = $decor->mission;

        $modelName = "\\common\\models\\{$className}";
        $model = new $modelName();
        $model->decor_id = $decor->id;

        return $this->createDetailModel($model, $mission, $type, $snippet);
    }

    private function addActionChild(int $actionId, string $type, string $snippet) {
        $action = $this->findModel('Action', ['id' => $actionId]);
        $mission = $action->mission;

        $model = new \common\models\ActionInteraction();
        if ($type === 'Prerequisite') {
            $model->next_action_id = $action->id;
        } else {
            $model->previous_action_id = $action->id;
        }

        return $this->createDetailModel($model, $mission, $type, $snippet);
    }

    /**
     * Creates a new Mission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionAddDetail(int $parentId, string $type) {
        $detailInfo = $this->getDetailInfoFromType($type);
        $className = $detailInfo['className'];
        $snippet = $detailInfo['snippet'];

        return match ($detailInfo['childOf']) {
            'mission' => $this->addMissionChild($parentId, $type, $className, $snippet),
            'decor' => $this->addDecorChild($parentId, $type, $className, $snippet),
            'action' => $this->addActionChild($parentId, $type, $snippet),
            default => throw new \Exception("Unsupported type {$detailInfo['childOf']}"),
        };
    }

    private function updateDetailModel(\yii\db\ActiveRecord &$model, Mission $mission, string $type, string $snippet) {
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
                    'snippet' => $snippet,
        ]);
    }

    private function editMissionChild(string $jsonParams, string $type, string $className, string $snippet) {
        $searchParams = json_decode($jsonParams, true);

        $model = $this->findModel($className, $searchParams);
        $mission = $model->mission;

        return $this->updateDetailModel($model, $mission, $type, $snippet);
    }

    private function editDecorChild(string $jsonParams, string $type, string $className, string $snippet) {
        $searchParams = json_decode($jsonParams, true);

        $model = $this->findModel($className, $searchParams);
        $decor = $model->decor;
        $mission = $decor->mission;

        return $this->updateDetailModel($model, $mission, $type, $snippet);
    }

    private function editActionChild(string $jsonParams, string $type, string $snippet) {
        $searchParams = json_decode($jsonParams, true);

        $model = $this->findModel('ActionInteraction', $searchParams);
        if ($type === 'Prerequisite') {
            $action = $model->nextAction;
        } else {
            $action = $model->previousAction;
        }
        $mission = $action->mission;

        return $this->updateDetailModel($model, $mission, $type, $snippet);
    }

    public function actionEditDetail(string $jsonParams, string $type) {
        $detailInfo = $this->getDetailInfoFromType($type);
        $className = $detailInfo['className'];
        $snippet = $detailInfo['snippet'];

        return match ($detailInfo['childOf']) {
            'mission' => $this->editMissionChild($jsonParams, $type, $className, $snippet),
            'decor' => $this->editDecorChild($jsonParams, $type, $className, $snippet),
            'action' => $this->editActionChild($jsonParams, $type, $snippet),
            default => throw new \Exception("Unsupported type {$detailInfo['childOf']}"),
        };
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
        $pk = $param['id'] ?? null;
        $model = $activeRecord::findOne($pk ? ['id' => $pk] : $param);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested {$modelName} does not exist.');
    }
}
