<?php

namespace frontend\controllers;

use common\models\Notification;
use frontend\components\AjaxRequest;
use common\components\QuestNotification;
use common\components\ManageAccessRights;
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
                                    'index', 'view', 'check',
                                    'ajax', 'ajax-counter', 'ajax-list',
                                    'ajax-mark-as-read'
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

    public function actionCheck() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not a GET Ajax request'];
        }
        $playerId = Yii::$app->request->get('playerId');

        if (!$playerId) {
            return ['error' => true, 'msg' => 'No selected player'];
        }

        $newNotifications = QuestNotification::getNewNotifications($playerId);
        if ($newNotifications) {
            return ['error' => false, 'notifications' => $newNotifications];
        }
        return ['error' => true, 'msg' => 'No new notification'];
    }

    public function actionAjaxList() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax request'];
        }
        /*
          $user = Yii::$app->user->identity;
          $playerId = $user->current_player_id;
         * 
         */
        $playerId = Yii::$app->request->get('playerId');
        $dateFrom = time() - (24 * 3600);

        $notificationList = QuestNotification::getList($playerId, $dateFrom);

        $render = $this->renderPartial('ajax-list', ['models' => $notificationList]);
        return ['error' => false, 'msg' => '', 'content' => $render];
    }

    public function actionAjaxCounter() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        /*
          $user = Yii::$app->user->identity;
          $playerId = $user->current_player_id;
         * 
         */
        $playerId = Yii::$app->request->get('playerId');
        $count = QuestNotification::getCount($playerId);

        return ['error' => false, 'msg' => '', 'content' => $count];
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

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
