<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\models\Chapter;
use common\models\Story;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ChapterController implements the CRUD actions for Chapter model.
 */
class ChapterController extends Controller
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
     * Displays a single Chapter model.
     * @param int $id Primary key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Chapter model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($storyId) {
        // Check if $id is a valid Story ID
        $story = $this->findStory($storyId);
        $model = new Chapter();
        $model->story_id = $story->id;

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
     * Updates an existing Chapter model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id Primary key
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Finds the Chapter model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id Primary key
     * @return Chapter the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Chapter::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested chapter does not exist.');
    }

    protected function findStory($id) {
        if (($model = Story::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested story does not exist.');
    }
}
