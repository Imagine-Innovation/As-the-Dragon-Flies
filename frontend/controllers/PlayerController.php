<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\helpers\Status;
use common\models\Player;
use common\models\User;
use frontend\components\AjaxRequest;
use frontend\components\BuilderTool;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;

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
                                    'index', 'create', 'builder', 'update', 'save-abilities', 'validate', 'restore',
                                    'ajax', 'ajax-admin', 'ajax-lite', 'ajax-set-context',
                                    'view', 'delete', 'possessions', 'admin',
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
                            'save-abilities' => ['POST'],
                            'validate' => ['POST'],
                        ],
                    ],
                /*
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
                 *
                 */
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
                ->andWhere(['status' => [Player::STATUS_ACTIVE, Player::STATUS_INACTIVE]])
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

        $userId = $request->post('userId');
        $playerId = $request->post('playerId');

        $success = User::updateAll(
                ['current_player_id' => $playerId],
                ['id' => $userId]
        );
        ManageAccessRights::updateSession();

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
        if (Status::changeStatus($model, Player::STATUS_DELETED)) {
            return $this->redirect(['admin']);
        }
        throw new NotFoundHttpException('Could not delete this player');
    }

    public function actionValidate($id) {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, Player::STATUS_ACTIVE)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not validate this player');
    }

    public function actionRestore($id) {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, Player::STATUS_INACTIVE)) {
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
