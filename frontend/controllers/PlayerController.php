<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ContextManager;
use common\components\ManageAccessRights;
use common\helpers\Status;
use common\models\Player;
use common\models\User;
use frontend\components\AjaxRequest;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * PlayerController implements the CRUD actions for Player model.
 */
class PlayerController extends Controller {

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
                                    'admin', 'delete', 'index', 'restore', 'update', 'validate', 'view',
                                    'ajax', 'ajax-admin', 'ajax-lite', 'ajax-set-context',
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
                            'validate' => ['POST'],
                        ],
                    ],
                ]
        );
    }

    /**
     * Lists all Player models.
     *
     * @return string
     */
    public function actionIndex() {
        $user = Yii::$app->user->identity;
        $players = Player::find()
                ->where(['user_id' => $user->id])
                ->andWhere(['status' => [AppStatus::ACTIVE->value, AppStatus::INACTIVE->value]])
                ->all();

        return $this->render('index', ['players' => $players]);
    }

    public function actionAdmin() {
        return $this->render('admin');
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
            'modelName' => 'Player',
            'filter' => ['user_id' => Yii::$app->user->identity->id]
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxAdmin() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $param = [
            'modelName' => 'Player',
            'render' => 'ajax-admin',
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxLite() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $userId = $request->post('userId');

        $param = [
            'modelName' => 'Player',
            'render' => 'ajax-lite',
            'filter' => ['user_id' => $userId]
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse($request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxSetContext() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;

        $userId = (int) $request->post('userId');
        $playerId = (int) $request->post('playerId');

        $success = User::updateAll(
                ['current_player_id' => $playerId],
                ['id' => $userId]
        );

        ContextManager::updatePlayerContext($playerId);

        return [
            'error' => false,
            'msg' => $success ? 'Context is saved' : 'Could not save context'
        ];
    }

    /**
     * Displays a single Player model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing Player model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        return $this->redirect(['player-builder/update', 'id' => $id]);
    }

    /**
     * Deletes an existing Player model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::DELETED->value)) {
            return $this->redirect(['admin']);
        }
        throw new NotFoundHttpException('Could not delete this player');
    }

    public function actionValidate($id) {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::ACTIVE->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not validate this player');
    }

    public function actionRestore($id) {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::INACTIVE->value)) {
            return $this->redirect(['admin']);
        }
        throw new NotFoundHttpException('Could not restore this player');
    }

    /**
     * Finds the Player model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Player the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {

        $query = Player::find()
                ->with(['race', 'class', 'background', 'history', 'playerAbilities', 'playerSkills', 'playerTraits'])
                ->where(['id' => $id]);

        $user = Yii::$app->user->identity;
        if (!$user->is_admin) {
            $query->andWhere(['user_id' => $user->id]);
        }

        if (($model = $query->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
