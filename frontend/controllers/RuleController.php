<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ManageAccessRights;
use common\helpers\Status;
use common\models\Rule;
use common\models\RuleExpression;
use frontend\components\AjaxRequest;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\db\ActiveRecord;
use yii\behaviors\AttributeBehavior;

/**
 * RuleController implements the CRUD actions for Rule model.
 */
class RuleController extends Controller
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
                                    'index', 'create', 'update', 'view', 'delete',
                                    'validate', 'restore',
                                    'ajax',
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
     * Lists all Rule models.
     *
     * @return string
     */
    public function actionIndex(): string {
        return $this->render('index');
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

        $param = [
            'modelName' => 'Rule'
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Displays a single Rule model.
     * @param int $id Primary key.
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string {
        return $this->render('view', [
                    'model' => $this->findModel($id)
        ]);
    }

    /**
     * Creates a new Rule model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate(): string|Response {
        $model = new Rule();

        if ($this->request->isPost) {
            $post = (array) $this->request->post();
            if ($model->load($post) && $model->save()) {
                $model->saveRuleDefinition();
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
     * Updates an existing Rule model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id Primary key.
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id): string|Response {
        $model = $this->findModel($id);

        $post = (array) $this->request->post();
        if ($this->request->isPost && $model->load($post) && $model->save()) {
            $affectedRows = RuleExpression::deleteAll(['rule_id' => $id]);
            $model->saveRuleDefinition();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Rule model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id Primary key.
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::DELETED->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not delete this rule');
    }

    /**
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionValidate(int $id): Response {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::ACTIVE->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not validate this rule');
    }

    /**
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRestore(int $id): Response {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::INACTIVE->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not restore this rule');
    }

    /**
     * Finds the Rule model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id Primary key.
     * @return Rule the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Rule {
        if (($model = Rule::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The rule your are looking for does not exist.');
    }
}
