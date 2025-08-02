<?php

namespace frontend\components;

use common\models\PlayerItem;
use Yii;

class Inventory {

    /**
     * Retrieves a list of unique item types from a collection of models.
     *
     * This function iterates over the provided models, extracts the item type
     * from each model's item, and returns a list of unique item types.
     *
     * @param PlayerItem[] $models An array of models, where each model contains
     *                     an item with an item type.
     * @return array A list of unique item types.
     */
    public function getItemTypes($models) {
        // Initialize an empty array to hold unique item types
        $itemTypes = [];

        // Iterate over each model in the provided models array
        foreach ($models as $model) {
            // Extract the item type from the model's item
            $itemType = $model->item->itemType->name;

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
     * @param PlayerItem[] $models The models to load data for.
     * @return array The organized data for rendering the shop page.
     */
    public function loadItemsData($models) {
        // Initialize an array to store organized shop data.
        $data = [];

        // Iterate through each model and populate the data array.
        foreach ($models as $model) {
            $item = $model->item;
            $itemType = $item->itemType->name;
            $data[$itemType][] = [
                'id' => $model->item_id,
                'type' => $itemType,
                'name' => $item->name,
                'description' => $item->description,
                'weight' => $item->weight * $model->quantity / $item->quantity,
                'quantity' => $model->quantity,
                'image' => $item->picture,
                'is_carrying' => $model->is_carrying,
            ];
        }

        // Iterate through each specified tab and sort the items within
        // that tab based on their values.
        foreach ($this->getItemTypes($models) as $tab) {
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
     * @param PlayerItem[] $models An array of models, where each model contains
     *                             an item with a weight and a quantity.
     * @return float The total weight of the items being carried.
     */
    public function packWeight($models) {
        // Initialize the total weight to 0
        $weight = 0;

        // Iterate over each model in the provided models array
        foreach ($models as $model) {
            // Check if the model is marked as carrying the item
            if ($model->is_carrying) {
                // Add the weight of the item multiplied by its quantity
                // to the total weight
                $item = $model->item;
                $weight += $item->weight * $model->quantity / $item->quantity;
            }
        }

        // Return the total weight of the items being carried
        return $weight;
    }

    public function getContainer($player) {
        $items = $player->items;
        if (!$items) {
            return null;
        }

        $containers = [];
        foreach ($items as $item) {
            $categories = $item->categories;
            if (in_array("Container", array_column($categories, 'name'))) {
                $containers[] = $item;
            }
        }

        $container = null;
        $maxLoad = 0;
        foreach ($containers as $item) {
            if ($item->max_load > $maxLoad) {
                $maxLoad = $item->max_load;
                $container = $item;
            }
        }
        return $container;
    }

    public function addToPack($model, $container) {
        $weight = $model->item->weight;
        if ($weight === null) {
            return ['error' => true, 'msg' => 'It is not possible to add this item to a pack. It weighs nothing.'];
        }

        if ($model->is_carrying) {
            return ['error' => true, 'msg' => 'Your have already packed this item'];
        }

        $maxLoad = $container->max_load;
        $models = PlayerItem::findAll(['player_id' => $model->player_id]);
        $actualLoad = $this->packWeight($models);
        if ($actualLoad + $weight > $maxLoad) {
            return ['error' => true, 'msg' => 'You\'ve reached the maximum weight your ' . $container->name . ' can carry. You must first remove one item before adding this one.'];
        }

        $model->is_carrying = 1;
        $save = $model->save();

        if ($save) {
            return ['error' => false, 'msg' => 'The item "' . $model->item->name . '" has been successfully added to your ' . $container->name];
        }
        return ['error' => true, 'msg' => 'Internal Error. Could not add the item "' . $model->item->name . '" to the ' . $container->name];
    }

    public function removeFromPack($model, $container) {
        if (!$model->is_carrying) {
            return ['error' => true, 'msg' => 'Your haven\'t packed this item yet'];
        }

        $model->is_carrying = 0;
        $save = $model->save();

        if ($save) {
            return ['error' => false, 'msg' => 'The item "' . $model->item->name . '" has been successfully removed from your ' . $container->name];
        }
        return ['error' => true, 'msg' => 'Internal Error. Could not remove the item "' . $model->item->name . '" from the ' . $container->name];
    }
}
