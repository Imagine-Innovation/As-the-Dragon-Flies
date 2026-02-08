<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\helpers\FindModelHelper;
use common\helpers\SaveHelper;
use common\models\Item;
use common\models\Player;
use common\models\PlayerBody;
use common\models\PlayerItem;
use frontend\components\Inventory;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * PlayerItemController implements the CRUD actions for PlayerItem model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class PlayerItemController extends Controller
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
                        'actions' => [
                            'index',
                            'see-package',
                            'ajax-equipment',
                            'ajax-toggle',
                            'ajax-equip-player',
                            'ajax-disarm-player',
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
        ]);
    }

    /**
     * Action for displaying the index page.
     * This action initializes a data provider with a query to fetch player packs.
     *
     * @return string The rendered index view.
     */
    public function actionIndex(): string
    {
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
    public function actionSeePackage(): string
    {
        // Find the PlayerItem model based on its primary key value
        $playerId = Yii::$app->session->get('playerId');
        $player = FindModelHelper::findPlayer(['id' => $playerId]);

        // If a player is found, return the player's packs
        $package = PlayerItem::findAll([
            'player_id' => $player->id,
            'is_carrying' => 1,
        ]);

        // Render the pack view with the found model
        return $this->render('pack', [
                    'models' => $package,
        ]);
    }

    /**
     *
     * @param PlayerItem[] $playerItems
     * @return array{
     * playerItems: array<string, non-empty-list<array{itemId: int, name: string, image: string|null, quantity: int, isProficient: int, isTwoHanded: int, buttonId: non-falsy-string}>>,
     * items: list<array{itemId: int, name: string, image: string|null, quantity: int, isProficient: int, isTwoHanded: int, buttonId: non-falsy-string}>
     * }
     */
    private function getEquipmentData(array &$playerItems): array
    {
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
                'buttonId' => "equipButton-{$playerItem->item_id}",
            ];
            $playerItemData[$itemType][] = $itemData;
            $items[] = $itemData;
        }
        return ['playerItems' => $playerItemData, 'items' => $items];
    }

    /**
     *
     * @return array{error: false, msg: '', content: string, contentModal: string, contentAside: string, contentOffcanvas: string, items: list<array{itemId: int, name: string, image: string|null, quantity: int, isProficient: int, isTwoHanded: int, buttonId: non-falsy-string}>, data: array<string, array{itemId: int|null, itemName: string|null, image: string|null}>}|array{error: true, msg: string}
     */
    public function actionAjaxEquipment(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $request = Yii::$app->request;
        $playerId = (int) $request->get('playerId');
        $playerBody = FindModelHelper::findPlayerBody(['player_id' => $playerId]);

        // If a player is found, return the player's packs
        $playerItems = PlayerItem::findAll(['player_id' => $playerId, 'is_carrying' => 1]);
        $playerBodyData = $this->getPlayerBodyData($playerBody);
        $equipmentData = $this->getEquipmentData($playerItems);

        // Render the pack view with the found model
        $content = $this->renderPartial('ajax\package', ['playerItems' => $equipmentData['playerItems']]);
        $contentModal = $this->renderPartial('ajax\svg', [
            'playerBodyData' => $playerBodyData,
            'withId' => true,
            'withOffcanvas' => false,
        ]);
        $contentAside = $this->renderPartial('ajax\svg', [
            'playerBodyData' => $playerBodyData,
            'withId' => false,
            'withOffcanvas' => false,
        ]);
        $contentOffcanvas = $this->renderPartial('ajax\svg', [
            'playerBodyData' => $playerBodyData,
            'withId' => false,
            'withOffcanvas' => true,
        ]);

        return [
            'error' => false,
            'msg' => '',
            'content' => $content,
            'contentModal' => $contentModal,
            'contentAside' => $contentAside,
            'contentOffcanvas' => $contentOffcanvas,
            'items' => $equipmentData['items'],
            'data' => $playerBodyData,
        ];
    }

    /**
     *
     * @param PlayerBody $playerBody
     * @return array<string, array{itemId: int|null, itemName: string|null, image: string|null}>
     */
    private function getPlayerBodyData(PlayerBody &$playerBody): array
    {
        $data = [];

        foreach (PlayerItem::BODY_ZONE as $property => $zone) {
            if ($playerBody->hasProperty($property)) {
                $playerItem = $playerBody->$property;
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

    /**
     *
     * @param array<string, array{itemId: int|null, itemName: string|null, image: string|null}> $playerBodyData
     * @param string $zone
     * @return string
     */
    private function getPlayerBodyRender(array $playerBodyData, string $zone): string
    {
        $snippet = 'ajax\svg';
        return match ($zone) {
            'modal' => $this->renderPartial($snippet, [
                'playerBodyData' => $playerBodyData,
                'withId' => true,
                'withOffcanvas' => false,
            ]),
            'aside' => $this->renderPartial($snippet, [
                'playerBodyData' => $playerBodyData,
                'withId' => false,
                'withOffcanvas' => false,
            ]),
            default => $this->renderPartial($snippet, [
                'playerBodyData' => $playerBodyData,
                'withId' => false,
                'withOffcanvas' => true,
            ])
        };
    }

    /**
     *
     * @param PlayerBody $playerBody
     * @param string $itemName
     * @param bool $equiped
     * @return array<string, mixed>
     */
    private function savePlayerBody(PlayerBody &$playerBody, string $itemName, bool $equiped = true): array
    {
        Yii::debug("*** debug *** savePlayerBody - itemName={$itemName}");
        $successfullySaved = SaveHelper::save($playerBody, false);
        if ($successfullySaved) {
            $playerBodyData = $this->getPlayerBodyData($playerBody);

            return [
                'error' => false,
                'msg' => "{$itemName} is " . ($equiped ? 'equiped' : 'disarmed'),
                'contentModal' => $this->getPlayerBodyRender($playerBodyData, 'modal'),
                'contentAside' => $this->getPlayerBodyRender($playerBodyData, 'aside'),
                'contentOffcanvas' => $this->getPlayerBodyRender($playerBodyData, 'offcanvas'),
                'data' => $playerBodyData,
            ];
        }
        return [
            'error' => true,
            'msg' => 'Player equipment failed: ' . ArrayHelper::getColumn($playerBody->errors, 0, false)[0],
        ];
    }

    /**
     *
     * @param PlayerItem $playerItem
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return array<string, mixed>
     */
    private function equipPlayerWithArmor(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone): array
    {
        if ($bodyZone !== PlayerItem::BODY_CHEST_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone',
            ];
        }
        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        $playerBody->chest_item_id = $playerItem->item_id;

        return $this->savePlayerBody($playerBody, $playerItem->item_name);
    }

    /**
     *
     * @param PlayerItem $playerItem
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return array<string, mixed>
     */
    private function equipPlayerWithHelmet(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone): array
    {
        if ($bodyZone !== PlayerItem::BODY_HEAD_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone',
            ];
        }
        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        $playerBody->head_item_id = $playerItem->item_id;
        return $this->savePlayerBody($playerBody, $playerItem->item_name);
    }

    /**
     *
     * @param PlayerItem $playerItem
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return array<string, mixed>
     */
    private function equipPlayerWithShield(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone): array
    {
        if ($bodyZone !== PlayerItem::BODY_LEFT_HAND_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone',
            ];
        }

        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        $playerBody->left_hand_item_id = $playerItem->item_id;

        return $this->savePlayerBody($playerBody, $playerItem->item_name);
    }

    /**
     *
     * @param PlayerItem $playerItem
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return array<string, mixed>
     */
    private function equipPlayerWithTool(PlayerItem &$playerItem, PlayerBody &$playerBody, string $bodyZone): array
    {
        if ($bodyZone !== PlayerItem::BODY_RIGHT_HAND_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone',
            ];
        }

        // disarm the previous item prior to equip the player with the new one
        $this->disarmPreviousItem($playerBody, $bodyZone);

        $playerBody->right_hand_item_id = $playerItem->item_id;
        return $this->savePlayerBody($playerBody, $playerItem->item_name);
    }

    /**
     *
     * @param PlayerBody $playerBody
     * @param Item $weapon
     * @return void
     */
    private function equipWithQuiver(PlayerBody &$playerBody, Item &$weapon): void
    {
        Yii::debug("*** debug *** equipWithQuiver - weapon={$weapon->name}");
        $haystack = strtolower($weapon->name);

        if (str_contains($haystack, 'bow')) {
            // Special treatment for weapons that use arrows:
            // carry arrows in a quiver on the player's back.
            $bow = $weapon->weapon;
            if ($bow === null) {
                return;
            }
            $amunitionId = $bow->amunition_id;
            $playerBody->back_item_id = $amunitionId;
        }
    }

    /**
     *
     * @param PlayerItem $playerItem
     * @param Item $weapon
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return array<string, mixed>
     */
    private function equipPlayerWithWeapon(
            PlayerItem &$playerItem,
            Item &$weapon,
            PlayerBody &$playerBody,
            string $bodyZone,
    ): array
    {
        $weaponName = $weapon->name;
        Yii::debug("*** debug *** equipPlayerWithWeapon - bodyZone={$bodyZone}, item={$weaponName}");
        if ($bodyZone !== PlayerItem::BODY_RIGHT_HAND_ZONE && $bodyZone !== PlayerItem::BODY_LEFT_HAND_ZONE) {
            return [
                'error' => true,
                'msg' => 'Invalid body zone',
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
     * @param array<string, mixed> $handProperties
     * @return void
     */
    private function disarmTheOtherHand(PlayerBody &$playerBody, array $handProperties): void
    {
        Yii::debug('*** debug *** disarmTheOtherHand - handProperties: ' . print_r($handProperties, true));
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
     * @return array<string, mixed>
     */
    private function holdWeapon(Item &$weapon, PlayerBody &$playerBody, string $bodyZone): array
    {
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
                    && $itemId === $weapon->id // The player holds the same weapon in the other hand AND
                    && $otherHand->quantity < 2 // He does not have mode than one weapon of this sort
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

    /**
     *
     * @param PlayerItem $playerItem
     * @param string $bodyZone
     * @return array<string, mixed>
     */
    private function equipPlayer(PlayerItem &$playerItem, string $bodyZone): array
    {
        $playerBody = FindModelHelper::findPlayerBody(['player_id' => $playerItem->player_id]);
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

    /**
     *
     * @return array<string, mixed>
     */
    public function actionAjaxEquipPlayer(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $itemId = $request->post('itemId');

        $playerItem = FindModelHelper::findPlayerItem(['player_id' => $playerId, 'item_id' => $itemId]);

        $bodyZone = $request->post('bodyZone') ?? '';
        Yii::debug(
                "*** debug *** actionAjaxEquipPlayer - playerId={$playerId}, itemId={$itemId}, bodyZone={$bodyZone}",
        );

        return $this->equipPlayer($playerItem, $bodyZone);
    }

    /**
     *
     * @param PlayerItem $playerItem
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @param string $weaponName
     * @return void
     */
    private function disarmWeapon(
            PlayerItem &$playerItem,
            PlayerBody &$playerBody,
            string $bodyZone,
            string $weaponName,
    ): void
    {
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

    /**
     *
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return void
     */
    private function clearBodyZone(PlayerBody &$playerBody, string $bodyZone): void
    {
        $bodyProperties = PlayerItem::BODY_PROPERTIES[$bodyZone];

        $itemIdField = $bodyProperties['itemIdField'];
        $playerBody->$itemIdField = null;
    }

    /**
     *
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return void
     */
    private function disarmPreviousItem(PlayerBody &$playerBody, string $bodyZone): void
    {
        $bodyProperties = PlayerItem::BODY_PROPERTIES[$bodyZone];
        $property = $bodyProperties['property'];
        Yii::debug("*** debug *** disarmPreviousItem - bodyZone={$bodyZone}, property={$property}");

        if (!$playerBody->hasProperty($property)) {
            return;
        }
        $playerItem = $playerBody->$property;

        $previousItemName = $playerItem->item_name;
        Yii::debug("*** debug *** disarmPreviousItem - bodyZone={$bodyZone}, previousItem={$previousItemName}");
        if ($playerItem->item_type === 'Weapon') {
            $this->disarmWeapon($playerItem, $playerBody, $bodyZone, $previousItemName);
        }

        $this->clearBodyZone($playerBody, $bodyZone);
    }

    /**
     *
     * @param PlayerBody $playerBody
     * @param string $bodyZone
     * @return array<string, mixed>
     */
    private function disarmPlayer(PlayerBody &$playerBody, string $bodyZone): array
    {
        $bodyProperties = PlayerItem::BODY_PROPERTIES[$bodyZone];

        $property = $bodyProperties['property'];
        Yii::debug("*** debug *** disarmPlayer - bodyZone={$bodyZone}, property={$property}");

        if (!$playerBody->hasProperty($property)) {
            return [
                'error' => true,
                'msg' => "Body zone {$bodyZone} has no attached item",
            ];
        }
        $playerItem = $playerBody->$property;
        $previousItem = $playerItem->item;

        $this->disarmPreviousItem($playerBody, $bodyZone);

        return $this->savePlayerBody($playerBody, $previousItem->name, false);
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function actionAjaxDisarmPlayer(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $bodyZone = $request->post('bodyZone') ?? '';

        $playerBody = FindModelHelper::findPlayerBody(['player_id' => $playerId]);

        return $this->disarmPlayer($playerBody, $bodyZone);
    }

    /**
     *
     * @param \yii\web\Request $request
     * @return array{error: bool, msg: string, player?: Player, playerItem?: PlayerItem, status?: int}
     */
    private function prepareAjax(\yii\web\Request $request): array
    {
        if (!$request->isPost || !$request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $playerId = Yii::$app->session->get('playerId');
        $player = FindModelHelper::findPlayer(['id' => $playerId]);
        $itemId = $request->post('item_id');
        $postStatus = $request->post('status', 0);
        $status = is_numeric($postStatus) ? (int) $postStatus : 0;
        $playerItem = FindModelHelper::findPlayerItem(['player_id' => $playerId, 'item_id' => $itemId]);
        return ['error' => false, 'msg' => '', 'player' => $player, 'playerItem' => $playerItem, 'status' => $status];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxToggle(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $checkAjax = $this->prepareAjax($this->request);

        if ($checkAjax['error']) {
            return ['error' => $checkAjax['error'], 'msg' => $checkAjax['msg']];
        }

        if (!($player = $checkAjax['player'] ?? null)) {
            return ['error' => true, 'msg' => 'Missing Player'];
        }
        $inventory = new Inventory();
        $container = $inventory->getContainer($player);
        if (!$container) {
            return ['error' => true, 'msg' => 'You cannot pack anything. Buy a container before you pack.'];
        }

        if (!($playerItem = $checkAjax['playerItem'] ?? null)) {
            return ['error' => true, 'msg' => 'Missing PlayerItem'];
        }

        $status = $checkAjax['status'] ?? 0;

        Yii::debug("*** debug *** - actionAjaxToggle - status={$status}");
        if ($status === 1) {
            return $inventory->addToPack($playerItem, $container);
        } else {
            return $inventory->removeFromPack($playerItem, $container);
        }
    }
}
