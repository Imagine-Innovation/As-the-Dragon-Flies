<?php

namespace frontend\components;

use common\models\Item;
use common\models\Player;
use common\models\PlayerCart;
use common\models\PlayerItem;
use Yii;

class Shopping
{

    public $itemTypes = ['Armor', 'Weapon', 'Pack', 'Gear', 'Tool', 'Poison'];

    // Constants defining the exchange rates and relationships between coin types.
    private const COINS = [
        'cp' => ['rate' => 1, 'prev' => null],
        'sp' => ['rate' => 10, 'prev' => 'cp'],
        'ep' => ['rate' => 50, 'prev' => 'sp'],
        'gp' => ['rate' => 100, 'prev' => 'ep'],
        'pp' => ['rate' => 1000, 'prev' => 'gp'],
    ];

    private $coins = [];        // Array to store coin details (exchange rates and relationships).
    private $purse = [];        // Array to represent the player's purse.
    private $purseValue = 0;    // Total value of the player's purse in copper coins.

    /**
     * Constructor for the Shopping class.
     * Initializes default exchange rates.
     */
    public function __construct() {
        // Initialize default exchange rates
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
    private function getPurseValue() {
        // Use array_column to extract the 'cp' values for each coin type and sum them up.
        return array_sum(array_column($this->purse, 'copperValue'));
    }

    /**
     * Load the PlayerCoin objects into an array with conversion and linking between array elements.
     *
     * This function initializes the purse array based on the given PlayerCoin objects,
     * converting quantities to copper values and establishing links between array elements.
     *
     * @param PlayerCoins[] $playerCoins Content of the player's purse.
     * @return array Associative array representing the player's purse with coin types as keys.
     */
    private function initPurse(array $playerCoins): array {
        // Initialize the purse array.
        if ($this->purse) {
            return $this->purse;
        }
        $this->purse = [];

        // Iterate through each PlayerCoin object in the given array.
        foreach ($playerCoins as $playerCoin) {
            // Extract the coin type from the PlayerCoin object.
            $coin = $playerCoin->coin;

            // Retrieve the coin details for the current coin type.
            $thisCoin = $this->coins[$coin];
            // Check if the coin details exist.
            if ($thisCoin) {
                // Create an entry in the purse array with quantity,
                // copper value, and link to the previous coin type.
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

        // Calculate the total value of the player's purse in copper coins.
        $this->purseValue = $this->getPurseValue();

        // Return the initialized purse array.
        return $this->purse;
    }

    /**
     * Converts the Purse array into PlayerCoin objects and saves them.
     *
     * This function iterates through the provided array of PlayerCoin objects representing
     * the content of the player's purse, updates the quantity from the current Purse array,
     * and attempts to save each PlayerCoin object. Returns false if saving fails for any coin type.
     *
     * @param PlayerCoin[] $playerCoins An array of PlayerCoin objects representing the content of the player's purse.
     *
     * @return bool True if saving is successful for all PlayerCoin objects, false otherwise.
     */
    private function setPlayerCoins(array $playerCoins): bool {
        // Initialize the return variable to true.
        $success = true;

        // Iterate through each PlayerCoin object in the provided array.
        foreach ($playerCoins as $playerCoin) {
            // Extract the coin type from the PlayerCoin object.
            $coin = $playerCoin->coin;

            // Retrieve the wallet details for the current coin type.
            $wallet = $this->purse[$coin] ?? null;

            // If the wallet details exist, update the PlayerCoin object's
            // quantity and attempt to save it.
            if ($wallet) {
                $playerCoin->quantity = $wallet['quantity'];

                // Update the return variable based on the success
                // of saving the PlayerCoin object.
                $success = $success && $playerCoin->save();
            }
        }

        // Return the final result of the saving process.
        return $success;
    }

    /**
     * Update the wallet in the purse after adding a specific quantity of coins.
     *
     * This function increments the quantity of the specified coin in the purse
     * by the given amount and updates its corresponding copper value.
     *
     * @param string $coin The type of coin to update in the purse.
     * @param int $added The quantity of coins to add to the wallet.
     * @return void
     */
    private function updateWallet(string $coin, int $added): void {
        // Retrieve the current wallet for the specified coin.
        $wallet = $this->purse[$coin];

        // Increment the quantity by the specified amount.
        $wallet['quantity'] += $added;

        // Update the copper value based on the new quantity.
        $wallet['copperValue'] = $this->copperValue($wallet['quantity'], $coin);

        // Update the wallet in the purse.
        $this->purse[$coin] = $wallet;
    }

    /**
     * Change to a higher value coin in the purse.
     *
     * This function identifies the coin with the lowest rate in the purse
     * and exchanges one unit of that coin for the target coin with a higher rate.
     *
     * @param string $targetCoin The target coin type to exchange to.
     * @return bool True if the exchange is successful, false otherwise.
     */
    private function changeHigherValueCoin(string $targetCoin): bool {
        // Check if the target coin exists in the coins array.
        if (!isset($this->coins[$targetCoin])) {
            return false;
        }

        // Initialize target and source rates with default values.
        $targetRate = $this->coins[$targetCoin]['rate'];
        $sourceRate = 9999;
        $sourceCoin = '';

        // Iterate through each wallet in the purse.
        foreach ($this->purse as $wallet) {
            // Check if the wallet has coins and if the rate is lower than
            // the current source rate.
            if ($wallet['quantity'] > 0 && $wallet['rate'] < $sourceRate) {
                // Update source rate and coin.
                $sourceRate = $wallet['rate'];
                $sourceCoin = $wallet['coin'];
            }
        }

        // If a source coin is found, perform the exchange.
        if ($sourceCoin) {
            // Update the purse for the source coin with a decrease of one unit.
            $this->updateWallet($sourceCoin, -1);

            // Update the purse for the target coin with an increase
            // based on the exchange rate.
            $this->updateWallet($targetCoin, $sourceRate / $targetRate);

            return true;
        }

        // Return false if no source coin is found.
        return false;
    }

    /**
     * Remove coins from the purse based on a given coin value and quantity.
     *
     * This function recursively traverses the purse, updating quantities and values
     * to deduct the equivalent value in copper coins based on the provided coin value.
     *
     * @param float $itemCopperValue The remaining value to be deducted in copper coins.
     * @param string $coin The current coin type being processed.
     * @return float The updated remaining value to be deducted.
     */
    private function removeCoinsFromPurse(&$itemCopperValue, $coin) {
        // If the remaining value to be deducted is non-positive or the coin is not provided,
        // there's no need to proceed, so return early.
        if ($itemCopperValue <= 0 || !$coin) {
            return $itemCopperValue;
        }

        // Retrieve the wallet corresponding to the current coin.
        $wallet = $this->purse[$coin];

        // If there are coins of the current type in the wallet, proceed with removal.
        if ($wallet['quantity'] > 0) {
            // Calculate the integer number of coins to be removed based on the remaining value.
            $coinValue = floor($itemCopperValue / $wallet['rate']);
            $coinsToRemove = min($wallet['quantity'], $coinValue);

            // If there are coins to be removed, update quantities and values.
            if ($coinsToRemove > 0) {
                $copperValue = $this->copperValue($coinsToRemove, $coin);

                $wallet['quantity'] -= $coinsToRemove;
                $wallet['copperValue'] -= $copperValue;

                // Update the remaining value to be deducted.
                $itemCopperValue -= $copperValue;

                // Update the wallet in the purse.
                $this->purse[$coin] = $wallet;
            }
        }

        // Recursively call the function with the previous coin type in the wallet.
        return $this->removeCoinsFromPurse($itemCopperValue, $wallet['prev']);
    }

    /**
     * Attempts to spend coins from a player's purse for a specified cost and coin type.
     *
     * @param array $playerCoins The player's current coin holdings represented as an associative array.
     * @param float $cost The cost of the item to be purchased.
     * @param string $coin The type of coin for the item cost (e.g., 'gold', 'silver', 'copper').
     *
     * @return bool True if the spending is successful, false otherwise.
     */
    public function spend(array $playerCoins, float $cost, string $coin): bool {
        // Check if the player has sufficient funding for the specified cost and coin type.
        $itemCopperValue = $this->getFunding($playerCoins, $cost, $coin);

        // If there's enough funding, attempt to remove coins from the player's purse for the item cost.
        if ($itemCopperValue > 0) {
            // Attempt to remove coins from the player's purse for the item cost.
            $removedCopperValue = $this->removeCoinsFromPurse($itemCopperValue, 'pp');

            // If removal is successful, perform additional actions.
            if ($removedCopperValue > 0) {
                // Change to a higher value coin based on the specified coin type.
                $this->changeHigherValueCoin($coin);

                // Remove coins from the player's purse for the item cost in the specified coin type.
                $this->removeCoinsFromPurse($itemCopperValue, $coin);
            }

            // Set the player's updated coin holdings after the spending.
            return $this->setPlayerCoins($playerCoins);
        }

        // Return false if the spending is not successful.
        return false;
    }

    /**
     * Restores the player's coins after a transaction.
     *
     * @param array $playerCoins The player's current coins.
     * @param int $cost The cost to be restored.
     * @param string $coin The type of coin to be restored.
     * @return bool Whether the restoration was successful.
     */
    public function restoreFunding(array $playerCoins, int $cost, string $coin): bool {
        $this->initPurse($playerCoins);

        // Iterate over the player's coins
        foreach ($playerCoins as $playerCoin) {
            // Check if the current coin matches the specified coin type
            if ($playerCoin->coin === $coin) {
                // Increment the quantity by the cost
                $playerCoin->quantity += $cost;
                // Save the updated coin quantity
                return $playerCoin->save();
            }
        }
        // Coin type not found, restoration failed
        return false;
    }

    /**
     * Calculates the maximum funding a player can contribute towards an item purchase.
     *
     * @param array $playerCoins The player's current coin holdings represented as an associative array.
     * @param float $cost The cost of the item to be purchased.
     * @param string $coin The type of coin for the item cost (e.g., 'gold', 'silver', 'copper').
     *
     * @return float The maximum funding the player can contribute towards the item purchase in copper coins.
     */
    public function getFunding(array $playerCoins, float $cost, string $coin): float {
        // Check if the specified coin type exists in the coins array,
        // and if the item cost is positive.
        if (!isset($this->coins[$coin]) || $cost <= 0) {
            // If not, return zero as the player cannot contribute funds.
            return 0;
        }

        // Initialize the player's purse based on their current coin holdings.
        $this->initPurse($playerCoins);

        // Calculate the value of the item cost in copper coins.
        $itemCopperValue = $this->copperValue($cost, $coin);

        // Determine the maximum funding the player can contribute based
        // on their purse and item cost.
        // If the player's purse value is less than the item cost, return zero;
        // otherwise, return the item cost in copper coins.
        return ($this->purseValue < $itemCopperValue) ? 0 : $itemCopperValue;
    }

    /**
     * Return the value in the lower coin value (copper).
     *
     * This function converts the given cost from the specified coin unit to copper coins.
     *
     * @param float $cost Actual cost in the specified $coin unit.
     * @param string $coin Current coin unit.
     * @return int Value converted into copper coins.
     */
    public function copperValue(float $cost, string $coin): int {
        // Check if the coin type is valid or the cost is non-positive.
        if (!isset($this->coins[$coin]) || $cost <= 0) {
            // Handle invalid coin types or negative cost.
            return 0;
        }

        // Calculate the value in copper coins by multiplying the cost with the coin's conversion rate.
        return (int) ($cost * $this->coins[$coin]['rate']);
    }

    /**
     * Get a string representation of the player's purse value.
     *
     * This method iterates through the purse array, generating a string
     * for each non-zero quantity wallet in the format "{quantity}{coin}",
     * and then joins these strings with a plus sign.
     *
     * @param array $playerCoins The player's current coin holdings represented as an associative array.
     * @return string The string representation of the player's purse.
     */
    public function getPurseValueString(array $playerCoins): string {
        // Initialize an array to store formatted strings for each wallet.
        $string = [];
        $this->initPurse($playerCoins);
        // Iterate through each wallet in the purse.
        foreach ($this->purse as $wallet) {
            // Check if the wallet has a non-zero quantity.
            if ($wallet['quantity'] > 0) {
                // Format the string as "{quantity} {coin}" and add it to the array.
                $string[] = $wallet['quantity'] . $wallet['coin'];
            }
        }

        // Join the formatted strings with plus sign (+) and return the result.
        return implode(" + ", $string);
    }

    private function initCoinage(): array {
        $coinage = [];
        foreach (array_keys($this->coins) as $coin) {
            $coinage[$coin] = [
                'value' => 0,
                'coin' => $coin,
                'rate' => $this->coins[$coin]['rate']
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
        // Return empty string if player's cart is empty
        if (empty($playerCarts)) {
            return "";
        }

        $coinage = $this->initCoinage();

        // Calculate the total cost for each coin type based on items in the cart
        foreach ($playerCarts as $cart) {
            $item = $cart->item;
            $coin = $item->coin;
            $coinage[$coin]['value'] += $item->cost * $cart->quantity;
        }

        // Sort the price array by descending change rate value
        usort($coinage, function ($a, $b) {
            return $b['rate'] <=> $a['rate'];
        });

        // Generate the cart value string
        $cartValueString = [];
        foreach ($coinage as $c) {
            if ($c['value'] > 0) {
                $cartValueString[] = $c['value'] . " " . $c['coin'];
            }
        }

        return implode(" + ", $cartValueString);
    }

    /**
     * Generates a message indicating why a purchase is not possible based on the player's coins and item cost.
     *
     * @param PlayerCoin[] $playerCoins The amount of coins the player has.
     * @param Item &$item The item to be purchased.
     *
     * @return string The purchase status message.
     */
    public function purchaseNotPossibleMessage(array $playerCoins, Item &$item): string {
        // Initialize the player's purse with the current coins.
        $this->initPurse($playerCoins);

        // Check if the purse has enough value to cover the item cost.
        if ($this->getPurseValue() > 0) {
            // Construct a message indicating insufficient funds for the purchase.
            $remaining = $this->getPurseValueString($playerCoins);
            $msg = "The item costs $item->price, but you only have $remaining left, so you can't buy it.";
            return $msg;
        } else {
            // Return a message indicating the player doesn't have any money.
            return "Come on, you don't have any money left, how are you going to buy this article?";
        }
    }

    /**
     * Removes an item from the player's cart.
     *
     * @param Player &$player The Player model representing the player.
     * @param Item &$item The Item model representing the item to be removed from the cart.
     * @param int|null $quantity The quantity of the item to be removed.
     * @return array An associative array containing the result of the operation.
     */
    public function removeFromCart(Player &$player, Item &$item, int|null $quantity = null): array {
        // Find the cart entry for the specified item
        $cartItem = PlayerCart::findOne(['player_id' => $player->id, 'item_id' => $item->id]);

        if (!$cartItem) {
            // If the item does not exist in the cart, return an error message
            return ['error' => true, 'msg' => 'Nothing to remove'];
        }

        $qty = $quantity ?? 1;
        $refund = $item->cost * $qty;
        $cartItem->quantity -= $qty;

        // If the quantity becomes zero or less, delete the cart entry; otherwise, save the changes
        if ($cartItem->quantity <= 0) {
            $ok = $cartItem->delete();
            $verb = "deleted";
        } else {
            $ok = $cartItem->save();
            $verb = "removed";
        }

        // Restore the spent cost of the item to the player's coins
        if ($ok && $this->restoreFunding($player->playerCoins, $refund, $item->coin)) {
            // Return a success message
            return ['error' => false, 'msg' => "{$qty} {$item->name}{($qty>1 ? 's':'')} $verb from your cart"];
        }

        // If the save operation fails or funding restoration fails, return an error message
        return ['error' => true, 'msg' => 'Save failed'];
    }

    public function deleteFromCart(Player &$player, Item &$item): array {
        // Find the cart entry for the specified item
        $cartItem = PlayerCart::findOne(['player_id' => $player->id, 'item_id' => $item->id]);

        if (!$cartItem) {
            // If the item does not exist in the cart, return an error message
            return ['error' => true, 'msg' => 'Nothing to delete'];
        }

        // Calculate the cost of the items to be deleted
        $refund = $item->cost * $cartItem->quantity;

        // Restore the spent cost of the item to the player's coins
        if (!$this->restoreFunding($player->playerCoins, $refund, $item->coin)) {
            // If funding restoration fails, return an error message
            return ['error' => true, 'msg' => 'Refund failed'];
        }
        PlayerCart::deleteAll(['player_id' => $player->id, 'item_id' => $item->id]);
        // Return a success message
        return ['error' => false, 'msg' => "Item {$item->name} is deleted from your cart"];
    }

    /**
     * Adds an item to the player's cart.
     *
     * @param Player &$player The Player model representing the player.
     * @param Item &$item The Item model representing the item to be added to the cart.
     * @param int|null $quantity The quantity to be added to the cart.
     * @return array An associative array containing the result of the operation.
     */
    public function addToCart(Player &$player, Item &$item, int|null $quantity = null): array {
        // Find if the item already exists in the player's cart
        $cartItem = PlayerCart::findOne(['player_id' => $player->id, 'item_id' => $item->id]);

        // If the item already exists, increment its quantity; otherwise, create a new PlayerCart model
        if ($cartItem) {
            $cartItem->quantity += $quantity ?? 1;
        } else {
            $cartItem = new PlayerCart(['player_id' => $player->id, 'item_id' => $item->id, 'quantity' => $quantity ?? 1]);
        }

        // Attempt to save the changes to the database
        if ($cartItem->save()) {
            $spent = $item->cost * $quantity;
            // If the save operation is successful, deduct the item cost from the player's coins
            $this->spend($player->playerCoins, $spent, $item->coin);
            // Return a success message
            return ['error' => false, 'msg' => "Item {$item->name} is added to your cart"];
        }

        // If the save operation fails, return an error message
        return ['error' => true, 'msg' => 'Save failed'];
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
     * @return array The JSON response indicating the success or failure of the validation.
     */
    private function validateSingleItemPurchase(PlayerCart &$playerCart): array {
        // Find the corresponding PlayerItem for the player cart item
        $playerItem = PlayerItem::findOne([
            'player_id' => $playerCart->player_id,
            'item_id' => $playerCart->item_id
        ]);

        $item = $playerCart->item;
        if (!$item) {
            return ['error' => true, 'msg' => "No item found in the cart"];
        }
        // Check if a PlayerItem exists
        if ($playerItem) {
            // Update the quantity of the existing PlayerItem
            $playerItem->quantity += $playerCart->quantity * ($item->quantity ?? 1);
        } else {
            $playerItem = $this->addToInventory($playerCart);
        }

        // Save the PlayerItem and return success if saving is successful
        if ($playerItem->save()) {
            return ['error' => false, 'msg' => 'Cart is validated'];
        }

        // Return an error response if saving failed
        return ['error' => true, 'msg' => "Could not validate the purchase of {$item->name}"];
    }

    private function addToInventory(PlayerCart &$playerCart): PlayerItem {
        $classId = $playerCart->player->class_id;
        $isProficient = PlayerComponent::isProficient($classId, $playerCart->item_id) ? 1 : 0;

        $item = $playerCart->item;

        // By default, weapons and armors are carried.
        $itemType = $item->itemType;
        $isCarrying = ($itemType->name === 'Weapon' || $itemType->name === 'Armor');

        // Create a new PlayerItem if it does not exist
        $playerItem = new PlayerItem([
            'player_id' => $playerCart->player_id,
            'item_id' => $playerCart->item_id,
            'item_name' => $item->name,
            'item_type' => $item->itemType->name,
            'image' => $item->image,
            'quantity' => $playerCart->quantity * $item->quantity,
            'is_carrying' => $isCarrying,
            'is_proficient' => $isProficient,
            'is_two_handed' => ($item->weapon ? $item->weapon->is_two_handed : 0),
        ]);

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
     * @return array An associative array containing 'error' and 'msg' keys to
     *               indicate the validation result.
     */
    private function validatePackPurchase(PlayerCart &$playerCart): array {
        // Retrieve the list of packs associated with the item in the cart
        $packs = $playerCart->item->packs;

        // Create a new instance of PlayerCart for processing each item in the pack
        $innerPack = new PlayerCart();

        // Iterate through each pack item
        foreach ($packs as $pack) {
            // Set the player ID for the inner pack
            $innerPack->player_id = $playerCart->player_id;

            // Set the item ID for the inner pack
            $innerPack->item_id = $pack->item_id;

            // Calculate and set the quantity for the inner pack
            $innerPack->quantity = $playerCart->quantity * $pack->quantity;

            // Validate the inner pack item as a single item
            $success = $this->validateSingleItemPurchase($innerPack);

            // If validation fails for any item, return the error response
            if ($success['error']) {
                return $success;
            }
        }

        // Return a success message
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
     * @return array An associative array containing 'error' and 'msg' keys to
     *               indicate the validation result.
     */
    public function validatePurchase(PlayerCart $playerCart): array {
        // Check if the item type in the cart is 'Pack'
        if ($playerCart->item->itemType->name === 'Pack') {
            // Validate the purchase as a pack
            $success = $this->validatePackPurchase($playerCart);
        } else {
            // Validate the purchase as a single item
            $success = $this->validateSingleItemPurchase($playerCart);
        }

        // If validation fails for any item, return the error response
        if ($success['error']) {
            return $success;
        }

        // Delete the original cart item if all items (single or pack)
        // are successfully validated
        $playerCart->delete();

        // Return a success message
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
     * @return array The organized data for rendering the shop page.
     */
    public function loadShopData(array $items): array {
        // Initialize an array to store organized shop data.
        $data = [];

        // Iterate through each model and populate the data array.
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

        // Iterate through each specified tab and sort the items within
        // that tab based on their values.
        foreach ($this->itemTypes as $tab) {
            if (isset($data[$tab])) {
                $items = $data[$tab];

                // Extract the 'value' column from the items and sort the items
                // in ascending order based on values.
                $value = array_column($items, 'value');
                array_multisort($value, SORT_ASC, $items);

                // Update the data array with the sorted items.
                $data[$tab] = $items;
            }
        }

        // Return the organized and sorted shop data.
        return $data;
    }
}
