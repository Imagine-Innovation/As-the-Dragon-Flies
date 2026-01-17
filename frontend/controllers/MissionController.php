<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\helpers\FindModelHelper;
use common\helpers\JsonHelper;
use common\helpers\MixedHelper;
use common\models\Mission;
use Yii;
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
        /** @phpstan-ignore-next-line */
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
     *
     * @param int $id Primary key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string {
        $model = FindModelHelper::findMission($id);
        return $this->render('view', [
                    'model' => $model,
        ]);
    }

    /**
     * Creates a new Mission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $chapterId
     * @return string|Response
     */
    public function actionCreate(int $chapterId): string|Response {
        // Check if $id is a valid Chapter ID
        $chapter = FindModelHelper::findChapter($chapterId);
        $model = new Mission();
        $model->chapter_id = $chapter->id;

        if ($this->request->isPost) {
            $post = (array) $this->request->post();
            if ($model->load($post) && $model->save()) {
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
    public function actionUpdate(int $id): string|Response {
        $model = FindModelHelper::findMission($id);

        $post = (array) $this->request->post();
        if ($this->request->isPost && $model->load($post) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     *
     * @param string $type
     * @return array{className: string, snippet: string, childOf: string}
     * @throws \Exception
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
            'Prerequisite' => ['className' => 'ActionFlow', 'snippet' => 'flow-form', 'childOf' => 'action'],
            'Trigger' => ['className' => 'ActionFlow', 'snippet' => 'flow-form', 'childOf' => 'action'],
            'Outcome' => ['className' => 'Outcome', 'snippet' => 'outcome-form', 'childOf' => 'action'],
            default => throw new \Exception("Unsupported type {$type}"),
        };
    }

    /**
     *
     * @param \yii\db\ActiveRecord $model
     * @param Mission $mission
     * @param string $type
     * @param string $snippet
     * @return string|Response
     * @throws \Exception
     */
    private function createDetailModel(\yii\db\ActiveRecord &$model, Mission $mission, string $type, string $snippet): string|Response {
        if ($this->request->isPost) {
            $post = (array) $this->request->post();
            if ($model->load($post) && $model->save()) {
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

    /**
     *
     * @param int $missionId
     * @param string $type
     * @param string $className
     * @param string $snippet
     * @return string|Response
     */
    private function addMissionChild(int $missionId, string $type, string $className, string $snippet): string|Response {
        $mission = FindModelHelper::findMission($missionId);

        /** @var class-string<\yii\db\ActiveRecord> $modelClass */
        $modelClass = "\\common\\models\\{$className}";
        $model = new $modelClass();
        $model->mission_id = $mission->id;

        return $this->createDetailModel($model, $mission, $type, $snippet);
    }

    /**
     *
     * @param int $decorId
     * @param string $type
     * @param string $className
     * @param string $snippet
     * @return string|Response
     */
    private function addDecorChild(int $decorId, string $type, string $className, string $snippet): string|Response {
        $decor = FindModelHelper::findDecor($decorId);
        $mission = $decor->mission;

        /** @var class-string<\yii\db\ActiveRecord> $modelClass */
        $modelClass = "\\common\\models\\{$className}";
        $model = new $modelClass();
        $model->decor_id = $decor->id;

        return $this->createDetailModel($model, $mission, $type, $snippet);
    }

    /**
     *
     * @param int $actionId
     * @param string $type
     * @param string $className
     * @param string $snippet
     * @return string|Response
     */
    private function addActionChild(int $actionId, string $type, string $className, string $snippet): string|Response {
        $action = FindModelHelper::findAction($actionId);
        $mission = $action->mission;

        /** @var class-string<\yii\db\ActiveRecord> $modelClass */
        $modelClass = "\\common\\models\\{$className}";
        $model = new $modelClass();
        if ($type === 'Prerequisite') {
            $model->next_action_id = $action->id;
        } elseif ($type === 'Trigger') {
            $model->previous_action_id = $action->id;
        } else {
            $model->action_id = $action->id;
        }

        return $this->createDetailModel($model, $mission, $type, $snippet);
    }

    /**
     *
     * @param int $parentId
     * @param string $type
     * @return string|Response
     * @throws \Exception
     */
    public function actionAddDetail(int $parentId, string $type): string|Response {
        $detailInfo = $this->getDetailInfoFromType($type);
        $className = $detailInfo['className'];
        $snippet = $detailInfo['snippet'];

        return match ($detailInfo['childOf']) {
            'mission' => $this->addMissionChild($parentId, $type, $className, $snippet),
            'decor' => $this->addDecorChild($parentId, $type, $className, $snippet),
            'action' => $this->addActionChild($parentId, $type, $className, $snippet),
            default => throw new \Exception("Unsupported type {$detailInfo['childOf']}"),
        };
    }

    /**
     *
     * @param \yii\db\ActiveRecord $model
     * @param Mission $mission
     * @param string $type
     * @param string $snippet
     * @return string|Response
     * @throws \Exception
     */
    private function updateDetailModel(\yii\db\ActiveRecord &$model, Mission $mission, string $type, string $snippet): string|Response {
        if ($this->request->isPost) {
            $post = (array) $this->request->post();
            if ($model->load($post) && $model->save()) {
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

    /**
     *
     * @param string $jsonParams
     * @param string $type
     * @param class-string<\yii\db\ActiveRecord> $className
     * @param string $snippet
     * @return string|Response
     */
    private function editMissionChild(string $jsonParams, string $type, string $className, string $snippet): string|Response {
        $searchParams = JsonHelper::decode($jsonParams);

        $model = FindModelHelper::findModel($className, $searchParams);
        $mission = $model->mission;

        return $this->updateDetailModel($model, $mission, $type, $snippet);
    }

    /**
     *
     * @param string $jsonParams
     * @param string $type
     * @param class-string<\yii\db\ActiveRecord> $className
     * @param string $snippet
     * @return string|Response
     */
    private function editDecorChild(string $jsonParams, string $type, string $className, string $snippet): string|Response {
        $searchParams = JsonHelper::decode($jsonParams);

        $model = FindModelHelper::findModel($className, $searchParams);
        $decor = $model->decor;
        $mission = $decor->mission;

        return $this->updateDetailModel($model, $mission, $type, $snippet);
    }

    /**
     *
     * @param string $jsonParams
     * @param string $type
     * @param class-string<\yii\db\ActiveRecord> $className
     * @param string $snippet
     * @return string|Response
     */
    private function editActionChild(string $jsonParams, string $type, string $className, string $snippet): string|Response {
        $searchParams = JsonHelper::decode($jsonParams);

        $model = FindModelHelper::findModel($className, $searchParams);
        if ($type === 'Prerequisite') {
            $action = $model->nextAction;
        } elseif ($type === "Trigger") {
            $action = $model->previousAction;
        } else {
            $action = $model->action;
        }
        $mission = $action->mission;

        return $this->updateDetailModel($model, $mission, $type, $snippet);
    }

    /**
     *
     * @param string $jsonParams
     * @param string $type
     * @return string|Response
     * @throws \Exception
     */
    public function actionEditDetail(string $jsonParams, string $type): string|Response {
        $detailInfo = $this->getDetailInfoFromType($type);
        /** @var class-string<\yii\db\ActiveRecord> $className */
        $className = $detailInfo['className'];
        $snippet = $detailInfo['snippet'];

        return match ($detailInfo['childOf']) {
            'mission' => $this->editMissionChild($jsonParams, $type, $className, $snippet),
            'decor' => $this->editDecorChild($jsonParams, $type, $className, $snippet),
            'action' => $this->editActionChild($jsonParams, $type, $className, $snippet),
            default => throw new \Exception("Unsupported type {$detailInfo['childOf']}"),
        };
    }
}
