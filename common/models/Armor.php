<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "armor".
 *
 * @property int $item_id Primary key synchronized 1:1 with the "item" table
 * @property int $armor_class Armor protects its wearer from attacks. The armor (and shield) you wear determines your base Armor Class.
 * @property int $armor_bonus Only the shield provides a bonus to the armor class
 * @property int $dex_modifier Dex modifier
 * @property int $max_modifier Max Dex modifier
 * @property int $strength Minimum Strength ability to use this armor
 * @property int $is_disadvantage Indicates that the wearer has disadvantage on Dexterity (Stealth) checks
 * @property int $don_delay This is the time it takes to put on armor. You benefit from the armor’s AC only if you take the full time to don the suit of armor.
 * @property int $doff_delay This is the time it takes to take off armor. If you have help, reduce this time by half.
 * @property string $delay_unit Delay unit (minute or action)
 *
 * @property ShapeArmor[] $shapeArmors
 * @property Shape[] $shapes
 *
 * @property string $armorClass
 */
class Armor extends Item {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'armor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return array_merge(parent::rules(), [
            [['item_id', 'armor_class', 'dex_modifier', 'max_modifier', 'strength', 'don_delay', 'doff_delay', 'delay_unit'], 'required'],
            [['item_id', 'armor_class', 'armor_bonus', 'dex_modifier', 'max_modifier', 'strength', 'is_disadvantage', 'don_delay', 'doff_delay'], 'integer'],
            [['delay_unit'], 'string', 'max' => 8],
            [['item_id'], 'unique'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return array_merge(parent::attributeLabels(), [
            'item_id' => 'Primary key synchronized 1:1 with the \"item" table',
            'armor_class' => 'Armor protects its wearer from attacks. The armor (and shield) you wear determines your base Armor Class.',
            'armor_bonus' => 'Only the shield provides a bonus to the armor class',
            'dex_modifier' => 'Dex modifier',
            'max_modifier' => 'Max Dex modifier',
            'strength' => 'Minimum Strength ability to use this armor',
            'is_disadvantage' => 'Indicates that the wearer has disadvantage on Dexterity (Stealth) checks',
            'don_delay' => 'This is the time it takes to put on armor. You benefit from the armor’s AC only if you take the full time to don the suit of armor.',
            'doff_delay' => 'This is the time it takes to take off armor. If you have help, reduce this time by half.',
            'delay_unit' => 'Delay unit (minute or action)',
        ]);
    }

    /**
     * Gets query for [[ShapeArmors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeArmors() {
        return $this->hasMany(ShapeArmor::class, ['armor_id' => 'item_id']);
    }

    /**
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapes() {
        return $this->hasMany(Shape::class, ['id' => 'shape_id'])->via('shapeArmors');
    }

    /**
     * **** CUSTOM PROPERTIES ****
     */

    /**
     * Generates a string with the item's armor class for the Armor object type.
     *
     * @return string The formatted armor class string indicating base armor class,
     *                DEX modifier, max modifier, and armor bonus.
     */
    public function getArmorClass() {
        $armorClass = "";

        // Append base armor class if greater than zero.
        $armorClass .= $this->armor_class > 0 ? $this->armor_class : "";

        // Append DEX modifier if present.
        $armorClass .= $this->dex_modifier ? " +DEX" : "";

        // Append max modifier if greater than zero.
        $armorClass .= $this->max_modifier > 0 ? " (max " . $this->max_modifier . ")" : "";

        // Append armor bonus if greater than zero.
        $armorClass .= $this->armor_bonus > 0 ? " +" . $this->armor_bonus : "";

        return trim($armorClass);
    }
}
