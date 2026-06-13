<?php

namespace backend\controllers;

use common\components\AccessRightsManager;
use common\components\AppStatus;
use common\components\AjaxRequest;
use common\helpers\Status;
use common\models\Chapter;
use common\models\Story;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * StoryController implements the CRUD actions for Story model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class StoryController extends Controller
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
                        'actions' => ['index', 'create', 'view', 'update', 'delete', 'validate', 'restore', 'print', 'ajax'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return AccessRightsManager::isRouteAllowed($action->controller);
                        },
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $user = Yii::$app->user->identity;
                            return $user && ($user->is_admin || $user->is_designer);
                        },
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
        ]);
    }

    /**
     * Lists all Story models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
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
            'modelName' => 'Story',
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Displays a single Story model.
     * @param int $id Primary key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Story model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate(): string|Response
    {
        $model = new Story();

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
     * Updates an existing Story model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id Primary key
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id): string|Response
    {
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
     * Deletes an existing Story model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id Primary key
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
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
    public function actionValidate(int $id): Response
    {
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
    public function actionRestore(int $id): Response
    {
        $model = $this->findModel($id);
        if (Status::changeStatus($model, AppStatus::DRAFT->value)) {
            return $this->redirect(['index']);
        }
        throw new NotFoundHttpException('Could not restore this story');
    }

    /**
     *
     * @param int $id
     * @return string
     */
    public function actionPrint(int $id): string
    {
        $story = $this->findModel($id);
        $chapters = Chapter::find()
                ->where(['story_id' => $story->id])
                ->orderBy('chapter_number')
                ->with([
                    'missions',
                    'missions.decors',
                    'missions.decors.traps',
                    'missions.decors.actions',
                    'missions.decors.passages',
                    'missions.npcs',
                    'missions.outcomes',
                    'missions.monsters',
                ])
                ->all();

        return $this->render('print', [
                    'story' => $story,
                    'chapters' => $chapters,
        ]);
    }

    /**
     * Exports a story to a JSON file.
     * @param int $id Primary key
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ServerErrorHttpException if JSON encoding fails
     */
    public function actionExport(int $id): Response
    {
        $data = Story::find()
                ->where(['id' => $id])
                ->with([
                    'storyClasses',
                    'storyTags',
                    'chapters',
                    'chapters.missions',
                    'chapters.missions.decors',
                    'chapters.missions.decors.traps',
                    'chapters.missions.decors.passages',
                    'chapters.missions.npcs',
                    'chapters.missions.npcs.npcType',
                    'chapters.missions.npcs.dialogs',
                    'chapters.missions.npcs.dialogs.replies',
                    'chapters.missions.monsters',
                    'chapters.missions.actions',
                    'chapters.missions.actions.actionType',
                    'chapters.missions.actions.actionType.actionTypeSkills',
                    'chapters.missions.actions.outcomes',
                    'chapters.missions.actions.outcomes.dialogs',
                    'chapters.missions.actions.outcomes.dialogs.replies',
                    'chapters.missions.actions.triggers',
                ])
                ->asArray()
                ->one();

        if ($data === null) {
            throw new NotFoundHttpException('The story your are looking for does not exist.');
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new ServerErrorHttpException('Error encountered during JSON encoding: ' . json_last_error_msg());
        }

        $sanitizedName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $data['name']);
        $filename = "story_{$data['id']}_{$sanitizedName}.json";

        return Yii::$app->response->sendContentAsFile($json, $filename, [
                    'mimeType' => 'application/json',
                    'inline' => false,
        ]);
    }

    /**
     * Finds the Story model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id Primary key
     * @return Story the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Story
    {
        if (($model = Story::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The story your are looking for does not exist.');
    }
}
