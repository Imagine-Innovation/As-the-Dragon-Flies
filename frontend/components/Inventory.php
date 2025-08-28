<?php

namespace frontend\components;

use common\models\Item;
use common\models\Player;
use common\models\PlayerItem;
use Yii;

class Inventory
{

    /**
     * Retrieves a list of unique item types from a collection of models.
     *
     * This function iterates over the provided models, extracts the item type
     * from each model's item, and returns a list of unique item types.
     *
     * @param PlayerItem[] $playerItems An array of models, where each model contains
     *                     an item with an item type.
     * @return array A list of unique item types.
     */
    public function getItemTypes(array $playerItems): array {
        // Initialize an empty array to hold unique item types
        $itemTypes = [];

        // Iterate over each model in the provided models array
        foreach ($playerItems as $playerItem) {
            // Extract the item type from the model's item
            $itemType = $playerItem->item_type;

            // Check if the item type is not already in the itemTypes array
            if (!in_array($itemType, $itemTypes)) {
                // Add the item type to the itemTypes array
                $itemTypes[] = $itemType;
            }
        }

        // Return the array of unique item types
        return $itemTypes;
    }

    /**
     * Loads data for the shop page.
     *
     * This method prepares the data required for rendering the shop page.
     * It organizes the models into categories based on their item types
     * and sorts the items within each category by their copper values
     * in ascending order.
     *
     * @param PlayerItem[] $playerItems The models to load data for.
     * @return array The organized data for rendering the shop page.
     */
    public function loadItemsData(array $playerItems): array {
        // Initialize an array to store organized shop data.
        $data = [];

        // Iterate through each model and populate the data array.
        foreach ($playerItems as $playerItem) {
            $item = $playerItem->item;
            $itemType = $playerItem->item_type;
            $data[$itemType][] = [
                'id' => $playerItem->item_id,
                'type' => $itemType,
                'name' => $item->name,
                'description' => $item->description,
                'weight' => $item->weight * $playerItem->quantity / $item->quantity,
                'quantity' => $playerItem->quantity,
                'image' => $playerItem->image,
                'is_carrying' => $playerItem->is_carrying,
            ];
        }

        // Iterate through each specified tab and sort the items within
        // that tab based on their values.
        foreach ($this->getItemTypes($playerItems) as $tab) {
            if (isset($data[$tab])) {
                $items = $data[$tab];

                // Extract the 'value' column from the items and sort the items
                // in ascending order based on values.
                $value = array_column($items, 'name');
                array_multisort($value, SORT_ASC, $items);

                // Update the data array with the sorted items.
                $data[$tab] = $items;
            }
        }

        // Return the organized and sorted shop data.
        return $data;
    }

    /**
     * Calculates the total weight of items being carried.
     *
     * This function iterates over the provided models, checks if the model is
     * marked as carrying, and sums up the weights of the items being carried,
     * considering their quantities.
     *
     * @param PlayerItem[] $playerItems An array of models, where each model contains
     *                             an item with a weight and a quantity.
     * @return float The total weight of the items being carried.
     */
    public function packWeight(array $playerItems): float {
        // Initialize the total weight to 0
        $weight = 0;

        // Iterate over each model in the provided models array
        foreach ($playerItems as $playerItem) {
            // Check if the model is marked as carrying the item
            if ($playerItem->is_carrying) {
                // Add the weight of the item multiplied by its quantity
                // to the total weight
                $item = $playerItem->item;
                $weight += $item->weight * $playerItem->quantity / $item->quantity;
            }
        }

        // Return the total weight of the items being carried
        return $weight;
    }

    public function getContainer(Player $player): Item|null {
        $items = $player->items;
        if (!$items) {
            return null;
        }

        // Search for the possible containers
        $containerItems = [];
        foreach ($items as $item) {
            $categories = $item->categories;
            if (in_array("Container", array_column($categories, 'name'))) {
                $containerItems[] = $item;
            }
        }

        // get the largest container
        $containerItem = null;
        $maxLoad = 0;
        foreach ($containerItems as $item) {
            if ($item->max_load > $maxLoad) {
                $maxLoad = $item->max_load;
                $containerItem = $item;
            }
        }
        return $containerItem;
    }

    public function addToPack(PlayerItem $playerItem, Item $containerItem): array {
        $weight = $playerItem->item->weight;
        if ($weight === null) {
            return ['error' => true, 'msg' => 'It is not possible to add this item to a pack. It weighs nothing.'];
        }

        if ($playerItem->is_carrying) {
            return ['error' => true, 'msg' => 'Your have already packed this item'];
        }

        $maxLoad = $containerItem->max_load;
        $playerItems = PlayerItem::findAll(['player_id' => $playerItem->player_id]);
        $actualLoad = $this->packWeight($playerItems);
        if ($actualLoad + $weight > $maxLoad) {
            return ['error' => true, 'msg' => 'You\'ve reached the maximum weight your ' . $containerItem->name . ' can carry. You must first remove one item before adding this one.'];
        }

        $playerItem->is_carrying = 1;
        $save = $playerItem->save();

        if ($save) {
            return ['error' => false, 'msg' => 'The item "' . $playerItem->item_name . '" has been successfully added to your ' . $containerItem->name];
        }
        return ['error' => true, 'msg' => 'Internal Error. Could not add the item "' . $playerItem->item_name . '" to the ' . $containerItem->name];
    }

    public function removeFromPack($playerItem, $containerItem) {
        if (!$playerItem->is_carrying) {
            return ['error' => true, 'msg' => 'Your haven\'t packed this item yet'];
        }

        $playerItem->is_carrying = 0;
        $save = $playerItem->save();

        if ($save) {
            return ['error' => false, 'msg' => 'The item "' . $playerItem->item_name . '" has been successfully removed from your ' . $containerItem->name];
        }
        return ['error' => true, 'msg' => 'Internal Error. Could not remove the item "' . $playerItem->item_name . '" from the ' . $containerItem->name];
    }
}
