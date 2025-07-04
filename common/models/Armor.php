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
 * @property Item $item
 * @property ShapeArmor[] $shapeArmors
 * @property Shape[] $shapes
 */
class Armor extends \yii\db\ActiveRecord {

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
        return [
            [['item_id', 'armor_class', 'dex_modifier', 'max_modifier', 'strength', 'don_delay', 'doff_delay', 'delay_unit'], 'required'],
            [['item_id', 'armor_class', 'armor_bonus', 'dex_modifier', 'max_modifier', 'strength', 'is_disadvantage', 'don_delay', 'doff_delay'], 'integer'],
            [['delay_unit'], 'string', 'max' => 8],
            [['item_id'], 'unique'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'item_id' => 'Primary key synchronized 1:1 with the \"item\" table',
            'armor_class' => 'Armor protects its wearer from attacks. The armor (and shield) you wear determines your base Armor Class.',
            'armor_bonus' => 'Only the shield provides a bonus to the armor class',
            'dex_modifier' => 'Dex modifier',
            'max_modifier' => 'Max Dex modifier',
            'strength' => 'Minimum Strength ability to use this armor',
            'is_disadvantage' => 'Indicates that the wearer has disadvantage on Dexterity (Stealth) checks',
            'don_delay' => 'This is the time it takes to put on armor. You benefit from the armor’s AC only if you take the full time to don the suit of armor.',
            'doff_delay' => 'This is the time it takes to take off armor. If you have help, reduce this time by half.',
            'delay_unit' => 'Delay unit (minute or action)',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem() {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
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
}
