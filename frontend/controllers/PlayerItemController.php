<?php

namespace frontend\controllers;

use Yii;
use common\models\Item;
use common\models\Player;
use common\models\PlayerBody;
use common\models\PlayerItem;
use frontend\components\Inventory;
use common\components\ManageAccessRights;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * PlayerItemController implements the CRUD actions for PlayerItem model.
 */
class PlayerItemController extends Controller {

    const BODY_HEAD = 'equipmentHeadZone';
    const BODY_CHEST = 'equipmentChestZone';
    const BODY_RIGHT_HAND = 'equipmentRightHandZone';
    const BODY_LEFT_HAND = 'equipmentLeftHandZone';
    // Define the properties to be used based on hand laterality
    const PROPERTIES = [
        self::BODY_RIGHT_HAND => [
            'otherHand' => 'leftHand',
            'otherZoneId' => 'left_hand_item_id',
            'zoneIdToEquip' => 'right_hand_item_id'
        ],
        self::BODY_LEFT_HAND => [
            'otherHand' => 'rightHand',
            'otherZoneId' => 'right_hand_item_id',
            'zoneIdToEquip' => 'left_hand_item_id'
        ]
    ];

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
                                    'ajax-equipment', 'ajax-toggle', 'ajax-equip-player'
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

    private function getEquipmentData(array $playerItems): array {
        $itemTypes = [];
        $playerItemData = [];
        $itemData = [];

        foreach ($playerItems as $playerItem) {
            $itemType = $playerItem->item_type;

            $itemTypes[$itemType] = $itemType;
            $item = $playerItem->item;
            $data = [
                'itemId' => $item->id,
                'name' => $item->name,
                'image' => $item->image,
                'isProficient' => $playerItem->is_proficient,
                'isEquiped' => $playerItem->is_equiped,
                'isTwoHanded' => $playerItem->is_two_handed,
                'buttonId' => "equipButton-{$item->id}"
            ];
            $playerItemData[$itemType][] = $data;
            $itemData[] = $data;
        }

        return [
            'itemTypes' => $itemTypes,
            'playerItems' => $playerItemData,
            'itemData' => $itemData
        ];
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
        if (!$playerId) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        // If a player is found, return the player's packs
        $playerItems = PlayerItem::findAll(['player_id' => $playerId, 'is_carrying' => 1]);

        $equipmentData = $this->getEquipmentData($playerItems);

        // Render the pack view with the found model
        $content = $this->renderPartial('ajax\package', [
            'itemTypes' => $equipmentData['itemTypes'],
            'playerItems' => $equipmentData['playerItems'],
        ]);
        return ['error' => false, 'msg' => '',
            'content' => $content,
            'itemData' => $equipmentData['itemData']
        ];
    }

    private function getItemIds(PlayerBody &$playerBody, string $property, array &$itemIds): void {
        $itemId = $playerBody->$property;
        if ($itemId) {
            $itemIds[] = $itemId;
        }
    }

    private function getPlayerBodyData(PlayerBody &$playerBody): array {
        $itemIds = [];
        $data = [];

        $this->getItemIds($playerBody, 'head_item_id', $itemIds);
        $this->getItemIds($playerBody, 'chest_item_id', $itemIds);
        $this->getItemIds($playerBody, 'right_hand_item_id', $itemIds);
        $this->getItemIds($playerBody, 'left_hand_item_id', $itemIds);

        $items = Item::findAll(['id' => $itemIds]);

        foreach ($items as $item) {
            $data[] = [
                'itemId' => $item->id,
                'itemName' => $item->name,
                'image' => $item->image,
            ];
        }

        return [
            'itemIds' => $itemIds,
            'data' => $data
        ];
    }

    private function savePlayerBody(PlayerBody &$playerBody, Item &$item): array {
        Yii::debug("*** debug *** savePlayerBody - playerId={$playerBody->player_id}, item={$item->name}");
        if ($playerBody->save()) {
            // Set the 'is_equiped' attribute of PlayerItem model to false
            PlayerItem::updateAll(
                    ['is_equiped' => 0],
                    ['player_id' => $playerBody->player_id]
            );

            $playerBodyData = $this->getPlayerBodyData($playerBody);

            PlayerItem::updateAll(
                    ['is_equiped' => 1],
                    ['player_id' => $playerBody->player_id, 'item_id' => $playerBodyData['itemIds']]
            );

            return [
                'error' => false,
                'msg' => "{$item->name} is equiped",
                'data' => $playerBodyData['data']
            ];
        }
        return [
            'error' => true,
            'msg' => 'Player equipment failed: ' . ArrayHelper::getColumn($playerBody->errors, 0, false)[0]
        ];
    }

    private function equipPlayerWithArmor(PlayerItem &$playerItem, Item &$item, PlayerBody &$playerBody, string $bodyZone): array {
        if ($bodyZone !== self::BODY_CHEST) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }
        $playerBody->chest_item_id = $playerItem->item_id;

        return $this->savePlayerBody($playerBody, $item);
    }

    private function equipPlayerWithHelmet(PlayerItem &$playerItem, Item &$item, PlayerBody &$playerBody, string $bodyZone): array {
        if ($bodyZone !== self::BODY_HEAD) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }
        $playerBody->head_item_id = $playerItem->item_id;
        return $this->savePlayerBody($playerBody, $item);
    }

    private function equipPlayerWithShield(PlayerItem &$playerItem, Item &$item, PlayerBody &$playerBody, string $bodyZone): array {
        if ($bodyZone !== self::BODY_LEFT_HAND) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }

        $properties = self::PROPERTIES[self::BODY_LEFT_HAND];
        $this->disarmTheOtherHand($playerBody, $properties);

        $playerBody->left_hand_item_id = $playerItem->item_id;

        return $this->savePlayerBody($playerBody, $item);
    }

    private function equipPlayerWithTool(PlayerItem &$playerItem, Item &$item, PlayerBody &$playerBody, string $bodyZone): array {
        if ($bodyZone !== self::BODY_RIGHT_HAND) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }

        $properties = self::PROPERTIES[self::BODY_RIGHT_HAND];
        $this->disarmTheOtherHand($playerBody, $properties);

        $playerBody->right_hand_item_id = $playerItem->item_id;
        return $this->savePlayerBody($playerBody, $item);
    }

    private function equipPlayerWithWeapon(PlayerItem &$playerItem, Item &$item, PlayerBody &$playerBody, string $bodyZone): array {
        Yii::debug("*** debug *** equipPlayerWithWeapon - bodyZone={$bodyZone}");
        if ($bodyZone !== self::BODY_RIGHT_HAND && $bodyZone !== self::BODY_LEFT_HAND) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone'
            ];
        }

        // If the weapon is two-handed, simply use both hands.
        if ($playerItem->is_two_handed) {
            $playerBody->right_hand_item_id = $playerItem->item_id;
            $playerBody->left_hand_item_id = $playerItem->item_id;
            return $this->savePlayerBody($playerBody, $item);
        }

        // For a one-handed weapon
        return $this->equipPlayerWithOneHandedWeapon($item, $playerBody, $bodyZone);
    }

    private function equipPlayerWithOneHandedWeapon(Item &$item, PlayerBody &$playerBody, string $bodyZone): array {
        // When this function is called, we already know that
        // it is either the right hand or the left hand.
        $properties = self::PROPERTIES[$bodyZone];
        $this->disarmTheOtherHand($playerBody, $properties);

        return $this->holdWeapon($item, $playerBody, $properties);
    }

    /**
     * Test if the player was previously holding a two-handed weapon.
     * If so, disarm the other hand before picking up the one-handed weapon.
     * This function is made generic and relies on the properties array
     * to manage hand laterality.
     *
     * @param PlayerBody $playerBody
     * @param array $properties
     * @return void
     */
    private function disarmTheOtherHand(PlayerBody &$playerBody, array $properties): void {
        Yii::debug($properties);
        $property = $properties['otherZoneId'];
        Yii::debug("*** debug *** disarmTheOtherHand - property={$property}");
        if ($playerBody->$property) {
            // check if the other hand is not holding a two-handed weapon
            $property = $properties['otherHand'];
            $otherHand = $playerBody->$property;
            if ($otherHand->is_two_handed) {
                // The player has a two-handed weapon in his hand.
                // It is therefore also in the other hand.
                // Disarm it!
                $property = $properties['otherZoneId'];
                $playerBody->$property = null;
            }
        }
    }

    /**
     * Before allowing the player to hold the weapon, check that he is not
     * holding an identical weapon in the other hand.
     * If this is the case, check that they have at least two.
     * If they only have one, disarm the other hand.
     *
     * @param Item $item
     * @param PlayerBody $playerBody
     * @param array $properties
     * @return array
     */
    private function holdWeapon(Item &$item, PlayerBody &$playerBody, array $properties): array {
        Yii::debug($properties);
        $property = $properties['otherZoneId'];
        Yii::debug("*** debug *** holdWeapon - property={$property}");
        if ($playerBody->$property) {
            // check if the other hand is not holding the same one-handed weapon
            $property = $properties['otherHand'];
            $otherHand = $playerBody->$property;
            $itemIdProperty = $properties['otherZoneId'];
            if (
                    !$otherHand->is_two_handed &&
                    $playerBody->$itemIdProperty === $item->id &&
                    $otherHand->quantity < 2
            ) {
                // The player does not have 2 identical weapon.
                // Disarm the other hand.
                $playerBody->$itemIdProperty = null;
            }
        }
        $itemIdProperty = $properties['zoneIdToEquip'];
        $playerBody->$itemIdProperty = $item->id;

        return $this->savePlayerBody($playerBody, $item);
    }

    private function equipPlayer(PlayerItem &$playerItem, string $bodyZone): array {
        $playerBody = $this->findPlayerBody($playerItem->player_id);
        $item = $playerItem->item;
        $itemType = $playerItem->item_type;
        Yii::debug("*** debug *** equipPlayer - itemType={$itemType}, bodyZone={$bodyZone}");
        return match ($itemType) {
            'Armor' => $this->equipPlayerWithArmor($playerItem, $item, $playerBody, $bodyZone),
            'Helmet' => $this->equipPlayerWithHelmet($playerItem, $item, $playerBody, $bodyZone),
            'Shield' => $this->equipPlayerWithShield($playerItem, $item, $playerBody, $bodyZone),
            'Tool' => $this->equipPlayerWithTool($playerItem, $item, $playerBody, $bodyZone),
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

    protected function findPlayer(int|null $playerId = null): Player {
        $player = $playerId ?
                Player::findOne(['id' => $playerId]) :
                Yii::$app->session->get('currentPlayer');

        if ($player) {
            return $player;
        }

        throw new NotFoundHttpException("The player (playerId={$playerId}) you are looking for does not exist.");
    }

    protected function findPlayerBody(int|null $playerId = null) {
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
