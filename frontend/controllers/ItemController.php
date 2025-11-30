<?php

namespace frontend\controllers;

use Yii;
use common\models\Item;
use frontend\components\AjaxRequest;
use common\components\ManageAccessRights;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * ItemController implements the CRUD actions for Item model.
 */
class ItemController extends Controller {

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
                                'actions' => ['index', 'shop', 'ajax', 'ajax-images', 'view'],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                ]
        );
    }

    /**
     * Lists all Item models.
     *
     * @return string
     */
    public function actionIndex() {
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
    public function actionShop() {
        $query = Item::find()->where(['>', 'cost', 0]);

        $models = $query->orderBy([
                    'item_type_id' => SORT_ASC,
                    'name' => SORT_ASC,
                ])->all();

        return $this->render('shop', [
                    'models' => $models,
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

        $request = Yii::$app->request;
        $itemTypeId = $request->post('currentTab', 1);
        Yii::debug("*** debug *** actionAjax - itemTypeId=" . $itemTypeId ?? 'null');
        $param = [
            'modelName' => 'Item',
            'param' => ['itemType' => $itemTypeId],
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

    public function actionAjaxImages() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $itemIds = $request->post('itemIds');
        Yii::debug("*** debug *** actionAjaxImages - itemIds=" . $itemIds ?? 'null');
        $param = [
            'modelName' => 'Item',
            'render' => 'images',
            'filter' => ['id' => explode(',', $itemIds)],
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
    public function actionView($id) {
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
    protected function findModel(int $id) {
        if (($model = Item::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The item you are looking for does not exist.');
    }
}
