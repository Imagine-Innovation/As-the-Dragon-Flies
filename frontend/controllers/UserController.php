<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ManageAccessRights;
use common\helpers\Status;
use common\models\User;
use frontend\components\AjaxRequest;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\db\ActiveRecord;
use yii\behaviors\AttributeBehavior;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller {

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
                                    'index', 'view', 'update', 'delete', 'validate', 'restore',
                                    'ajax', 'ajax-set-role',
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
                            'ajax' => ['POST'],
                        ],
                    ],
                    [
                        'class' => AttributeBehavior::class,
                        'attributes' => [
                            ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                            ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                        ],
                        'value' => function ($event) {
                            return time();
                        },
                    ],
                ]
        );
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex() {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'sort' => [
                'defaultOrder' => [
                    'username' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAjax() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $param = [
            'modelName' => 'User',
            'sortOrder' => ['created_at' => SORT_DESC]
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxSetRole() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $id = $request->post('id');
        $model = User::findOne(['id' => $id]);
        if (!$model) {
            return ['error' => true, 'msg' => "User id $id not found!"];
        }

        $role = $request->post('role');
        $status = $request->post('status');
        $property = "is_" . $role;
        $model->$property = $status;

        if ($model->save()) {
            return ['error' => false, 'msg' => "Role $role has been " . ($status == 1 ? "granted" : "revoked") . " to user $model->username"];
        }
        return ['error' => true, 'msg' => "Unable to " . ($status == 1 ? "grant" : "revoke") . " role $role to user $model->username"];
    }

    /**
     * Displays a single User model.
     * @param int $id Primary Key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    public function actionDelete($id) {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::DELETED->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not delete this user');
    }

    public function actionValidate($id) {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::ACTIVE->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not validate this user');
    }

    public function actionRestore($id) {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::INACTIVE->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not restore this user');
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id Primary Key
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id) {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The user your are looking for does not exist.');
    }
}
