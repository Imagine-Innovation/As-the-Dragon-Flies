<?php

namespace frontend\controllers;

use Yii;
use common\models\PlayerCart;
use common\models\Player;
use common\models\Item;
use common\models\User;
use frontend\components\Shopping;
use common\components\ManageAccessRights;
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
     * @param int $id Player ID
     * @return string The rendered shop view.
     */
    public function actionShop($id = null) {
        // Query items with cost greater than 0
        $query = Item::find()->where(['>', 'cost', 0]);

        // Fetch models and order them by item type and name
        $models = $query->orderBy([
                    'item_type_id' => SORT_ASC,
                    'name' => SORT_ASC,
                ])->all();

        // Render the shop view with the fetched models
        return $this->render('shop', [
                    'models' => $models,
                    'player_id' => $id,
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
     * @throws NotFoundHttpException if the model cannot be found.
     */
    public function actionCart() {
        // Find the PlayerCart model
        $model = $this->findCart();

        // Render the cart view with the found model
        return $this->render('cart', [
                    'models' => $model,
        ]);
    }

    /**
     * Handles AJAX request to add an item to the player's cart.
     *
     * @return array The response in JSON format
     */
    public function actionAjaxAdd() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Retrieve the player
        $playerId = Yii::$app->session->get('playerId');
        $player = Player::findOne(['id' => $playerId]);

        // If no player is found, return an error response
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        // Retrieve the item ID from the POST data and find the item
        $itemId = Yii::$app->request->post('itemId');
        $quantity = Yii::$app->request->post('quantity', 1);
        $item = $this->findItem($itemId);
        // If no item is found, return an error response
        if (!$item) {
            return ['error' => true, 'msg' => 'Item not found'];
        }

        // Create a new Shopping instance and calculate the funding
        $shopping = new Shopping();
        $funding = $shopping->getFunding($player->playerCoins, $item->cost, $item->coin);

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
     * @return array The JSON response indicating the success or failure of the cart validation.
     */
    public function actionAjaxValidate() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Retrieve the player
        $playerId = Yii::$app->session->get('playerId');
        $player = Player::findOne(['id' => $playerId]);

        // If no player is found, return an error response
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        // Get the player's cart items
        $playerCarts = $player->playerCarts;
        $shopping = new Shopping();

        // Iterate over the player's cart items
        foreach ($playerCarts as $playerCart) {
            $ret = $shopping->validatePurchase($playerCart);
            if ($ret['error']) {
                return $ret;
            }
        }

        // Return a success response indicating that the cart is validated
        return ['error' => false, 'msg' => 'Cart is validated'];
    }

    /**
     * Handles AJAX request to remove an item from the player's cart.
     *
     * @return array The response in JSON format
     */
    public function actionAjaxRemove() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Retrieve the player
        $playerId = Yii::$app->session->get('playerId');
        $player = Player::findOne(['id' => $playerId]);

        // If no player is found, return an error response
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        // Retrieve the item ID and quantity from the POST data
        $itemId = Yii::$app->request->post('itemId');
        $quantity = Yii::$app->request->post('quantity', 1);
        // Find the item
        $item = $this->findItem($itemId);
        // If no item is found, return an error response
        if (!$item) {
            return ['error' => true, 'msg' => 'Item not found'];
        }

        // Remove the item from the player's cart and return the result
        $shopping = new Shopping();
        return $shopping->removeFromCart($player, $item, $quantity);
    }

    public function actionAjaxDelete() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Retrieve the player
        $playerId = Yii::$app->session->get('playerId');
        $player = Player::findOne(['id' => $playerId]);
        // If no player is found, return an error response
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        // Retrieve the item ID and quantity from the POST data
        $itemId = Yii::$app->request->post('itemId');
        // Find the item
        $item = $this->findItem($itemId);
        // If no item is found, return an error response
        if (!$item) {
            return ['error' => true, 'msg' => 'Item not found'];
        }

        // Remove the item from the player's cart and return the result
        $shopping = new Shopping();
        return $shopping->deleteFromCart($player, $item);
    }

    /**
     * Retrieves information from the player's cart via an AJAX request.
     *
     * @return array The JSON response containing the total count of items in the player's cart,
     *               the cart string, and the purse string.
     */
    public function actionAjaxInfo() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Retrieve the player
        $playerId = Yii::$app->session->get('playerId');
        $player = Player::findOne(['id' => $playerId]);
        // If no player is found, return an error response
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        // Calculate the total quantity of items in the player's cart
        $sum = PlayerCart::find()->where(['=', 'player_id', $player->id])->sum('quantity');
        $count = $sum ?? 0;

        // Construct a message indicating the player's name and their purse status based on the purse string
        $shopping = new Shopping();
        $str = $shopping->getPurseValueString($player->playerCoins);
        $playerDesc = $player->name . ' is a ' . $player->alignment->name . ' ' . $player->race->name . ' ' . $player->class->name;
        $purseMsg = $str !== "" ? "$playerDesc that currently has $str" : "$player->name's purse is empty";

        // Construct the response containing the count of items and the cart string, and return it
        return [
            'error' => false, 'msg' => '', 'count' => $count,
            'cartString' => "You have " . ($count > 0 ? $count : "no") . " article" . ($count > 1 ? "s" : "") . " in your cart",
            //'cartValueString' => $shopping->getCartValueString($player->playerCarts),
            'cartValueString' => $shopping->getCartValueString($this->findCart()),
            'purseString' => $purseMsg,
        ];
    }

    /**
     * Handles AJAX request to retrieve the quantity of a specific item in the player's cart.
     *
     * @return array The response in JSON format
     */
    public function actionAjaxItemCount() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Retrieve the player
        $playerId = Yii::$app->session->get('playerId');
        $player = Player::findOne(['id' => $playerId]);
        // If no player is found, return an error response
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        // Retrieve the item ID from the request
        $itemId = Yii::$app->request->post('itemId');

        // Find the PlayerCart model corresponding to the player ID and item ID
        $model = PlayerCart::findOne(['player_id' => $player->id, 'item_id' => $itemId]);

        // Construct the response containing the count of the item and return it
        return [
            'error' => false, 'msg' => '', 'count' => $model ? $model->quantity : 0
        ];
    }

    /**
     * Finds the cart associated with the selected player.
     *
     * @return PlayerCart[] The player's carts
     * @throws NotFoundHttpException if no player is selected
     */
    protected function findCart() {
        // Find the player associated with the selected player
        $playerId = Yii::$app->session->get('playerId');

        // If a player is found, return the player's carts
        if ($playerId) {
            // Find the PlayerCart model based on its primary key value
            $model = PlayerCart::find()
                    ->where(['player_id' => $playerId])
                    ->all();

            if ($model !== null) {
                return $model;
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
     * @return Item|null The loaded Item model if found, or null if not found.
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findItem($id) {
        // Find the Item model based on the provided primary key.
        $item = Item::findOne(['id' => $id]);

        // If the model is found, return it. Otherwise, throw a 404 HTTP exception.
        if ($item) {
            return $item;
        }
        throw new NotFoundHttpException("The player's cart you are looking for does not exist.");
    }
}
