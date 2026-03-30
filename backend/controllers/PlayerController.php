<?php

namespace backend\controllers;

use common\components\AppStatus;
use common\components\ContextManager;
use common\components\AccessRightsManager;
use common\helpers\SaveHelper;
use common\helpers\Status;
use common\models\Player;
use common\models\User;
use common\components\AjaxRequest;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * PlayerController implements the CRUD actions for Player model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class PlayerController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        /** @phpstan-ignore-next-line */
        return array_merge(parent::behaviors(), [
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
                            'delete',
                            'index',
                            'restore',
                            'update',
                            'validate',
                            'view',
                            'ajax',
                        ],
                        'allow' => AccessRightsManager::isRouteAllowed($this),
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'restore' => ['POST'],
                    'validate' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjax(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $param = [
            'modelName' => 'Player',
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Displays a single Player model.
     *
     * @param int $id ID
     * @return string
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing Player model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     * @return string|\yii\web\Response
     */
    public function actionUpdate(int $id): string|Response
    {
        return $this->redirect(['player-builder/update', 'id' => $id]);
    }

    /**
     * Deletes an existing Player model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::DELETED->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not delete this player');
    }

    /**
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionValidate(int $id): Response
    {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::ACTIVE->value)) {
            return $this->redirect('index');
        }
        throw new NotFoundHttpException('Could not validate this player');
    }

    /**
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRestore(int $id): Response
    {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::INACTIVE->value)) {
            return $this->redirect(['index']);
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
    protected function findModel(int $id): Player
    {
        $query = Player::find()
                ->with(['race', 'class', 'background', 'playerAbilities', 'playerSkills', 'playerTraits'])
                ->where(['id' => $id]);

        $user = Yii::$app->user->identity;
        if (!$user->is_admin) {
            $query->andWhere(['user_id' => $user->id]);
        }

        if (($model = $query->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The player you are looking for does not exist.');
    }
}
