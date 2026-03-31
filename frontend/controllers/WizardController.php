<?php

namespace frontend\controllers;

use common\components\AccessRightsManager;
use common\models\Alignment;
use common\models\CharacterClass;
use common\models\Race;
use common\models\Wizard;
use common\models\WizardQuestion;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * WizardController implements the CRUD actions for Wizard model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class WizardController extends Controller
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
                        'actions' => ['index', 'ajax-question', 'ajax-alignment', 'ajax-character-class', 'ajax-race', 'view'],
                        'allow' => AccessRightsManager::isRouteAllowed($this),
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
     * Lists all Wizard models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Wizard::find(),
        ]);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Wizard model.
     * @param int $id ID
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
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxQuestion(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $id = $request->post('id', 1);
        $topic = $request->post('topic', 'Unknown');
        Yii::debug("*** Debug *** actionAjaxQuestion - id={$id}, topic={$topic}");
        $propertyMap = [
            'class' => 'class_id',
            'race' => 'race_id',
            'alignment' => 'alignment_id',
        ];

        $property = $propertyMap[$topic] ?? null;
        if ($property === null) {
            return ['error' => true, 'msg' => "Invalid Wizard topic '{$topic}'"];
        }

        $model = $this->findQuestion($id);
        $content = $this->renderPartial('ajax/question', [
            'topic' => $topic,
            'property' => $property,
            'model' => $model,
        ]);
        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     * }
     */
    public function actionAjaxAlignment(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $id = $request->post('id');
        $model = $this->findAlignment($id);

        $content = $this->renderPartial('ajax/alignment', [
            'model' => $model,
        ]);

        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxCharacterClass()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $id = (int) $request->post('id');

        $model = $this->findCharacterClass($id);

        $content = $this->renderPartial('ajax/character-class', [
            'model' => $model,
        ]);

        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxRace(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $id = $request->post('id');
        $model = $this->findRace($id);

        $content = $this->renderPartial('ajax/race', [
            'model' => $model,
        ]);

        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     * Finds the Wizard model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return Wizard the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Wizard
    {
        if (($model = Wizard::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested wizard does not exist.');
    }

    /**
     * Finds the WizardQuestion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return WizardQuestion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findQuestion(int $id): WizardQuestion
    {
        if (($model = WizardQuestion::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested question does not exist.');
    }

    /**
     * Finds the Alignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return Alignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findAlignment(int $id): Alignment
    {
        if (($model = Alignment::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested alignment does not exist.');
    }

    /**
     * Finds the CharacterClass model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return Race the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findCharacterClass(int $id): CharacterClass
    {
        if (($model = CharacterClass::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested race does not exist.');
    }

    /**
     * Finds the Race model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return Race the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findRace(int $id): Race
    {
        if (($model = Race::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested race does not exist.');
    }
}
