<?php

namespace frontend\controllers;

use common\models\Image;
use common\models\ClassImage;
use frontend\components\AjaxRequest;
use common\components\ManageAccessRights;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * ImageController implements the CRUD actions for Image model.
 */
class ImageController extends Controller
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
                                    'index', 'view', 'ajax', 'ajax-set-class', 'ajax-upload',
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
     * Lists all Image models.
     *
     * @return string
     */
    public function actionIndex(): string {
        $dataProvider = new ActiveDataProvider([
            'query' => Image::find(),
        ]);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjax(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $raceGroupId = (int) $request->post('currentId', 1);
        $gender = $request->post('filter', null);

        $param = [
            'modelName' => 'Image',
            'param' => ['race_group_id' => $raceGroupId],
            'innerJoin' => [
                ['table' => 'race_group_image', 'clause' => 'image.id = race_group_image.image_id']
            ],
            'filter' => $gender ? ['race_group_image.race_group_id' => $raceGroupId, 'race_group_image.gender' => $gender] : ['race_group_image.race_group_id' => $raceGroupId],
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse($request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxSetClass(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $imageId = (int) $request->post('imageId');
        $classId = (int) $request->post('classId');
        $className = $request->post('className');
        $status = $request->post('status');

        $model = ClassImage::findOne(['image_id' => $imageId, 'class_id' => $classId]);

        $success = true;
        if ($model && !$status) {
            $success = $model->delete();
        } else {
            if (!$model && $status) {
                $model = new ClassImage(['image_id' => $imageId, 'class_id' => $classId]);
                $success = $model->save();
            }
        }

        if ($success) {
            return ['error' => false, 'msg' => "Class $className has been " . (($status === 1) ? "added" : "removed") . " to the image."];
        }
        return ['error' => true, 'msg' => "Unable to " . (($status === 1) ? "add" : "remove") . " class $className to the image!"];
    }

    /**
     * Displays a single Image model.
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
     * Creates a new Image model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate(): string|Response {
        $model = new Image();

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
     * Updates an existing Image model.
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
     * Deletes an existing Image model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id Primary key
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxUpload(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $id = (int) $request->post('id');

        $model = $this->findModel($id);

        $model->image = UploadedFile::getInstance($model, 'image');
        if ($model->upload()) {
            return ['error' => false, 'msg' => 'Image uploaded'];
        }

        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Finds the Image model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id Primary key
     * @return Image the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Image {
        if (($model = Image::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The image you are looking for does not exist.');
    }
}
