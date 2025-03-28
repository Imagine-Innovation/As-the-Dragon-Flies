<?php

namespace frontend\controllers;

use Yii;
use common\models\PlayerItem;
use frontend\components\Inventory;
use common\components\ManageAccessRights;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * PlayerItemController implements the CRUD actions for PlayerItem model.
 */
class PlayerItemController extends Controller {

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
                                    'index', 'pack',
                                    'ajax-can-pack', 'ajax-toggle', 'ajax-pack-info'
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
     * Action for displaying the index page.
     * This action initializes a data provider with a query to fetch player packs.
     *
     * @return string The rendered index view.
     */
    public function actionIndex() {
        // Find the PlayerItem model
        /**/
        $playerId = Yii::$app->session->get('playerId');

        $models = PlayerItem::find()
                ->select('player_item.*')
                ->innerJoin('item', 'player_item.item_id = item.id')
                ->innerJoin('item_type', 'item.item_type_id = item_type.id')
                ->where(['player_item.player_id' => $playerId])
                ->orderBy(['item_type.sort_order' => SORT_ASC, 'item.name' => SORT_ASC])
                ->all();
        /**/
        //$models = $this->findPlayerItems();
        // Render the index view with the data provider
        return $this->render('index', [
                    'models' => $models,
        ]);
    }

    /**
     * Displays the shopping pack.
     *
     * This action renders the 'pack' view, which displays the shopping pack 
     * containing PlayerItem models.
     * If the specified model cannot be found, it throws a NotFoundHttpException.
     *
     * @return string The rendered pack view.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    public function actionPack() {
        // Find the PlayerItem model
        $models = $this->findPack();

        // Render the pack view with the found model
        return $this->render('pack', [
                    'models' => $models,
        ]);
    }

    private function _prepareAjax($request) {
        if (!$request->isPost || !$request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $player = $this->findPlayer();
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        $itemId = Yii::$app->request->post('item_id');
        $status = Yii::$app->request->post('status', 0);
        $item = $this->findModel($player->id, $itemId);
        if (!$item) {
            return ['error' => true, 'msg' => 'Item not found'];
        }
        return ['error' => false, 'player' => $player, 'item' => $item, 'status' => $status];
    }

    public function actionAjaxToggle() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $checkAjax = $this->_prepareAjax($this->request);

        if ($checkAjax['error']) {
            return $checkAjax;
        }

        $player = $checkAjax['player'];
        $item = $checkAjax['item'];
        $status = $checkAjax['status'];

        $inventory = new Inventory();
        $container = $inventory->getContainer($player);
        if (!$container) {
            return ['error' => true, 'msg' => 'You cannot pack anything. Buy a container before you pack.'];
        }

        if ($status == 1) {
            return $inventory->addToPack($item, $container);
        } else {
            return $inventory->removeFromPack($item, $container);
        }
    }

    /**
     * Finds the PlayerItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * This method is used internally to retrieve the PlayerItem model based on 
     * the provided player ID and item ID.
     * It queries the PlayerItem table for a record with matching player_id and item_id values.
     * If a matching model is found, it is returned. Otherwise, a NotFoundHttpException is thrown.
     *
     * @param int $player_id Player ID
     * @param int $itemId Item ID
     * @return PlayerItem The loaded PlayerItem model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($player_id, $itemId) {
        // Find the PlayerItem model based on its primary key value
        if (($model = PlayerItem::findOne(['player_id' => $player_id, 'item_id' => $itemId])) !== null) {
            return $model;
        }

        // If the model is not found, throw a 404 HTTP exception
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Finds the pack associated with the selected player.
     *
     * This method is used internally to retrieve the pack associated with the selected player.
     * It first retrieves the player using the `findPlayer` method.
     * If a player is found, it returns the player's packs. Otherwise, 
     * it throws a NotFoundHttpException.
     *
     * @return PlayerItem[] The player's packs
     * @throws NotFoundHttpException if no player is selected
     */
    protected function findPack() {
        // Find the PlayerItem model based on its primary key value
        $player = $this->findPlayer();
        // If a player is found, return the player's packs
        if ($player) {
            $pack = PlayerItem::findAll(['player_id' => $player->id, 'is_carrying' => 1]);

            return $pack;
        }

        // If no player is selected, throw a NotFoundHttpException
        throw new NotFoundHttpException('No player is selected. Select one and try again.');
    }

    protected function findPlayerItems() {
        $player = $this->findPlayer();

        if ($player) {
            return $player->playerItems;
        }

        throw new NotFoundHttpException('No player is selected. Select one and try again.');
    }

    protected function findPlayer() {
        $user = Yii::$app->user->identity;

        if ($user) {
            return Yii::$app->session->get('currentPlayer');
        }

        throw new NotFoundHttpException('Internal error, User not found');
    }
}
