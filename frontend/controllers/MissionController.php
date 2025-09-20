<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\models\Mission;
use common\models\Chapter;
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
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Mission model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($chapterId) {
        // Check if $id is a valid Chapter ID
        $chapter = $this->findChapter($chapterId);
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
     * Finds the Mission model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id Primary key
     * @return Mission the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Mission::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested mission does not exist.');
    }

    protected function findChapter($id) {
        if (($model = Chapter::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested chapter does not exist.');
    }
}
