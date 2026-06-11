<?php

namespace frontend\controllers;

use common\components\AccessRightsManager;
use common\models\Item;
use common\components\AjaxRequest;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
                        'actions' => ['ajax-images'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return AccessRightsManager::isRouteAllowed($action->controller);
                        },
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
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
}
