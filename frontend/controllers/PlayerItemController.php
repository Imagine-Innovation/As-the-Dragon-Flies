<?php

namespace frontend\controllers;

use common\models\Item;
use common\models\Player;
use common\models\PlayerBody;
use common\models\PlayerItem;
use common\components\ManageAccessRights;
use frontend\components\Inventory;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * PlayerItemController implements the CRUD actions for PlayerItem model.
 */
class PlayerItemController extends Controller
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
                                'actions' => [
                                    'index', 'see-package',
                                    'ajax-equipment', 'ajax-toggle', 'ajax-equip-player', 'ajax-disarm-player'
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
    public function actionSeePackage() {
        // Find the PlayerItem model based on its primary key value
        $player = $this->findPlayer();

        // If a player is found, return the player's packs
        $package = PlayerItem::findAll([
            'player_id' => $player->id,
            'is_carrying' => 1
        ]);

        // Render the pack view with the found model
        return $this->render('pack', [
                    'models' => $package,
        ]);
    }

    private function getEquipmentData(array &$playerItems): array {
        $playerItemData = [];
        $items = [];

        foreach ($playerItems as $playerItem) {
            $itemType = $playerItem->item_type;

            $itemData = [
                'itemId' => $playerItem->item_id,
                'name' => $playerItem->item_name,
                'image' => $playerItem->image,
                'quantity' => $playerItem->quantity,
                'isProficient' => $playerItem->is_proficient,
                'isTwoHanded' => $playerItem->is_two_handed,
                'buttonId' => "equipButton-{$playerItem->item_id}"
            ];
            $playerItemData[$itemType][] = $itemData;
            $items[] = $itemData;
        }
        return ['playerItems' => $playerItemData, 'items' => $items];
    }

    public function actionAjaxEquipment() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a GET request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->get('playerId');
        $playerBody = $this->findPlayerBody($playerId);

        // If a player is found, return the player's packs
        $playerItems = PlayerItem::findAll(['player_id' => $playerId, 'is_carrying' => 1]);
        $playerBodyData = $this->getPlayerBodyData($playerBody);
        $equipmentData = $this->getEquipmentData($playerItems);

        // Render the pack view with the found model
        $content = $this->renderPartial('ajax\package', ['playerItems' => $equipmentData['playerItems']]);
        $contentModal = $this->renderPartial('ajax\svg', ['playerBodyData' => $playerBodyData, 'withId' => true, 'withOffcanvas' => false]);
        $contentAside = $this->renderPartial('ajax\svg', ['playerBodyData' => $playerBodyData, 'withId' => false, 'withOffcanvas' => false]);
        $contentOffcanvas = $this->renderPartial('ajax\svg', ['playerBodyData' => $playerBodyData, 'withId' => false, 'withOffcanvas' => true]);

        return ['error' => false, 'msg' => '',
            'content' => $content,
            'contentModal' => $contentModal,
            'contentAside' => $contentAside,
            'contentOffcanvas' => $contentOffcanvas,
            'items' => $equipmentData['items'],
            'data' => $playerBodyData
        ];
    }

    private function getPlayerBodyData(PlayerBody &$playerBody): array {
        $data = [];

        foreach (PlayerItem::BODY_ZONE as $property => $zone) {
            $playerItem = $playerBody->$property;
            if ($playerItem) {
                $data[$zone] = [
                    'itemId' => $playerItem->item_id,
                    'itemName' => $playerItem->item_name,
                    'image' => $playerItem->image,
                ];
            } else {
                $data[$zone] = [
                    'itemId' => null,
                    'itemName' => null,
                    'image' => null,
                ];
            }
        }

        return $data;
    }

    private function savePlayerBody(PlayerBody &$playerBody, string $itemName, bool $equiped = true): array {
        Yii::debug("*** debug *** savePlayerBody - itemName={$itemName}");
        if ($playerBody->save()) {
            $playerBodyData = $this->getPlayerBodyData($playerBody);

            $contentModal = $this->renderPartial('ajax\svg', ['playerBodyData' => $playerBodyData, 'withId' => true, 'withOffcanvas' => false]);
            $contentAside = $this->renderPartial('ajax\svg', ['playerBodyData' => $playerBodyData, 'withId' => false, 'withOffcanvas' => false]);
            $contentOffcanvas = $this->renderPartial('ajax\svg', ['playerBodyData' => $playerBodyData, 'withId' => false, 'withOffcanvas' => true]);

            return [
                'error' => false,
                'msg' => "{$itemName} is " . ($equiped ? 'equiped' : 'disarmed'),
                'contentModal' => $contentModal,
                'contentAside' => $contentAside,
                'contentOffcanvas' => $contentOffcanvas,
                'data' => $playerBodyData
            ];
        }
        return [
            'error' => true,
            'msg' => 'Player equipment failed: ' . ArrayHelper::getColumn($playerBody->errors, 0, false)[0]
        ];
    }

    private function equipPlayerWithArmor(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone): array {
        if ($bodyZone !== PlayerItem::BODY_CHEST_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }
        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        $playerBody->chest_item_id = $playerItem->item_id;

        return $this->savePlayerBody($playerBody, $playerItem->item_name);
    }

    private function equipPlayerWithHelmet(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone): array {
        if ($bodyZone !== PlayerItem::BODY_HEAD_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }
        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        $playerBody->head_item_id = $playerItem->item_id;
        return $this->savePlayerBody($playerBody, $playerItem->item_name);
    }

    private function equipPlayerWithShield(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone): array {
        if ($bodyZone !== PlayerItem::BODY_LEFT_HAND_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }

        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        $playerBody->left_hand_item_id = $playerItem->item_id;

        return $this->savePlayerBody($playerBody, $playerItem->item_name);
    }

    private function equipPlayerWithTool(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone): array {
        if ($bodyZone !== PlayerItem::BODY_RIGHT_HAND_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }

        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        $playerBody->right_hand_item_id = $playerItem->item_id;
        return $this->savePlayerBody($playerBody, $playerItem->item_name);
    }

    private function equipWithQuiver(PlayerBody &$playerBody, Item &$weapon) {
        Yii::debug("*** debug *** equipWithQuiver - weapon={$weapon->name}");
        $haystack = strtolower($weapon->name);

        if (str_contains($haystack, 'bow')) {
            // Special treatment for weapons that use arrows:
            // carry arrows in a quiver on the player's back.
            $bow = $weapon->weapon;
            $amunitionId = $bow->amunition_id;
            $playerBody->back_item_id = $amunitionId;
        }
    }

    private function equipPlayerWithWeapon(PlayerItem &$playerItem, Item &$weapon, PlayerBody &$playerBody, string $bodyZone): array {
        $weaponName = $weapon->name;
        Yii::debug("*** debug *** equipPlayerWithWeapon - bodyZone={$bodyZone}, item={$weaponName}");
        if ($bodyZone !== PlayerItem::BODY_RIGHT_HAND_ZONE && $bodyZone !== PlayerItem::BODY_LEFT_HAND_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }
        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        // If the weapon is two-handed, simply use both hands.
        if ($playerItem->is_two_handed) {
            $playerBody->right_hand_item_id = $playerItem->item_id;
            $playerBody->left_hand_item_id = $playerItem->item_id;

            // Special treatment for weapons that use arrows
            $this->equipWithQuiver($playerBody, $weapon);

            return $this->savePlayerBody($playerBody, $weaponName);
        }

        return $this->holdWeapon($weapon, $playerBody, $bodyZone);
    }

    /**
     * Test if the player was previously holding a two-handed weapon.
     * If so, disarm the other hand before picking up the one-handed weapon.
     * This function is made generic and relies on the properties array
     * to manage hand laterality.
     *
     * @param PlayerBody $playerBody
     * @param array $handProperties
     * @return void
     */
    private function disarmTheOtherHand(PlayerBody &$playerBody, array $handProperties): void {
        Yii::debug("*** debug *** disarmTheOtherHand - handProperties: " . print_r($handProperties, true));
        $otherHandItemIdField = $handProperties['otherHandItemIdField'];
        if ($playerBody->$otherHandItemIdField) {
            // check if the other hand is not holding a two-handed weapon
            $property = $handProperties['otherHandProperty'];
            $otherHand = $playerBody->$property;
            if ($otherHand->is_two_handed) {
                // The player has a two-handed weapon in his hand.
                // It is therefore also in the other hand.
                // Disarm it!
                $playerBody->$otherHandItemIdField = null;
            }
        }
    }

    /**
     * Before allowing the player to hold the weapon, check that he is not
     * holding an identical weapon in the other hand.
     * If this is the case, check that they have at least two.
     * If they only have one, disarm the other hand.
     *
     * @param Item $weapon
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return array
     */
    private function holdWeapon(Item &$weapon, PlayerBody &$playerBody, string $bodyZone): array {
        Yii::debug("*** debug *** holdWeapon - bodyZone={$bodyZone}, weapon={$weapon->name}");

        $handProperties = PlayerItem::HAND_PROPERTIES[$bodyZone];

        $otherHandItemIdField = $handProperties['otherHandItemIdField'];
        if ($playerBody->$otherHandItemIdField) {
            // check if the other hand is not holding the same one-handed weapon
            $property = $handProperties['otherHandProperty'];
            $otherHand = $playerBody->$property;
            $itemId = $playerBody->$otherHandItemIdField;
            if (
                    $otherHand->is_two_handed === 0 // The Weapon is not two-handed AND
                    && $itemId === $weapon->id      // The player holds the same weapon in the other hand AND
                    && $otherHand->quantity < 2     // He does not have mode than one weapon of this sort
            ) {
                // The player does not have 2 or more identical weapon => Disarm the other hand.
                $otherHandBodyZone = PlayerItem::HAND_PROPERTIES[$bodyZone]['otherHandBodyZone'];
                $this->disarmPreviousItem($playerBody, $otherHandBodyZone);
            }
        }
        $itemIdField = $handProperties['itemIdField'];
        $playerBody->$itemIdField = $weapon->id;

        // Special treatment for weapons that use arrows
        $this->equipWithQuiver($playerBody, $weapon);

        return $this->savePlayerBody($playerBody, $weapon->name);
    }

    private function equipPlayer(PlayerItem &$playerItem, string $bodyZone): array {
        $playerBody = $this->findPlayerBody($playerItem->player_id);
        $item = $playerItem->item;
        $itemType = $playerItem->item_type;
        Yii::debug("*** debug *** equipPlayer - itemType={$itemType}, bodyZone={$bodyZone}");

        return match ($itemType) {
            'Armor' => $this->equipPlayerWithArmor($playerItem, $playerBody, $bodyZone),
            'Helmet' => $this->equipPlayerWithHelmet($playerItem, $playerBody, $bodyZone),
            'Shield' => $this->equipPlayerWithShield($playerItem, $playerBody, $bodyZone),
            'Tool' => $this->equipPlayerWithTool($playerItem, $playerBody, $bodyZone),
            'Weapon' => $this->equipPlayerWithWeapon($playerItem, $item, $playerBody, $bodyZone),
            default => throw new \InvalidArgumentException("Unknown item type: {$itemType}"),
        };
    }

    public function actionAjaxEquipPlayer() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $itemId = $request->post('itemId');

        $playerItem = $this->findModel($playerId, $itemId);

        $bodyZone = $request->post('bodyZone');
        Yii::debug("*** debug *** actionAjaxEquipPlayer - playerId={$playerId}, itemId={$itemId}, bodyZone={$bodyZone}");

        return $this->equipPlayer($playerItem, $bodyZone);
    }

    private function disarmWeapon(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone, string $weaponName): void {
        Yii::debug("*** debug *** disarmWeapon - bodyZone={$bodyZone}, weaponName={$weaponName}");
        $haystack = strtolower($weaponName);

        if (str_contains($haystack, 'bow')) {
            // Special treatment for weapons that use arrows:
            // remove the quiver from the player's back.
            $playerBody->back_item_id = null;
        }

        $handProperties = PlayerItem::HAND_PROPERTIES[$bodyZone];

        if ($playerItem->is_two_handed === 1) {
            $this->disarmTheOtherHand($playerBody, $handProperties);
        }
    }

    private function clearBodyZone(PlayerBody &$playerBody, string $bodyZone): void {
        $bodyProperties = PlayerItem::BODY_PROPERTIES[$bodyZone];

        $itemIdField = $bodyProperties['itemIdField'];
        $playerBody->$itemIdField = null;
    }

    private function disarmPreviousItem(PlayerBody &$playerBody, string $bodyZone): void {
        $bodyProperties = PlayerItem::BODY_PROPERTIES[$bodyZone];
        $property = $bodyProperties['property'];
        Yii::debug("*** debug *** disarmPreviousItem - bodyZone={$bodyZone}, property={$property}");
        $playerItem = $playerBody->$property;

        if (!$playerItem) {
            return;
        }

        $previousItemName = $playerItem->item_name;
        Yii::debug("*** debug *** disarmPreviousItem - bodyZone={$bodyZone}, previousItem={$previousItemName}");
        if ($playerItem->item_type === 'Weapon') {
            $this->disarmWeapon($playerItem, $playerBody, $bodyZone, $previousItemName);
        }

        $this->clearBodyZone($playerBody, $bodyZone);
    }

    private function disarmPlayer(PlayerBody &$playerBody, string $bodyZone): array {
        $bodyProperties = PlayerItem::BODY_PROPERTIES[$bodyZone];

        $property = $bodyProperties['property'];
        Yii::debug("*** debug *** disarmPlayer - bodyZone={$bodyZone}, property={$property}");
        $playerItem = $playerBody->$property;

        if (!$playerItem) {
            return [
                'error' => true,
                'msg' => "Body zone {$bodyZone} has no attached item"
            ];
        }
        $previousItem = $playerItem->item;

        $this->disarmPreviousItem($playerBody, $bodyZone);

        return $this->savePlayerBody($playerBody, $previousItem->name, false);
    }

    public function actionAjaxDisarmPlayer() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $bodyZone = $request->post('bodyZone');

        $playerBody = $this->findPlayerBody($playerId);

        return $this->disarmPlayer($playerBody, $bodyZone);
    }

    private function prepareAjax($request) {
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

        $checkAjax = $this->prepareAjax($this->request);

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
     * @param int $playerId Player ID
     * @param int $itemId Item ID
     * @return PlayerItem The loaded PlayerItem model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($playerId, $itemId) {
        // Find the PlayerItem model based on its primary key value
        if (($model = PlayerItem::findOne(['player_id' => $playerId, 'item_id' => $itemId])) !== null) {
            return $model;
        }

        // If the model is not found, throw a 404 HTTP exception
        throw new NotFoundHttpException("The player's item you are looking for does not exist. playerId={$playerId}, itemId={$itemId}");
    }

    protected function findPlayer(?int $playerId = null): Player {
        $player = Player::findOne(['id' => ($playerId ?? Yii::$app->session->get('playerId'))]);

        if ($player) {
            return $player;
        }

        throw new NotFoundHttpException("The player (playerId={$playerId}) you are looking for does not exist.");
    }

    protected function findPlayerBody(?int $playerId = null) {
        $player = $this->findPlayer($playerId);

        $playerBody = $player->playerBody;

        if (!$playerBody) {
            $playerBody = new PlayerBody(['player_id' => $player->id]);
            if (!$playerBody->save()) {
                throw new \Exception(implode("<br />", ArrayHelper::getColumn($playerBody->errors, 0, false)));
            }
        }
        return $playerBody;
    }
}
