<?php

namespace frontend\controllers;

use common\models\Notification;
use common\components\ManageAccessRights;
use frontend\components\AjaxRequest;
use frontend\components\QuestNotification;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * NotificationController implements the CRUD actions for Notification model.
 */
class NotificationController extends Controller {

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
                                    'index', 'view',
                                    'ajax', 'ajax-mark-as-read'
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
     * Lists all Notification models.
     *
     * @return string
     */
    public function actionIndex() {
        $user = Yii::$app->user->identity;
        if ($user->is_admin) {
            return $this->render('index');
        }
        throw new ForbiddenHttpException('You are not allowed to see the notifications');
    }

    /**
     * Displays a single Notification model.
     * @param int $id Primary key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $user = Yii::$app->user->identity;
        if ($user->is_admin) {
            return $this->render('view', [
                        'model' => $this->findModel($id),
            ]);
        }
        throw new ForbiddenHttpException('You are not allowed to see the notifications');
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
            'modelName' => 'Notification'
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxMarkAsRead() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId', 0);
        Yii::debug("*** Debug *** actionAjaxMarkAsRead - playerId=$playerId");
        $ret = QuestNotification::markNotificationsAsRead($playerId);
        if ($ret) {
            return ['error' => false, 'msg' => 'Notification updated', 'content' => 0];
        }
        return ['error' => true, 'msg' => 'Unable to mark notifications as read for player id ' . $playerId];
    }

    /**
     * Finds the Notification model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id Primary key
     * @return Notification the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Notification::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The notification you are looking for does not exist.');
    }
}
