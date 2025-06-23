<?php

namespace frontend\controllers;

use Yii;
use common\models\PlayerCart;
use common\models\Player;
use common\models\Item;
use common\models\User;
use frontend\components\Shopping;
use common\components\ManageAccessRights;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * PlayerCartController implements the CRUD actions for PlayerCart model.
 */
class PlayerCartController extends Controller {

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
                                'actions' => ['index', 'create',
                                    'update', 'delete', 'view',
                                    'shop', 'cart',
                                    'ajax-add', 'ajax-remove', 'ajax-validate',
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
     * Action for displaying the index page.
     * This action initializes a data provider with a query to fetch player carts.
     *
     * @return string The rendered index view.
     */
    public function actionIndex() {
        // Initialize a data provider with a query to fetch player carts
        $dataProvider = new ActiveDataProvider([
            'query' => PlayerCart::find(),
        ]);

        // Render the index view with the data provider
        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PlayerCart model.
     *
     * This action renders the 'view' view, which displays details of a specific
     * PlayerCart model identified by player ID and item ID.
     * If the specified model cannot be found, it throws a NotFoundHttpException.
     *
     * @param int $playerId The ID of the player associated with the cart item.
     * @param int $itemId The ID of the item in the cart.
     * @return string The rendered view.
     * @throws NotFoundHttpException if the model cannot be found.
     */
    public function actionView($playerId, $itemId) {
        // Find the PlayerCart model
        $model = $this->findModel($playerId, $itemId);

        // Render the 'view' view with the found model
        return $this->render('view', [
                    'model' => $model,
        ]);
    }

    /**
     * Creates a new PlayerCart model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * This action handles the creation of a new PlayerCart model.
     * It first initializes a new instance of PlayerCart.
     * If the request method is POST, it attempts to load data from the request
     * and save the model.
     * If the model is saved successfully, the browser is redirected to the 'view'
     * page for the newly created model.
     * If the request method is not POST, it loads default values for the model
     * and renders the 'create' view.
     *
     * @return string|\yii\web\Response The rendered 'create' view or a response
     *                                  object for redirection.
     */
    public function actionCreate() {
        // Create a new PlayerCart model
        $model = new PlayerCart();

        // Check if the request method is POST
        if ($this->request->isPost) {
            // Load data from the request and attempt to save the model
            if ($model->load($this->request->post()) && $model->save()) {
                // If model saved successfully, redirect to the 'view' page with the model's player ID and item ID
                return $this->redirect(['view', 'player_id' => $model->player_id, 'item_id' => $model->item_id]);
            }
        } else {
            // If not a POST request, load default values for the model
            $model->loadDefaultValues();
        }

        // Render the 'create' view with the model
        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * Updates an existing PlayerCart model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * This action handles the update of an existing PlayerCart model identified
     * by the provided player ID and item ID.
     * It first finds the model using the provided player ID and item ID.
     * If the request method is POST and the model is loaded with data from the
     * request and saved successfully,
     * the browser is redirected to the 'view' page for the updated model.
     * If the request method is not POST, or if there are errors in loading or
     * saving the model, the 'update' view is rendered with the model.
     *
     * @param int $playerId Player ID
     * @param int $itemId Item ID
     * @return string|\yii\web\Response The rendered 'update' view or a response object for redirection.
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($playerId, $itemId) {
        $model = $this->findModel($playerId, $itemId);

        // Check if the request method is POST and the model is loaded and saved successfully
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            // Redirect to the 'view' page for the updated model
            return $this->redirect(['view', 'player_id' => $model->player_id, 'item_id' => $model->item_id]);
        }

        // Render the 'update' view with the model
        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PlayerCart model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * This action handles the deletion of an existing PlayerCart model identified
     * by the provided player ID and item ID.
     * It first finds the model using the provided player ID and item ID, then deletes it.
     * After successful deletion, the browser is redirected to the 'index' page.
     *
     * @param int $playerId Player ID
     * @param int $itemId Item ID
     * @return \yii\web\Response The response object for redirection to the 'index' page.
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($playerId, $itemId) {
        // Find the model using the provided player ID and item ID, then delete it
        $this->findModel($playerId, $itemId)->delete();

        // Redirect to the 'index' page after successful deletion
        return $this->redirect(['index']);
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
     * This action is responsible for handling AJAX requests to add an item to the player's cart.
     * It first checks if the request is a POST request and if it is an AJAX request.
     * If not, it returns an error response.
     * Then, it retrieves the player using the `findPlayer` method.
     * If no player is found, it returns an error response.
     * Next, it retrieves the item ID from the POST data and finds the item
     * using the `findItem` method.
     * If no item is found, it returns an error response.
     * It then calculates the funding using the `getFunding` method of the `Shopping` class.
     * If the funding is insufficient, it returns an error response with a message
     * indicating the reason.
     * Otherwise, it adds the item to the player's cart using the `addToCart`
     * method and returns the result.
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
        $player = $this->findPlayer();
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

        // If the funding is insufficient, return an error response with a message
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
     * This action responds to AJAX requests by validating the items in the player's cart.
     * It checks if the request is a POST request and if it is an AJAX request.
     * If not, an error response is returned.
     * It then retrieves the player associated with the current user.
     * If no player is found, another error response is returned.
     * Next, it iterates over the player's cart items, creates a corresponding
     * PlayerItem model for each cart item, saves it, and removes the item from the cart.
     * Finally, a success response indicating that the cart is validated is returned.
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
        $player = $this->findPlayer();
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
     * This action is responsible for handling AJAX requests to remove an item
     * from the player's cart.
     * It first checks if the request is a POST request and if it is an AJAX request.
     * If not, it returns an error response.
     * Then, it retrieves the player using the `findPlayer` method.
     * If no player is found, it returns an error response.
     * Next, it retrieves the item ID and quantity from the POST data.
     * It then finds the item using the `findItem` method.
     * If no item is found, it returns an error response.
     * Finally, it calls the `removeFromCart` method to remove the item from
     * the player's cart and returns the result.
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
        $player = $this->findPlayer();
        // If no player is found, return an error response
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        // Retrieve the item ID and quantity from the POST data
        $itemId = Yii::$app->request->post('itemId');
        $quantity = Yii::$app->request->post('quantity');
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

    /**
     * Retrieves information from the player's cart via an AJAX request.
     *
     * This action responds to AJAX requests by returning a JSON object containing
     * the total count of items in the player's cart, as well as the current status
     * of the player's purse.
     * If the request is not a POST request or not an AJAX request, an error response is returned.
     * If the player is not found, another error response is returned.
     * Otherwise, the total count of items in the cart is calculated, along with the cart
     * and purse strings, and a JSON response containing this information is returned.
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
        $player = $this->findPlayer();
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
            'cartValueString' => $shopping->getCartValueString($player->playerCarts),
            'purseString' => $purseMsg,
        ];
    }

    /**
     * Handles AJAX request to retrieve the quantity of a specific item in the player's cart.
     *
     * This action is responsible for handling AJAX requests to retrieve the
     * quantity of a specific item in the player's cart.
     * It first checks if the request is a POST request and if it is an AJAX request.
     * If not, it returns an error response.
     * Then, it retrieves the player using the `findPlayer` method.
     * If no player is found, it returns an error response.
     * Next, it retrieves the item ID from the request.
     * After that, it searches for the PlayerCart model corresponding to the player ID and item ID.
     * Finally, it constructs the response containing the count of the item and returns it.
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
        $player = $this->findPlayer();
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
     * Finds the PlayerCart model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * This method is used internally to retrieve the PlayerCart model based on
     * the provided player ID and item ID.
     * It queries the PlayerCart table for a record with matching player_id and item_id values.
     * If a matching model is found, it is returned. Otherwise, a NotFoundHttpException is thrown.
     *
     * @param int $playerId Player ID
     * @param int $itemId Item ID
     * @return PlayerCart The loaded PlayerCart model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($playerId, $itemId) {
        // Find the PlayerCart model based on its primary key value
        if (($model = PlayerCart::findOne(['player_id' => $playerId, 'item_id' => $itemId])) !== null) {
            return $model;
        }

        // If the model is not found, throw a 404 HTTP exception
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Finds the cart associated with the selected player.
     *
     * This method is used internally to retrieve the cart associated with the selected player.
     * It first retrieves the player using the `findPlayer` method.
     * If a player is found, it returns the player's carts. Otherwise,
     * it throws a NotFoundHttpException.
     *
     * @return PlayerCart[] The player's carts
     * @throws NotFoundHttpException if no player is selected
     */
    protected function findCart() {
        // Find the player associated with the selected player
        $player = $this->findPlayer();

        // If a player is found, return the player's carts
        if ($player) {
            return $player->playerCarts;
        }

        // If no player is selected, throw a NotFoundHttpException
        throw new NotFoundHttpException('No player is selected. Select one and try again.');
    }

    /**
     * Finds the Player model associated with the user's current player.
     *
     * This method retrieves the user ID from the Yii::$app->user->identity object,
     * then uses it to find the corresponding User model. The current player associated
     * with the user is returned.
     *
     * @return Player|null The loaded Player model, or null if not found.
     */
    protected function findPlayer() {
        // Get the user ID from the currently logged-in user.
        $user_id = Yii::$app->user->identity->id;

        // Find the User model based on the user ID.
        $user = User::findOne(['id' => $user_id]);

        // Return the current player associated with the user.
        return $user->currentPlayer;
    }

    /**
     * Finds the User model based on its primary key value.
     *
     * This method retrieves the User model based on the provided primary key.
     * If the model is found, it is returned. If not found, a 404 HTTP exception is thrown.
     *
     * @param int $id The primary key value of the User model to be found.
     * @return User|null The loaded User model if found, or null if not found.
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findUser($id) {
        // Find the User model based on the provided primary key.
        $model = User::findOne(['id' => $id]);

        // If the model is found, return it. Otherwise, throw a 404 HTTP exception.
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
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
        $model = Item::findOne(['id' => $id]);

        // If the model is found, return it. Otherwise, throw a 404 HTTP exception.
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
