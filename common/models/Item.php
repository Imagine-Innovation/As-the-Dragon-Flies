<?php

namespace common\models;

use frontend\components\Shopping;
use Yii;

/**
 * This is the model class for table "item".
 *
 * @property int $id Primary key
 * @property int $item_type_id Foreign key to "item_type" table
 * @property int|null $image_id Foreign key to "image" table. Null if no image is linked to the item.
 * @property string $name Item
 * @property string|null $description A textual description of the equipment, providing additional details about its appearance, properties, or usage.
 * @property int $sort_order Sort order
 * @property int|null $cost Unitary cost of the item
 * @property string|null $coin Currency
 * @property int $quantity The number of unit contained in the item
 * @property float|null $weight The weight of the equipment item, typically in pounds (lb). This value is important for tracking encumbrance and carrying capacity.
 * @property float|null $max_load The maximum load a container can carry (in lbs)
 * @property float|null $max_volume Maximum volume a container can hold (in liters)
 * @property int $is_packable Indicates that the item can be packed
 * @property int $is_wearable Indicates that the item can be worn
 * @property int $is_purchasable Indicates that the item can be bought in the shop
 *
 * @property Armor $armor
 * @property BackgroundItem[] $backgroundItems
 * @property Category[] $categories
 * @property ClassEquipment[] $classEquipments
 * @property ClassItemProficiency[] $classItemProficiencies
 * @property CharacterClass[] $classes
 * @property GridItem[] $gridItems
 * @property Grid[] $grids
 * @property Image $image
 * @property ItemCategory[] $itemCategories
 * @property ItemType $itemType
 * @property Item[] $packItems
 * @property Pack[] $packs
 * @property Item[] $parentItems
 * @property Category $mainCategory
 * @property PassageStatusItem[] $passageStatusItems
 * @property PlayerCart[] $playerCarts
 * @property PlayerItem[] $playerItems
 * @property Player[] $players
 * @property Player[] $players0
 * @property Poison $poison
 * @property PassageStatus[] $statuses
 * @property Weapon $weapon
 * @property Weapon[] $weapons
 *
 * @property string $category
 * @property string $pounds
 * @property string $price
 * @property int    $copperValue
 * @property string $armorClassString
 * @property int    $armorStrength
 * @property string $armorDisadvantage
 * @property string $damageDice
 * @property string $weaponPropertiesString
 * @property string $poisonType
 * @property string $picture
 */
class Item extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['image_id', 'description', 'cost', 'coin', 'weight', 'max_load', 'max_volume'], 'default', 'value' => null],
            [['is_purchasable'], 'default', 'value' => 1],
            [['is_wearable'], 'default', 'value' => 0],
            [['item_type_id', 'name'], 'required'],
            [['item_type_id', 'image_id', 'sort_order', 'cost', 'quantity', 'is_packable', 'is_wearable', 'is_purchasable'], 'integer'],
            [['description'], 'string'],
            [['weight', 'max_load', 'max_volume'], 'number'],
            [['name'], 'string', 'max' => 32],
            [['coin'], 'string', 'max' => 2],
            [['name'], 'unique'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
            [['item_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ItemType::class, 'targetAttribute' => ['item_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'item_type_id' => 'Foreign key to \"item_type\" table',
            'image_id' => 'Foreign key to \"image\" table. Null if no image is linked to the item.',
            'name' => 'Item',
            'description' => 'A textual description of the equipment, providing additional details about its appearance, properties, or usage.',
            'sort_order' => 'Sort order',
            'cost' => 'Unitary cost of the item',
            'coin' => 'Currency',
            'quantity' => 'The number of unit contained in the item',
            'weight' => 'The weight of the equipment item, typically in pounds (lb). This value is important for tracking encumbrance and carrying capacity.',
            'max_load' => 'The maximum load a container can carry (in lbs)',
            'max_volume' => 'Maximum volume a container can hold (in liters)',
            'is_packable' => 'Indicates that the item can be packed',
            'is_wearable' => 'Indicates that the item can be worn',
            'is_purchasable' => 'Indicates that the item can be bought in the shop',
        ];
    }

    /**
     * Gets query for [[Armor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArmor() {
        return $this->hasOne(Armor::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[BackgroundItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackgroundItems() {
        return $this->hasMany(BackgroundItem::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategories() {
        return $this->hasMany(Category::class, ['id' => 'category_id'])->via('itemCategories');
    }

    /**
     * Gets query for [[ClassEquipments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClassEquipments() {
        return $this->hasMany(ClassEquipment::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[ClassItemProficiencies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClassItemProficiencies() {
        return $this->hasMany(ClassItemProficiency::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Classes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClasses() {
        return $this->hasMany(CharacterClass::class, ['id' => 'class_id'])->via('classItems');
    }

    /**
     * Gets query for [[GridItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGridItems() {
        return $this->hasMany(GridItem::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Grids]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrids() {
        return $this->hasMany(Grid::class, ['id' => 'grid_id'])->via('gridItems');
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage() {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

    /**
     * Gets query for [[ItemCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemCategories() {
        return $this->hasMany(ItemCategory::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[ItemType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemType() {
        return $this->hasOne(ItemType::class, ['id' => 'item_type_id']);
    }

    /**
     * Gets query for [[Pack]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPacks() {
        return $this->hasMany(Pack::class, ['parent_item_id' => 'id']);
    }

    /**
     * Gets query for [[PackItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPackItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->via('packs');
    }

    /**
     * Gets query for [[ParentItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParentItems() {
        return $this->hasMany(Item::class, ['id' => 'parent_item_id'])->via('packs');
    }

    /**
     * Gets the main category of the item.
     *
     * @return ActiveQuery
     */
    public function getMainCategory() {
        return $this->hasOne(Category::class, ['id' => 'category_id'])
                        ->viaTable('item_category', ['item_id' => 'id'], function ($query) {
                            $query->andWhere(['item_category.is_main' => 1]);
                        });
    }

    /**
     * Gets query for [[PassageStatusItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassageStatusItems() {
        return $this->hasMany(PassageStatusItem::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerCarts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerCarts() {
        return $this->hasMany(PlayerCart::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerItems() {
        return $this->hasMany(PlayerItem::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('playerCarts');
    }

    /**
     * Gets query for [[Players0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers0() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('playerItems');
    }

    /**
     * Gets query for [[Poison]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPoison() {
        return $this->hasOne(Poison::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Statuses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatuses() {
        return $this->hasMany(PassageStatus::class, ['id' => 'status_id'])->via('passageStatusItems');
    }

    /**
     * Gets query for [[Weapon]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWeapon() {
        return $this->hasOne(Weapon::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Weapons]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWeapons() {
        return $this->hasMany(Weapon::class, ['amunition_id' => 'id']);
    }

    /**
     * *****************************
     *      Custom Properties
     * ***************************** */

    /**
     * Select the main category name among the different item categories
     *
     * @return string
     */
    public function getCategory(): string {
        $mainCategory = Category::find()
                ->joinWith('itemCategories')
                ->joinWith('itemCategories.item')
                ->where(['item_category.is_main' => 1, 'item_category.item_id' => $this->id])
                ->one();

        return $mainCategory ? $mainCategory->name : "Undefined";
    }

    /**
     * Calculates and returns the total weight of an item or pack in both pounds
     * (lb) and kilograms (kg).
     *
     * @return string The formatted weight string in the format "X lb. (Y kg)" or
     *                an empty string if the weight is zero.
     */
    public function getPounds() {
        // Initialize the weight variable to zero.
        $weight = 0;

        // Check if the item type is a "Pack".
        if ($this->itemType->name == "Pack") {
            // Retrieve the pack items associated with the pack.
            $packItems = $this->packItems;

            // Iterate through each pack item and accumulate the weights.
            foreach ($packItems as $packItem) {
                $weight += $packItem->weight;
            }
        } else {
            // If the item type is not a "Pack", use the item's own weight.
            $weight = $this->weight;
        }

        // If the calculated weight is greater than zero, convert it to kilograms.
        if ($weight > 0) {
            $kgWeight = number_format($weight / 2.205, 2);

            // Return the formatted weight string.
            return "$weight lb. ($kgWeight kg)";
        }

        // Return an empty string if the weight is zero.
        return "";
    }

    /**
     * Retrieves and formats the price of an item or service.
     *
     * @return string The formatted price string or "free" if the item is free of charge.
     */
    public function getPrice() {
        // Check if the model has a non-zero cost value.
        if ($this->cost && $this->cost > 0) {
            // Format the cost to two decimal places and append the coin type.
            return number_format($this->cost, 0) . ' ' . $this->coin;
        }

        // Return "free" if the item or service is free of charge (cost is zero or not set).
        return "free";
    }

    /**
     * Converts the price of an item or service into its equivalent value in copper coins.
     *
     * @return int The equivalent value in copper coins based on the provided
     *             item's cost and coin type.
     */
    public function getCopperValue() {
        // Create a new instance of the Shopping class.
        $shopping = new Shopping();

        // Call the 'copperValue' method of the Shopping class to convert the
        // item's cost to copper coins.
        return $shopping->copperValue($this->cost, $this->coin);
    }

    /**
     * Generates a string with the item's armor class for the Armor object type.
     *
     * @return string The formatted armor class string indicating base armor class,
     *                DEX modifier, max modifier, and armor bonus.
     */
    public function getArmorClass() {
        return $this->armor->armorClass;
    }

    /**
     * Retrieves the required strength for wearing the specified Armor.
     *
     * @return int The required strength for wearing the armor.
     */
    public function getArmorStrength() {
        return $this->armor->strength;
    }

    /**
     * Retrieves a CSS class indicating whether wearing the specified Armor
     * imposes disadvantage.
     *
     * @return string The CSS class 'bi-check-lg' if disadvantage is imposed,
     *                otherwise an empty string.
     */
    public function getArmorDisadvantage() {
        return $this->armor->is_disadvantage ? 'bi-check-lg' : "";
    }

    /**
     * Retrieves a formatted string listing the properties of the specified Weapon.
     *
     * This function iterates through the defined weapon properties, checks if the
     * weapon has each property, and constructs a formatted string with relevant
     * information such as range, versatile dice, etc.
     *
     * @return string The formatted string listing weapon properties, separated by commas.
     */
    public function getWeaponProperties(): string {
        return $this->weapon->properties;
    }

    /**
     * Retrieves the damage dice string of the specified Weapon.
     *
     * @return string The damage dice string of the weapon.
     */
    public function getDamageDice() {
        return $this->weapon->damage_dice;
    }

    /**
     * Retrieves the poison type of the specified Poison.
     *
     * @return string The poison type of the specified poison.
     */
    public function getPoisonType() {
        return $this->poison->poison_type;
    }

    /**
     * Retrieves the file name of the item's image.
     *
     * @return string The image file name
     */
    public function getPicture() {
        if ($this->image) {
            return $this->image->file_name;
        }
        return null;
    }
}
