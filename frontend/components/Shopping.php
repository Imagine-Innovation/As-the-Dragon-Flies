<?php

namespace frontend\components;

use common\models\Item;
use common\models\Player;
use common\models\PlayerCart;
use common\models\PlayerCoin;
use common\models\PlayerItem;
use Yii;
use yii\helpers\ArrayHelper;

class Shopping
{

    const DEFAULT_COIN = 'gp';

    /** @var array<string> $itemTypes */
    public array $itemTypes = ['Armor', 'Shield', 'Weapon', 'Pack', 'Gear', 'Tool', 'Poison'];

    private const COINS = [
        'cp' => ['rate' => 1, 'prev' => null],
        'sp' => ['rate' => 10, 'prev' => 'cp'],
        'ep' => ['rate' => 50, 'prev' => 'sp'],
        'gp' => ['rate' => 100, 'prev' => 'ep'],
        'pp' => ['rate' => 1000, 'prev' => 'gp'],
    ];

    /** @var array<string, array{rate: int, prev: string|null}> $coins */
    private array $coins = [];      // coin details (exchange rates and relationships).

    /** @var array<string, array{coin: string, quantity: int, copperValue: int, prev: string|null, rate: int}> $purse */
    private array $purse = [];
    private int $purseValue = 0;    // Total value of the player's purse in copper coins.

    public function __construct() {
        $this->coins = self::COINS;
    }

    /**
     * Converts the contents of the purse into copper coin equivalents.
     *
     * This function calculates the total funding in copper coins by summing up
     * the 'cp' (copper) values for each coin type in the purse array.
     *
     * @return int Total funding in copper coins.
     */
    private function getPurseValue(): int {
        return array_sum(array_column($this->purse, 'copperValue'));
    }

    /**
     * Load the PlayerCoin objects into an array with conversion and linking between array elements.
     *
     * This function initializes the purse array based on the given PlayerCoin objects,
     * converting quantities to copper values and establishing links between array elements.
     *
     * @param PlayerCoin[] $playerCoins Content of the player's purse.
     * @return array<string, mixed> Associative array representing the player's purse with coin types as keys.
     */
    private function initPurse(array $playerCoins): array {
        if ($this->purse) {
            // if purse is already initialized, keep it
            return $this->purse;
        }
        $this->purse = [];

        foreach ($playerCoins as $playerCoin) {
            $coin = $playerCoin->coin;

            $thisCoin = $this->coins[$coin] ?? null;
            if ($thisCoin !== null) {
                $purseContent = [
                    'coin' => $coin,
                    'quantity' => $playerCoin->quantity,
                    'copperValue' => $playerCoin->quantity * $thisCoin['rate'],
                    'prev' => $thisCoin['prev'],
                    'rate' => $thisCoin['rate'],
                ];
                Yii::debug("*** debug *** initPurse");
                Yii::debug($purseContent);
                $this->purse[$coin] = $purseContent;
            }
        }
        // Sort the player's purse by descending change rate value
        $value = array_column($this->purse, 'rate');
        array_multisort($value, SORT_DESC, $this->purse);

        $this->purseValue = $this->getPurseValue();
        return $this->purse;
    }

    /**
     * Converts the Purse array into PlayerCoin objects and saves them.
     *
     * This function iterates through the provided array of PlayerCoin objects representing
     * the content of the player's purse, updates the quantity from the current Purse array,
     * and attempts to save each PlayerCoin object. Returns false if saving fails for any coin type.
     *
     * @param PlayerCoin[] $playerCoins content of the player's purse.
     *
     * @return bool True if saving is successful for all PlayerCoin objects, false otherwise.
     */
    private function setPlayerCoins(array $playerCoins): bool {
        $success = true;
        foreach ($playerCoins as $playerCoin) {
            $coin = $playerCoin->coin;
            $wallet = $this->purse[$coin] ?? null;

            if ($wallet) {
                $playerCoin->quantity = $wallet['quantity'];
                $success = $success && $playerCoin->save();
            }
        }
        return $success;
    }

    /**
     * Update the wallet in the purse after adding a specific quantity of coins.
     *
     * This function increments the quantity of the specified coin in the purse
     * by the given amount and updates its corresponding copper value.
     *
     * @param int $amount The amount in coins to add to the wallet.
     * @param string|null $coin The type of coin (e.g., 'gold', 'silver', 'copper').
     * @return void
     */
    private function updateWallet(int $amount, ?string $coin = self::DEFAULT_COIN): void {
        $wallet = $this->purse[$coin];
        $wallet['quantity'] += $amount;
        $wallet['copperValue'] = $this->copperValue($wallet['quantity'], $coin);
        $this->purse[$coin] = $wallet;
    }

    /**
     * Change to a higher value coin in the purse.
     *
     * This function identifies the coin with the lowest rate in the purse
     * and exchanges one unit of that coin for the target coin with a higher rate.
     *
     * @param string|null $targetCoin The target coin type to exchange to.
     * @return bool True if the exchange is successful, false otherwise.
     */
    private function changeHigherValueCoin(?string $targetCoin = self::DEFAULT_COIN): bool {
        if (!isset($this->coins[$targetCoin])) {
            // Coin is unknown type
            return false;
        }

        $targetRate = $this->coins[$targetCoin]['rate'];
        $sourceRate = 9999;
        $sourceCoin = null;

        foreach ($this->purse as $wallet) {
            if ($wallet['quantity'] > 0 && $wallet['rate'] < $sourceRate) {
                $sourceRate = $wallet['rate'];
                $sourceCoin = $wallet['coin'];
            }
        }

        if ($sourceCoin !== null) {
            $this->updateWallet(-1, $sourceCoin);
            $this->updateWallet($sourceRate / $targetRate, $targetCoin);
            return true;
        }
        return false;
    }

    /**
     * Remove coins from the purse based on a given coin value and quantity.
     *
     * This function recursively traverses the purse, updating quantities and values
     * to deduct the equivalent value in copper coins based on the provided coin value.
     *
     * @param int $itemCopperValue The remaining value to be deducted in copper coins.
     * @param string|null $coin The current coin type being processed.
     * @return int The updated remaining value to be deducted.
     */
    private function removeCoinsFromPurse(int &$itemCopperValue, ?string $coin): int {
        if ($itemCopperValue <= 0 || !$coin) {
            return $itemCopperValue;
        }

        $wallet = $this->purse[$coin];

        if ($wallet['quantity'] > 0) {
            $coinValue = floor($itemCopperValue / $wallet['rate']);
            $coinsToRemove = (int) min($wallet['quantity'], $coinValue);

            if ($coinsToRemove > 0) {
                $copperValue = $this->copperValue($coinsToRemove, $coin);

                $wallet['quantity'] -= $coinsToRemove;
                $wallet['copperValue'] -= $copperValue;

                $itemCopperValue -= $copperValue;

                $this->purse[$coin] = $wallet;
            }
        }

        // Recursively call the function with the previous coin type in the wallet.
        return $this->removeCoinsFromPurse($itemCopperValue, $wallet['prev']);
    }

    /**
     * Attempts to spend coins from a player's purse for a specified price.
     *
     * @param \common\models\PlayerCoin[] $playerCoins The player's current coin holdings represented as an associative array.
     * @param int $amount The amount of coins needed to purchase the item.
     * @param string|null $coin The type of coin (e.g., 'gold', 'silver', 'copper').
     *
     * @return bool True if the spending is successful, false otherwise.
     */
    public function spend(array $playerCoins, int $amount, ?string $coin = self::DEFAULT_COIN): bool {
        // Check if the player has sufficient funding for the specified cost and coin type.
        $itemCopperValue = $this->getFunding($playerCoins, $amount, $coin);
        if ($itemCopperValue <= 0) {
            return false;
        }

        // If there's enough funding, attempt to remove coins from the player's purse for the item cost.
        $removedCopperValue = $this->removeCoinsFromPurse($itemCopperValue, 'pp');
        if ($removedCopperValue > 0) {
            $this->changeHigherValueCoin($coin);
            $this->removeCoinsFromPurse($itemCopperValue, $coin);
        }
        return $this->setPlayerCoins($playerCoins);
    }

    /**
     * Restores the player's coins after a transaction.
     *
     * @param \common\models\PlayerCoin[] $playerCoins The player's current coins.
     * @param int|null $amount The cost to be restored.
     * @param string|null $coin The type of coin (e.g., 'gold', 'silver', 'copper').
     * @return bool Whether the restoration was successful.
     */
    public function restoreFunding(array $playerCoins, int|null $amount = 0, ?string $coin = self::DEFAULT_COIN): bool {
        $this->initPurse($playerCoins);

        foreach ($playerCoins as $playerCoin) {
            if ($playerCoin->coin === $coin) {
                $playerCoin->quantity += (int) ($amount ?? 0);
                return $playerCoin->save();
            }
        }
        return false;
    }

    /**
     * Calculates the maximum funding a player can contribute towards an item purchase.
     *
     * @param \common\models\PlayerCoin[] $playerCoins The player's current coin holdings represented as an associative array.
     * @param int $amount The amount of coins needed to purchase the item.
     * @param string|null $coin The type of coin (e.g., 'gold', 'silver', 'copper').
     *
     * @return int The maximum funding the player can contribute towards the item purchase in copper coins.
     */
    public function getFunding(array $playerCoins, int $amount, ?string $coin = self::DEFAULT_COIN): int {
        if (!isset($this->coins[$coin]) || $amount <= 0) {
            return 0;
        }

        $this->initPurse($playerCoins);
        $itemCopperValue = $this->copperValue($amount, $coin);

        return ($this->purseValue < $itemCopperValue) ? 0 : $itemCopperValue;
    }

    /**
     * Return the value in the lower coin value (copper).
     *
     * This function converts the given cost from the specified coin unit to copper coins.
     *
     * @param int $amount The amount in coins defining the price of the item.
     * @param string|null $coin The type of coin (e.g., 'gold', 'silver', 'copper').
     * @return int Value converted into copper coins.
     */
    public function copperValue(int $amount, ?string $coin = self::DEFAULT_COIN): int {
        return (!isset($this->coins[$coin]) || $amount <= 0) ?
                0 :
                (int) ($amount * $this->coins[$coin]['rate']);
    }

    /**
     * Get a string representation of the player's purse value.
     *
     * This method iterates through the purse array, generating a string
     * for each non-zero quantity wallet in the format "{quantity}{coin}",
     * and then joins these strings with a plus sign.
     *
     * @param \common\models\PlayerCoin[] $playerCoins The player's current coin holdings represented as an associative array.
     * @return string The string representation of the player's purse.
     */
    public function getPurseValueString(array $playerCoins): string {
        $string = [];
        $this->initPurse($playerCoins);
        foreach ($this->purse as $wallet) {
            if ($wallet['quantity'] > 0) {
                $string[] = $wallet['quantity'] . $wallet['coin'];
            }
        }
        return implode(" + ", $string);
    }

    /**
     *
     * @return array<string, array{value: int, coin: string, rate: int}>
     */
    private function initCoinage(): array {
        $coinage = [];
        foreach (array_keys($this->coins) as $coin) {
            $coinage[$coin] = [
                'value' => 0,
                'coin' => $coin,
                'rate' => (int) $this->coins[$coin]['rate']
            ];
        }
        return $coinage;
    }

    /**
     * Generate a cart value string representing the total value of items in the player's cart.
     *
     * @param PlayerCart[] $playerCarts The player's cart containing items.
     * @return string The cart value string.
     */
    public function getCartValueString(array $playerCarts): string {
        if (empty($playerCarts)) {
            return "";
        }

        $coinage = $this->initCoinage();

        foreach ($playerCarts as $cart) {
            $item = $cart->item;
            $coin = $item->coin ?? 'gp';
            $coinage[$coin]['value'] += ($item->cost ?? 0) * $cart->quantity;
        }

        /** @var list<array{value: int, coin: string, rate: int}> $coinage */
        usort($coinage, function ($a, $b) {
            return $b['rate'] <=> $a['rate'];
        });
        $cartValueString = [];
        foreach ($coinage as $c) {
            /** @var array{value: int, coin: string, rate: int} $c */
            if ($c['value'] > 0) {
                $cartValueString[] = $c['value'] . " " . $c['coin'];
            }
        }
        return implode(" + ", $cartValueString);
    }

    /**
     * Generates a message indicating why a purchase is not possible based on the player's coins and item cost.
     *
     * @param \common\models\PlayerCoin[] $playerCoins $playerCoins The amount of coins the player has.
     * @param Item &$item The item to be purchased.
     *
     * @return string The purchase status message.
     */
    public function purchaseNotPossibleMessage(array $playerCoins, Item &$item): string {
        $this->initPurse($playerCoins);

        if ($this->getPurseValue() > 0) {
            $remaining = $this->getPurseValueString($playerCoins);
            $msg = "The item costs {$item->price}, but you only have {$remaining} left, so you can't buy it.";
            return $msg;
        } else {
            return "Come on, you don't have any money left, how are you going to buy this article?";
        }
    }

    /**
     * Removes an item from the player's cart.
     *
     * @param Player &$player The Player model representing the player.
     * @param Item &$item The Item model representing the item to be removed from the cart.
     * @param int|null $quantity The quantity of the item to be removed.
     * @return array{error: bool, msg: string, content?: string} An associative array containing the result of the operation.
     */
    public function removeFromCart(Player &$player, Item &$item, ?int $quantity = null): array {
        $cartItem = PlayerCart::findOne(['player_id' => $player->id, 'item_id' => $item->id]);
        if ($cartItem === null) {
            return ['error' => true, 'msg' => 'Nothing to remove'];
        }

        $qty = $quantity ?? 1;
        $refund = $item->cost * $qty;
        $cartItem->quantity -= $qty;

        if ($cartItem->quantity <= 0) {
            $ok = $cartItem->delete();
            $verb = "deleted";
        } else {
            $ok = $cartItem->save();
            $verb = "removed";
        }

        if ($ok && $this->restoreFunding($player->playerCoins, $refund, $item->coin)) {
            return ['error' => false, 'msg' => "{$qty} {$item->name}{($qty>1 ? 's':'')} $verb from your cart"];
        }
        return ['error' => true, 'msg' => 'Save failed'];
    }

    /**
     *
     * @param Player $player
     * @param Item $item
     * @return array{error: bool, msg: string, content?: string}
     */
    public function deleteFromCart(Player &$player, Item &$item): array {
        $cartItem = PlayerCart::findOne(['player_id' => $player->id, 'item_id' => $item->id]);
        if ($cartItem === null) {
            return ['error' => true, 'msg' => 'Nothing to delete'];
        }

        $refund = $item->cost * $cartItem->quantity;
        if (!$this->restoreFunding($player->playerCoins, $refund, $item->coin)) {
            return ['error' => true, 'msg' => 'Refund failed'];
        }
        PlayerCart::deleteAll(['player_id' => $player->id, 'item_id' => $item->id]);
        return ['error' => false, 'msg' => "Item {$item->name} is deleted from your cart"];
    }

    /**
     * Adds an item to the player's cart.
     *
     * @param Player &$player The Player model representing the player.
     * @param Item &$item The Item model representing the item to be added to the cart.
     * @param int|null $quantity The quantity to be added to the cart.
     * @return array{error: bool, msg: string, content?: string}  An associative array containing the result of the operation.
     */
    public function addToCart(Player &$player, Item &$item, ?int $quantity = null): array {
        $qty = $quantity ?? 1;
        $cartItem = PlayerCart::findOne(['player_id' => $player->id, 'item_id' => $item->id]);
        if ($cartItem) {
            $cartItem->quantity += $qty;
        } else {
            $cartItem = new PlayerCart([
                'player_id' => $player->id,
                'item_id' => $item->id,
                'quantity' => $qty
            ]);
        }

        if ($cartItem->save()) {
            $spent = $item->cost * $quantity;
            $this->spend($player->playerCoins, $spent, $item->coin);
            return ['error' => false, 'msg' => "Item {$item->name} is added to your cart"];
        }
        return ['error' => true, 'msg' => 'Save failed. Unable to save the cart'];
    }

    /**
     * Validates the purchase of a single item in the player's cart.
     *
     * This method is responsible for validating the purchase of a player cart item.
     * It checks if a corresponding PlayerItem exists for the cart item.
     * If it exists, it updates the quantity of the PlayerItem.
     * If not, it creates a new PlayerItem.
     * If the saving process is successful, it removes the item from the
     * player's cart. Finally, it returns a success response if the validation
     * is successful, otherwise, it returns an error response.
     *
     * @param PlayerCart $playerCart The player cart item to validate.
     * @return array{error: bool, msg: string, content?: string} The JSON response indicating the success or failure of the validation.
     */
    private function validateSingleItemPurchase(PlayerCart &$playerCart): array {
        $item = $playerCart->item;
        $playerItem = PlayerItem::findOne([
            'player_id' => $playerCart->player_id,
            'item_id' => $playerCart->item_id
        ]);
        if ($playerItem) {
            $playerItem->quantity += $playerCart->quantity * ($item->quantity ?? 1);
        } else {
            $playerItem = $this->addToInventory($playerCart);
        }
        if ($playerItem->save()) {
            return ['error' => false, 'msg' => 'Cart is validated'];
        }
        throw new \Exception(implode("<br />", ArrayHelper::getColumn($playerItem->errors, 0, false)));
    }

    private function addToInventory(PlayerCart &$playerCart): PlayerItem {
        $classId = $playerCart->player->class_id;
        $isProficient = PlayerComponent::isProficient($classId, $playerCart->item_id) ? 1 : 0;

        $item = $playerCart->item;

        // By default, weapons and armors are carried.
        $itemType = $item->itemType;
        $isCarrying = ($itemType->name === 'Weapon' || $itemType->name === 'Armor');

        $playerItemData = [
            'player_id' => $playerCart->player_id,
            'item_id' => $playerCart->item_id,
            'item_name' => $item->name,
            'item_type' => $itemType->name,
            'image' => $item->image,
            'quantity' => $playerCart->quantity * $item->quantity,
            'is_carrying' => $isCarrying ? 1 : 0,
            'is_proficient' => $isProficient,
            'is_two_handed' => ($item->weapon->is_two_handed ? 1 : 0),
        ];
        Yii::debug($playerItemData);
        $playerItem = new PlayerItem($playerItemData);

        return $playerItem;
    }

    /**
     * Validates the purchase of a pack in the player's cart.
     *
     * This function processes each item in the pack by creating a temporary
     * cart item (`innerPack`) and validates it as a single item.
     * If any item in the pack fails validation, the function returns an error.
     * If all items are successfully validated, the original cart item is removed,
     * and a success message is returned.
     *
     * @param PlayerCart $playerCart The player's cart item to be validated.
     * @return array{error: bool, msg: string, content?: string} An associative array containing 'error' and 'msg' keys to indicate the validation result.
     */
    private function validatePackPurchase(PlayerCart &$playerCart): array {
        $item = $playerCart->item;
        $packs = $item->packs;
        $packContent = new PlayerCart();

        foreach ($packs as $pack) {
            $packContent->player_id = $playerCart->player_id;
            $packContent->item_id = $pack->item_id;
            $packContent->quantity = $playerCart->quantity * $pack->quantity;

            $success = $this->validateSingleItemPurchase($packContent);
            if ($success['error']) {
                return $success;
            }
        }
        return ['error' => false, 'msg' => 'Cart is validated'];
    }

    /**
     * Validates the purchase in the player's cart.
     *
     * This function checks the type of item in the player's cart.
     * If the item is of type 'Pack', it validates the purchase as a pack.
     * Otherwise, it validates the purchase as a single item.
     *
     * @param PlayerCart $playerCart The player's cart item to be validated.
     * @return array{error: bool, msg: string, content?: string} An associative array containing 'error' and 'msg' keys to indicate the validation result.
     */
    public function validatePurchase(PlayerCart $playerCart): array {
        $item = $playerCart->item;
        $success = ($item->itemType->name === 'Pack') ?
                $this->validatePackPurchase($playerCart) :
                $this->validateSingleItemPurchase($playerCart);

        if ($success['error']) {
            return $success;
        }
        $playerCart->delete();
        return ['error' => false, 'msg' => 'Cart is validated'];
    }

    /**
     * Loads data for the shop page.
     *
     * This method prepares the data required for rendering the shop page.
     * It organizes the models into categories based on their item types
     * and sorts the items within each category by their copper values
     * in ascending order.
     *
     * @param Item[] $items The models to load data for.
     * @return array<string, list<array<string, int|string|null>>> The organized data for rendering the shop page.
     */
    public function loadShopData(array $items): array {
        $data = [];

        foreach ($items as $item) {
            $itemType = $item->itemType->name;
            $data[$itemType][] = [
                'id' => $item->id,
                'type' => $itemType,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'value' => $item->copperValue,
                'quantity' => $item->quantity,
                'image' => $item->image,
            ];
        }

        foreach ($this->itemTypes as $tab) {
            if (isset($data[$tab])) {
                $items = $data[$tab];
                $value = array_column($items, 'value');
                array_multisort($value, SORT_ASC, $items);
                $data[$tab] = $items;
            }
        }
        return $data;
    }
}
