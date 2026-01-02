<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ManageAccessRights;
use common\helpers\Status;
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
 * StoryController implements the CRUD actions for Story model.
 */
class StoryController extends Controller
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
                                    'index', 'create', 'view', 'update', 'delete',
                                    'validate', 'restore',
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
     * Lists all Story models.
     *
     * @return string
     */
    public function actionIndex(): string {
        $user = Yii::$app->user->identity;

        $query = Story::find();
        if (!$user->is_designer) {
            $query->where(['status' => AppStatus::PUBLISHED->value]);
        }

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Story model.
     * @param int $id Primary key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Story model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate(): string|Response {
        $model = new Story();

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
     * Updates an existing Story model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id Primary key
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id): string|Response {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Story model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id Primary key
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::ARCHIVED->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not delete this story');
    }

    /**
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionValidate(int $id): Response {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::PUBLISHED->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not validate this story');
    }

    /**
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRestore(int $id): Response {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::DRAFT->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not restore this story');
    }

    /**
     * Finds the Story model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id Primary key
     * @return Story the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Story {
        if (($model = Story::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The story your are looking for does not exist.');
    }
}
