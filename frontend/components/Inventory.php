<?php

namespace frontend\components;

use common\models\Item;
use common\models\Player;
use common\models\PlayerItem;
use Yii;

class Inventory
{
    const IGNORE_TYPES = ['Armor', 'Weapon', 'Shield'];

    /**
     * Retrieves a list of unique item types from a collection of models.
     *
     * This function iterates over the provided models, extracts the item type
     * from each model's item, and returns a list of unique item types.
     *
     * @param PlayerItem[] $playerItems An array of models, where each model contains
     *                     an item with an item type.
     * @return array<string> A list of unique item types.
     */
    public function getItemTypes(array $playerItems): array
    {
        $itemTypes = [];

        foreach ($playerItems as $playerItem) {
            $itemType = $playerItem->item_type;

            if (!in_array($itemType, $itemTypes)) {
                $itemTypes[] = $itemType;
            }
        }

        return $itemTypes;
    }

    /**
     * Loads data for the shop page.
     *
     * This method prepares the data required for rendering the shop page.
     *
     * @param PlayerItem[] $playerItems The models to load data for.
     * @return array<string, list<array<string, float|int|string|null>>> The organized data for rendering the shop page.
     */
    private function prepareItemsData(array $playerItems): array
    {
        $data = [];

        foreach ($playerItems as $playerItem) {
            $item = $playerItem->item;
            $itemType = $playerItem->item_type;
            $data[$itemType][] = [
                'id' => $playerItem->item_id,
                'type' => $itemType,
                'name' => $item->name,
                'description' => $item->description,
                'weight' => ($item->weight * $playerItem->quantity) / $item->quantity,
                'quantity' => $playerItem->quantity,
                'image' => $playerItem->image,
                'is_carrying' => $playerItem->is_carrying,
            ];
        }

        return $data;
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
     * @return array<string, list<array<string, float|int|string|null>>> The organized data for rendering the shop page.
     */
    public function loadItemsData(array $playerItems): array
    {
        $data = $this->prepareItemsData($playerItems);

        foreach ($this->getItemTypes($playerItems) as $tab) {
            if (isset($data[$tab])) {
                $items = $data[$tab];

                $value = array_column($items, 'name');
                array_multisort($value, SORT_ASC, $items);

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
     * @param Player $player
     * @return float The total weight of the items being carried.
     */
    private function packWeight(Player $player): float
    {
        $weight = 0;

        foreach ($player->playerItems as $playerItem) {
            if ($playerItem->is_carrying && !in_array($playerItem->item_type, self::IGNORE_TYPES)) {
                $item = $playerItem->item;
                $weight += (($item->weight ?? 0) * ($playerItem->quantity ?? 1)) / ($item->quantity ?? 1);
            }
        }

        return $weight;
    }

    /**
     *
     * @param Player $player
     * @return Item|null
     */
    public function getContainer(Player $player): ?Item
    {
        $items = $player->items;
        if (!$items) {
            return null;
        }

        // Search for the possible containers
        $containerItems = [];
        foreach ($items as $item) {
            $categories = $item->categories;
            if (in_array('Container', array_column($categories, 'name'))) {
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

    /**
     *
     * @param PlayerItem $playerItem
     * @param Item $containerItem
     * @return array{error: bool, msg: string, content?: string}
     */
    public function addToPack(PlayerItem $playerItem, Item $containerItem): array
    {
        $weight = $playerItem->item->weight ?? 0;

        $itemType = $playerItem->item_type;
        if (in_array($itemType, self::IGNORE_TYPES)) {
            return ['error' => true, 'msg' => "{$itemType} items don't have to be packed"];
        }

        if ($playerItem->is_carrying) {
            return ['error' => true, 'msg' => 'Your have already packed this item'];
        }

        $maxLoad = $containerItem->max_load;
        $packLoad = $this->packWeight($playerItem->player);
        if (($packLoad + $weight) > $maxLoad) {
            return [
                'error' => true,
                'msg' => "You've reached the maximum weight your {$containerItem->name} can carry. You must first remove one item before adding this one.",
            ];
        }

        $playerItem->is_carrying = 1;
        $saved = $playerItem->save();

        if ($saved) {
            return [
                'error' => false,
                'msg' => "Your {$playerItem->item_name} has been successfully added to your {$containerItem->name}",
            ];
        }
        return [
            'error' => true,
            'msg' => "Internal Error. Could not add the item {$playerItem->item_name} to the {$containerItem->name}",
        ];
    }

    /**
     *
     * @param PlayerItem $playerItem
     * @param Item $containerItem
     * @return array{error: bool, msg: string, content?: string}
     */
    public function removeFromPack(PlayerItem $playerItem, Item $containerItem): array
    {
        $itemType = $playerItem->item_type;
        if (in_array($itemType, self::IGNORE_TYPES)) {
            return ['error' => true, 'msg' => "{$itemType} items cannot be unpacked"];
        }

        if (!$playerItem->is_carrying) {
            return ['error' => true, 'msg' => "Your haven't packed this item yet"];
        }

        $playerItem->is_carrying = 0;
        $saved = $playerItem->save();

        if ($saved) {
            return [
                'error' => false,
                'msg' => "Your {$playerItem->item_name} has been successfully removed from your {$containerItem->name}",
            ];
        }
        return [
            'error' => true,
            'msg' => "Internal Error. Could not remove the item {$playerItem->item_name} from the {$containerItem->name}",
        ];
    }
}
