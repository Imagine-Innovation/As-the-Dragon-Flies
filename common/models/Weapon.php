<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "weapon".
 *
 * @property int $item_id Primary key synchronized 1:1 with the "item" table
 * @property int|null $amunition_id Optional foreign key to weapon ammunition item. Used only when the "need_amunition" flag is set to TRUE.
 * @property int|null $damage_type_id Optional foreign key to the "damage_type" table
 * @property string|null $damage_dice Roll dice to determine damage
 * @property int $is_melee Indicates a melee weapon. A melee weapon is used to attack a target within 5 feet of you
 * @property int $is_ranged Indicates a ranged weapon. A ranged weapon is used to attack a target at a distance
 * @property int $is_simple Indicates a simple weapon. Most people can use simple weapons with proficiency
 * @property int $is_martial Indicates a martial weapon. Most warriors use martial weapons because these weapons put their fighting style and training to best use
 * @property int $need_ammunition Flag indicating that the weapon requires ammunition for use
 * @property int $is_finesse When making an attack with a finesse weapon, you use your choice of your Strength or Dexterity modifier for the attack and damage rolls. You must use the same modifier for both rolls
 * @property int $is_heavy Small creatures have disadvantage on attack rolls with heavy weapons. A heavy weapon’s size and bulk make it too large for a Small creature to use effectively
 * @property int $is_light A light weapon is small and easy to handle, making it ideal for use when fighting with two weapons.
 * @property int $is_loading Because of the time required to load this weapon, you can fire only one piece of ammunition from it when you use an action, bonus action, or reaction to fire it, regardless of the number of attacks you can normally make
 * @property int $is_range A weapon that can be used to make a ranged attack has a range in parentheses after the ammunition or thrown property. The range lists two numbers. The first is the weapon’s normal range in feet, and the second indicates the weapon’s long range. When attacking a target beyond normal range, you have disadvantage on the attack roll. You can’t attack a target beyond the weapon’s long range
 * @property int|null $range_min Minimum range
 * @property int|null $range_max Maximum range
 * @property int $is_reach This weapon adds 5 feet to your reach when you attack with it, as well as when determining your reach for opportunity attacks with it
 * @property int $is_special A weapon with the special property has unusual rules governing its use, explained in the weapon’s description
 * @property int $is_thrown If a weapon has the thrown property, you can throw the weapon to make a ranged attack. If the weapon is a melee weapon, you use the same ability modifier for that attack roll and damage roll that you would use for a melee attack with the weapon. For example, if you throw a handaxe, you use your Strength, but if you throw a dagger, you can use either your Strength or your Dexterity, since the dagger has the finesse property
 * @property int $is_two_handed This weapon requires two hands when you attack with it
 * @property int $is_versatile This weapon can be used with one or two hands
 * @property string|null $versatile_dice Roll dice to determine the damage when the weapon is used with two hands to make a melee attack
 *
 * @property Item $amunition
 * @property DamageType $damageType
 * @property Item $item
 * @property ShapeAttack[] $shapeAttacks
 */
class Weapon extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'weapon';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['item_id'], 'required'],
            [['item_id', 'amunition_id', 'damage_type_id', 'is_melee', 'is_ranged', 'is_simple', 'is_martial', 'need_ammunition', 'is_finesse', 'is_heavy', 'is_light', 'is_loading', 'is_range', 'range_min', 'range_max', 'is_reach', 'is_special', 'is_thrown', 'is_two_handed', 'is_versatile'], 'integer'],
            [['damage_dice', 'versatile_dice'], 'string', 'max' => 8],
            [['item_id'], 'unique'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['amunition_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['amunition_id' => 'id']],
            [['damage_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DamageType::class, 'targetAttribute' => ['damage_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'item_id' => 'Primary key synchronized 1:1 with the \"item\" table',
            'amunition_id' => 'Optional foreign key to weapon ammunition item. Used only when the \"need_amunition\" flag is set to TRUE.',
            'damage_type_id' => 'Optional foreign key to the \"damage_type\" table',
            'damage_dice' => 'Roll dice to determine damage',
            'is_melee' => 'Indicates a melee weapon. A melee weapon is used to attack a target within 5 feet of you',
            'is_ranged' => 'Indicates a ranged weapon. A ranged weapon is used to attack a target at a distance',
            'is_simple' => 'Indicates a simple weapon. Most people can use simple weapons with proficiency',
            'is_martial' => 'Indicates a martial weapon. Most warriors use martial weapons because these weapons put their fighting style and training to best use',
            'need_ammunition' => 'Flag indicating that the weapon requires ammunition for use',
            'is_finesse' => 'When making an attack with a finesse weapon, you use your choice of your Strength or Dexterity modifier for the attack and damage rolls. You must use the same modifier for both rolls',
            'is_heavy' => 'Small creatures have disadvantage on attack rolls with heavy weapons. A heavy weapon’s size and bulk make it too large for a Small creature to use effectively',
            'is_light' => 'A light weapon is small and easy to handle, making it ideal for use when fighting with two weapons.',
            'is_loading' => 'Because of the time required to load this weapon, you can fire only one piece of ammunition from it when you use an action, bonus action, or reaction to fire it, regardless of the number of attacks you can normally make',
            'is_range' => 'A weapon that can be used to make a ranged attack has a range in parentheses after the ammunition or thrown property. The range lists two numbers. The first is the weapon’s normal range in feet, and the second indicates the weapon’s long range. When attacking a target beyond normal range, you have disadvantage on the attack roll. You can’t attack a target beyond the weapon’s long range',
            'range_min' => 'Minimum range',
            'range_max' => 'Maximum range',
            'is_reach' => 'This weapon adds 5 feet to your reach when you attack with it, as well as when determining your reach for opportunity attacks with it',
            'is_special' => 'A weapon with the special property has unusual rules governing its use, explained in the weapon’s description',
            'is_thrown' => 'If a weapon has the thrown property, you can throw the weapon to make a ranged attack. If the weapon is a melee weapon, you use the same ability modifier for that attack roll and damage roll that you would use for a melee attack with the weapon. For example, if you throw a handaxe, you use your Strength, but if you throw a dagger, you can use either your Strength or your Dexterity, since the dagger has the finesse property',
            'is_two_handed' => 'This weapon requires two hands when you attack with it',
            'is_versatile' => 'This weapon can be used with one or two hands',
            'versatile_dice' => 'Roll dice to determine the damage when the weapon is used with two hands to make a melee attack',
        ];
    }

    /**
     * Gets query for [[Amunition]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAmunition() {
        return $this->hasOne(Item::class, ['id' => 'amunition_id']);
    }

    /**
     * Gets query for [[DamageType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDamageType() {
        return $this->hasOne(DamageType::class, ['id' => 'damage_type_id']);
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
     * Gets query for [[ShapeAttacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeAttacks() {
        return $this->hasMany(ShapeAttack::class, ['weapon_id' => 'item_id']);
    }
}
