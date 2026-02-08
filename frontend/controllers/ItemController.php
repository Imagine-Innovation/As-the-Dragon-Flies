<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\models\Item;
use frontend\components\AjaxRequest;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

/**
 * ItemController implements the CRUD actions for Item model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class ItemController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
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
                        'actions' => ['index', 'shop', 'ajax', 'ajax-images', 'view'],
                        'allow' => ManageAccessRights::isRouteAllowed($this),
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Lists all Item models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        if (Yii::$app->user->identity->is_designer) {
            return $this->render('index');
        }
        throw new UnauthorizedHttpException('Only designer users can see this page');
    }

    /**
     * Lists all Item models.
     *
     * @return string
     */
    public function actionShop(): string
    {
        $query = Item::find()->where(['>', 'cost', 0]);

        $models = $query->orderBy([
                    'item_type_id' => SORT_ASC,
                    'name' => SORT_ASC,
                ])->all();

        return $this->render('shop', [
                    'models' => $models,
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

        $request = Yii::$app->request;
        $itemTypeId = $request->post('currentTab', 1);
        Yii::debug("*** debug *** actionAjax - itemTypeId={$itemTypeId}");
        $param = [
            'modelName' => 'Item',
            'param' => ['itemTypeId' => $itemTypeId],
            'filter' => ['item_type_id' => $itemTypeId],
            'sortOrder' => [
                'sort_order' => SORT_ASC,
                'name' => SORT_ASC,
            ],
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
    public function actionAjaxImages(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $itemIdsString = $request->post('itemIds') ?? '';
        $param = [
            'modelName' => 'Item',
            'render' => 'images',
            'filter' => ['id' => explode(',', $itemIdsString)],
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse($request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Displays a single Item model.
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
     * Finds the Item model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Item the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Item
    {
        if (($model = Item::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The item you are looking for does not exist.');
    }
}
