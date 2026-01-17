<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\models\Chapter;
use common\models\Story;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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
     *
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
     * Creates a new Chapter model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $storyId
     * @return string|\yii\web\Response
     */
    public function actionCreate(int $storyId): string|Response {
        // Check if $id is a valid Story ID
        $story = $this->findStory($storyId);
        $model = new Chapter();
        $model->story_id = $story->id;

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
     * Updates an existing Chapter model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id Primary key
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id): string|Response {
        $model = $this->findModel($id);

        $post = (array) $this->request->post();
        if ($this->request->isPost && $model->load($post) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Finds the Chapter model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id Primary key
     * @return Chapter the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Chapter {
        if (($model = Chapter::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested chapter does not exist.');
    }

    /**
     *
     * @param int $id
     * @return Story
     * @throws NotFoundHttpException
     */
    protected function findStory($id): Story {
        if (($model = Story::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested story does not exist.');
    }
}
