<?php

namespace common\models;

use common\helpers\RichTextHelper;
use common\components\Shopping;
use Yii;

/**
 * This is the model class for table "item".
 *
 * @property int $id Primary key
 * @property int $item_type_id Foreign key to “item_type” table
 * @property string $name Item
 * @property string|null $description A textual description of the equipment, providing additional details about its appearance, properties, or usage.
 * @property string|null $image Image
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
 * @property Action[] $actions
 * @property Armor|null $armor
 * @property BackgroundItem[] $backgroundItems
 * @property Category[] $categories
 * @property ClassEquipment[] $classEquipments
 * @property ClassItemProficiency[] $classItemProficiencies
 * @property DecorItem[] $decorItems
 * @property ItemCategory[] $itemCategories
 * @property ItemType $itemType
 * @property Item[] $packItems
 * @property Pack[] $packs
 * @property Pack[] $packs0
 * @property Item[] $parentItems
 * @property PlayerCart[] $playerCarts
 * @property PlayerItem[] $playerItems
 * @property Player[] $players
 * @property Player[] $players0
 * @property Poison|null $poison
 * @property Scroll|null $scroll
 * @property Weapon|null $weapon
 * @property Weapon[] $weapons
 *
 * Custom properties
 * @property string $category
 * @property string $totalWeight
 * @property string $price
 * @property int $copperValue
 * @property string $armorClass
 * @property int $armorStrength
 * @property string $armorDisadvantage
 * @property string $weaponProperties
 * @property string $damageDice
 * @property string $poisonType
 *
 */
class Item extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'image', 'cost', 'coin', 'weight', 'max_load', 'max_volume'], 'default', 'value' => null],
            [['is_purchasable'], 'default', 'value' => 1],
            [['is_wearable'], 'default', 'value' => 0],
            [['item_type_id', 'name'], 'required'],
            [
                ['item_type_id', 'sort_order', 'cost', 'quantity', 'is_packable', 'is_wearable', 'is_purchasable'],
                'integer',
            ],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['weight', 'max_load', 'max_volume'], 'number'],
            [['name', 'image'], 'string', 'max' => 64],
            [['coin'], 'string', 'max' => 2],
            [['name'], 'unique'],
            [
                ['item_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ItemType::class,
                'targetAttribute' => ['item_type_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'item_type_id' => 'Foreign key to “item_type” table',
            'name' => 'Item',
            'description' => 'A textual description of the equipment, providing additional details about its appearance, properties, or usage.',
            'image' => 'Image',
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
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery<Action>
     */
    public function getActions()
    {
        return $this->hasMany(Action::class, ['required_item_id' => 'id']);
    }

    /**
     * Gets query for [[Armor]].
     *
     * @return \yii\db\ActiveQuery<Armor>|null
     */
    public function getArmor()
    {
        return $this->hasOne(Armor::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[BackgroundItems]].
     *
     * @return \yii\db\ActiveQuery<BackgroundItem>
     */
    public function getBackgroundItems()
    {
        return $this->hasMany(BackgroundItem::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return \yii\db\ActiveQuery<Category>
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category_id'])->viaTable('item_category', ['item_id' => 'id']);
    }

    /**
     * Gets query for [[ClassEquipments]].
     *
     * @return \yii\db\ActiveQuery<ClassEquipment>
     */
    public function getClassEquipments()
    {
        return $this->hasMany(ClassEquipment::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[ClassItemProficiencies]].
     *
     * @return \yii\db\ActiveQuery<ClassItemProficiency>
     */
    public function getClassItemProficiencies()
    {
        return $this->hasMany(ClassItemProficiency::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[DecorItems]].
     *
     * @return \yii\db\ActiveQuery<DecorItem>
     */
    public function getDecorItems()
    {
        return $this->hasMany(DecorItem::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[ItemCategories]].
     *
     * @return \yii\db\ActiveQuery<ItemCategory>
     */
    public function getItemCategories()
    {
        return $this->hasMany(ItemCategory::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[ItemType]].
     *
     * @return \yii\db\ActiveQuery<ItemType>
     */
    public function getItemType()
    {
        return $this->hasOne(ItemType::class, ['id' => 'item_type_id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->viaTable('pack', ['parent_item_id' => 'id']);
    }

    /**
     * Gets query for [[Outcomes]].
     *
     * @return \yii\db\ActiveQuery<Outcome>
     */
    public function getOutcomes()
    {
        return $this->hasMany(Outcome::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Packs]].
     *
     * @return \yii\db\ActiveQuery<Pack>
     */
    public function getPacks()
    {
        return $this->hasMany(Pack::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Packs0]].
     *
     * @return \yii\db\ActiveQuery<Pack>
     */
    public function getPacks0()
    {
        return $this->hasMany(Pack::class, ['parent_item_id' => 'id']);
    }

    /**
     * Gets query for [[PackItems]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getPackItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->viaTable('pack', ['item_id' => 'id']);
    }

    /**
     * Gets query for [[ParentItems]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getParentItems()
    {
        return $this->hasMany(Item::class, ['id' => 'parent_item_id'])->viaTable('pack', ['item_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerCarts]].
     *
     * @return \yii\db\ActiveQuery<PlayerCart>
     */
    public function getPlayerCarts()
    {
        return $this->hasMany(PlayerCart::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerItems]].
     *
     * @return \yii\db\ActiveQuery<PlayerItem>
     */
    public function getPlayerItems()
    {
        return $this->hasMany(PlayerItem::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayers()
    {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('player_cart', ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Players0]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayers0()
    {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('player_item', ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Poison]].
     *
     * @return \yii\db\ActiveQuery<Poison>|null
     */
    public function getPoison()
    {
        return $this->hasOne(Poison::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Scroll]].
     *
     * @return \yii\db\ActiveQuery<Scroll>|null
     */
    public function getScroll()
    {
        return $this->hasOne(Scroll::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Weapon]].
     *
     * @return \yii\db\ActiveQuery<Weapon>|null
     */
    public function getWeapon()
    {
        return $this->hasOne(Weapon::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[Weapons]].
     *
     * @return \yii\db\ActiveQuery<Weapon>
     */
    public function getWeapons()
    {
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
    public function getCategory(): string
    {
        $mainCategory = Category::find()
                ->joinWith('itemCategories')
                ->joinWith('itemCategories.item')
                ->where(['item_category.is_main' => 1, 'item_category.item_id' => $this->id])
                ->one();

        return $mainCategory ? $mainCategory->name : 'Undefined';
    }

    /**
     * Calculates and returns the total weight of an item or pack in both pounds
     * (lb) and kilograms (kg).
     *
     * @return string The formatted weight string in the format "X lb. (Y kg)" or
     *                an empty string if the weight is zero.
     */
    public function getTotalWeight(): string
    {
        // Initialize the weight variable to zero.
        $weight = 0;

        // Check if the item type is a "Pack".
        if ($this->itemType->name === 'Pack') {
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
        return '';
    }

    /**
     * Retrieves and formats the price of an item or service.
     *
     * @return string The formatted price string or "free" if the item is free of charge.
     */
    public function getPrice(): string
    {
        // Check if the model has a non-zero cost value.
        if ($this->cost && $this->cost > 0) {
            // Format the cost to two decimal places and append the coin type.
            return number_format($this->cost, 0) . ' ' . $this->coin;
        }

        // Return "free" if the item or service is free of charge (cost is zero or not set).
        return 'free';
    }

    /**
     * Converts the price of an item or service into its equivalent value in copper coins.
     *
     * @return int The equivalent value in copper coins based on the provided
     *             item's cost and coin type.
     */
    public function getCopperValue(): int
    {
        $shopping = new Shopping();

        // Call the 'copperValue' method of the Shopping class to convert the
        // item's cost into copper coins.
        return $shopping->copperValue($this->cost ?? 0, $this->coin ?? 'gp');
    }

    /**
     * Generates a string with the item's armor class for the Armor object type.
     *
     * @return string The formatted armor class string indicating base armor class,
     *                DEX modifier, max modifier, and armor bonus.
     */
    public function getArmorClass(): string
    {
        return $this->armor?->armorClass ? $this->armor->armorClass : '';
    }

    /**
     * Retrieves the required strength for wearing the specified Armor.
     *
     * @return int The required strength for wearing the armor.
     */
    public function getArmorStrength(): int
    {
        return $this->armor?->strength ? $this->armor->strength : 0;
    }

    /**
     * Retrieves a CSS class indicating whether wearing the specified Armor
     * imposes disadvantage.
     *
     * @return string The CSS class 'bi-check-lg' if disadvantage is imposed,
     *                otherwise an empty string.
     */
    public function getArmorDisadvantage(): string
    {
        return $this->armor?->is_disadvantage ? 'bi-check-lg' : '';
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
    public function getWeaponProperties(): string
    {
        return $this->weapon?->properties ? $this->weapon->properties : '';
    }

    /**
     * Retrieves the damage dice string of the specified Weapon.
     *
     * @return string|null The damage dice string of the weapon.
     */
    public function getDamageDice(): ?string
    {
        return $this->weapon?->damage_dice ? $this->weapon->damage_dice : '';
    }

    /**
     * Retrieves the poison type of the specified Poison.
     *
     * @return string The poison type of the specified poison.
     */
    public function getPoisonType(): string
    {
        return $this->poison?->poison_type ? $this->poison->poison_type : '';
    }
}
