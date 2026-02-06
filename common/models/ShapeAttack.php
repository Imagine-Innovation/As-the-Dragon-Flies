<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "shape_attack".
 *
 * @property int $id Primary key
 * @property int $weapon_id Foreign key to “weapon” table
 * @property int $shape_id Foreign key to “shape” table
 * @property int $damage_type_id Foreign key to “damage_type” table
 * @property string $name Attack
 * @property string|null $description Short description
 * @property int $bonus Attack bonus
 * @property int $damage Average hit damage
 * @property string|null $damage_dice Damage dice when hit
 * @property string|null $additional_damage_dice Addtional hit dice when monster has 2 damage in one round
 * @property int|null $reach Maximum distance to reach a target with a melee weapon (ft.)
 * @property int|null $range_min Mininum distance to reach a target with a ranged weapon (ft.)
 * @property int|null $range_max Maximum distance to reach a target with a ranged weapon (ft.)
 *
 * @property DamageType $damageType
 * @property Shape $shape
 * @property Weapon $weapon
 */
class ShapeAttack extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shape_attack';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['description', 'damage_dice', 'additional_damage_dice', 'reach', 'range_min', 'range_max'],
                'default',
                'value' => null,
            ],
            [['damage'], 'default', 'value' => 0],
            [['id', 'weapon_id', 'shape_id', 'damage_type_id', 'name'], 'required'],
            [
                ['id', 'weapon_id', 'shape_id', 'damage_type_id', 'bonus', 'damage', 'reach', 'range_min', 'range_max'],
                'integer',
            ],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name'], 'string', 'max' => 64],
            [['damage_dice', 'additional_damage_dice'], 'string', 'max' => 8],
            [['id'], 'unique'],
            [
                ['shape_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Shape::class,
                'targetAttribute' => ['shape_id' => 'id'],
            ],
            [
                ['damage_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => DamageType::class,
                'targetAttribute' => ['damage_type_id' => 'id'],
            ],
            [
                ['weapon_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Weapon::class,
                'targetAttribute' => ['weapon_id' => 'item_id'],
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
            'weapon_id' => 'Foreign key to “weapon” table',
            'shape_id' => 'Foreign key to “shape” table',
            'damage_type_id' => 'Foreign key to “damage_type” table',
            'name' => 'Attack',
            'description' => 'Short description',
            'bonus' => 'Attack bonus',
            'damage' => 'Average hit damage',
            'damage_dice' => 'Damage dice when hit',
            'additional_damage_dice' => 'Addtional hit dice when monster has 2 damage in one round',
            'reach' => 'Maximum distance to reach a target with a melee weapon (ft.)',
            'range_min' => 'Mininum distance to reach a target with a ranged weapon (ft.)',
            'range_max' => 'Maximum distance to reach a target with a ranged weapon (ft.)',
        ];
    }

    /**
     * Gets query for [[DamageType]].
     *
     * @return \yii\db\ActiveQuery<DamageType>
     */
    public function getDamageType()
    {
        return $this->hasOne(DamageType::class, ['id' => 'damage_type_id']);
    }

    /**
     * Gets query for [[Shape]].
     *
     * @return \yii\db\ActiveQuery<Shape>
     */
    public function getShape()
    {
        return $this->hasOne(Shape::class, ['id' => 'shape_id']);
    }

    /**
     * Gets query for [[Weapon]].
     *
     * @return \yii\db\ActiveQuery<Weapon>
     */
    public function getWeapon()
    {
        return $this->hasOne(Weapon::class, ['item_id' => 'weapon_id']);
    }
}
