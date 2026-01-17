<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\helpers\MixedHelper;
use common\models\PlayerCart;
use common\models\Player;
use common\models\Item;
use frontend\components\Shopping;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * PlayerCartController implements the CRUD actions for PlayerCart model.
 */
class PlayerCartController extends Controller
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
                                'actions' => ['shop', 'cart',
                                    'ajax-add', 'ajax-remove', 'ajax-delete', 'ajax-validate',
                                    'ajax-info', 'ajax-item-count',
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
     * Action for displaying the shop page.
     * This action fetches items with a cost greater than 0,
     * orders them by item type and name,
     * and then renders the 'shop' view with the fetched models.
     *
     * @param int|null $playerId Player ID
     * @return string The rendered shop view.
     */
    public function actionShop(?int $playerId = null): string {
        // Query items with cost greater than 0
        // Fetch models and order them by item type and name
        $items = Item::find()
                        ->where(['>', 'cost', 0])
                        ->orderBy([
                            'item_type_id' => SORT_ASC,
                            'name' => SORT_ASC,
                        ])->all();

        return $this->render('shop', [
                    'models' => $items,
                    'playerId' => $playerId,
        ]);
    }

    /**
     * Displays the shopping cart.
     *
     * This action renders the 'cart' view, which displays the shopping cart
     * containing PlayerCart models.
     * If the specified model cannot be found, it throws a NotFoundHttpException.
     *
     * @return string The rendered cart view.
     */
    public function actionCart(): string {
        $playerCarts = $this->findCart();

        return $this->render('cart', [
                    'playerCarts' => $playerCarts,
        ]);
    }

    /**
     * Handles AJAX request to add an item to the player's cart.
     *
     * @return array{error: bool, msg: string, content?: string} The response in JSON format
     */
    public function actionAjaxAdd(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }


        $playerId = (int) Yii::$app->session->get('playerId');
        $player = $this->findPlayer($playerId);

        $itemId = (int) Yii::$app->request->post('itemId');
        $quantity = (int) Yii::$app->request->post('quantity', 1);
        $item = $this->findItem($itemId);

        $shopping = new Shopping();
        $funding = $shopping->getFunding($player->playerCoins, $item->cost ?? 0, $item->coin);

        // If the funding is insufficient, return an error response
        if ($funding <= 0) {
            return [
                'error' => true,
                'msg' => $shopping->purchaseNotPossibleMessage($player->playerCoins, $item)
            ];
        }

        // Add the item to the player's cart and return the result
        return $shopping->addToCart($player, $item, $quantity);
    }

    /**
     * Validates the items in the player's cart via an AJAX request.
     *
     * @return array{error: bool, msg: string, content?: string} The JSON response indicating the success or failure of the cart validation.
     */
    public function actionAjaxValidate(): array {

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }


        $playerId = (int) Yii::$app->session->get('playerId');
        $player = $this->findPlayer($playerId);

        $playerCarts = $player->playerCarts;
        $shopping = new Shopping();

        foreach ($playerCarts as $playerCart) {
            $ret = $shopping->validatePurchase($playerCart);
            if ($ret['error']) {
                return $ret;
            }
        }

        return ['error' => false, 'msg' => 'Cart is validated'];
    }

    /**
     * Handles AJAX request to remove an item from the player's cart.
     *
     * @return array{error: bool, msg: string, content?: string} The response in JSON format
     */
    public function actionAjaxRemove(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }


        $playerId = (int) Yii::$app->session->get('playerId');
        $player = $this->findPlayer($playerId);

        $itemId = (int) Yii::$app->request->post('itemId');
        $quantity = (int) Yii::$app->request->post('quantity', 1);

        $item = $this->findItem($itemId);

        // Remove the item from the player's cart and return the result
        $shopping = new Shopping();
        return $shopping->removeFromCart($player, $item, $quantity);
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxDelete(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $playerId = (int) Yii::$app->session->get('playerId');
        $player = $this->findPlayer($playerId);

        $itemId = (int) Yii::$app->request->post('itemId');
        $item = $this->findItem($itemId);

        // Remove the item from the player's cart and return the result
        $shopping = new Shopping();
        return $shopping->deleteFromCart($player, $item);
    }

    /**
     * Retrieves information from the player's cart via an AJAX request.
     *
     * @return array{
     *      error: bool,
     *      msg: string,
     *      count?: int,
     *      cartString?: string,
     *      cartValueString?: string,
     *      purseString?: string
     * }
     * The JSON response containing the total count of items in the player's cart,
     * the cart string, and the purse string.
     */
    public function actionAjaxInfo(): array {

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }


        $playerId = (int) Yii::$app->session->get('playerId');
        $player = $this->findPlayer($playerId);

        // Calculate the total quantity of items in the player's cart
        $sum = PlayerCart::find()->where(['player_id' => $player->id])->sum('quantity');
        $count = MixedHelper::toInt($sum);

        // Construct a message indicating the player's name and his purse status based on the purse string
        $shopping = new Shopping();
        $str = $shopping->getPurseValueString($player->playerCoins);
        $playerDesc = "{$player->name} is a {$player->alignment?->name} {$player->race->name} {$player->class->name}";
        $purseMsg = $str !== '' ? "{$playerDesc} that currently has {$str}" : "{$player->name}'s purse is empty";

        return [
            'error' => false, 'msg' => '', 'count' => $count,
            'cartString' => "You have " . ($count > 0 ? $count : "no") . " article" . ($count > 1 ? "s" : '') . " in your cart",
            'cartValueString' => $shopping->getCartValueString($this->findCart()),
            'purseString' => $purseMsg,
        ];
    }

    /**
     * Handles AJAX request to retrieve the quantity of a specific item in the player's cart.
     *
     * @return array{error: bool, msg: string, content?: string, count?: int}
     */
    public function actionAjaxItemCount(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }


        $playerId = (int) Yii::$app->session->get('playerId');
        $player = $this->findPlayer($playerId);

        $itemId = (int) Yii::$app->request->post('itemId');

        // Find the PlayerCart model corresponding to the player ID and item ID
        $model = PlayerCart::findOne(['player_id' => $player->id, 'item_id' => $itemId]);

        // Construct the response containing the count of the item and return it
        return ['error' => false, 'msg' => '', 'count' => $model ? $model->quantity : 0];
    }

    /**
     * Finds the cart associated with the selected player.
     *
     * @return PlayerCart[] The player's carts
     * @throws NotFoundHttpException if no player is selected
     */
    protected function findCart(): array {
        // Find the player associated with the selected player
        $playerId = (int) Yii::$app->session->get('playerId');

        // If a player is found, return the player's carts
        if ($playerId) {
            // Find the PlayerCart model based on its primary key value
            $models = PlayerCart::find()
                    ->where(['player_id' => $playerId])
                    ->all();

            if (!empty($models)) {
                return $models;
            }

            // If the model is not found, throw a 404 HTTP exception
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        // If no player is selected, throw a NotFoundHttpException
        throw new NotFoundHttpException('No player is selected. Select one and try again.');
    }

    /**
     * Finds the Item model based on its primary key value.
     *
     * This method retrieves the Item model based on the provided primary key.
     * If the model is found, it is returned. If not found, a 404 HTTP exception is thrown.
     *
     * @param int $id The primary key value of the Item model to be found.
     * @return Item The loaded Item model if found, or null if not found.
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findItem(int $id): Item {
        // Find the Item model based on the provided primary key.
        $item = Item::findOne(['id' => $id]);

        // If the model is found, return it. Otherwise, throw a 404 HTTP exception.
        if ($item) {
            return $item;
        }
        throw new NotFoundHttpException("The iten you are looking for does not exist.");
    }

    /**
     * Finds the Player model based on its primary key value.
     *
     * This method retrieves the Player model based on the provided primary key.
     * If the model is found, it is returned. If not found, a 404 HTTP exception is thrown.
     *
     * @param int $id The primary key value of the Item model to be found.
     * @return Player The loaded Item model if found, or null if not found.
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findPlayer(int $id): Player {
        // Find the Player model based on the provided primary key.
        $player = Player::findOne(['id' => $id]);

        // If the model is found, return it. Otherwise, throw a 404 HTTP exception.
        if ($player) {
            return $player;
        }
        throw new NotFoundHttpException("The player you are looking for does not exist.");
    }
}
