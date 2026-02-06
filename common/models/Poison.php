<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "poison".
 *
 * @property int $item_id Foreign key to “item” table
 * @property int $damage_type_id Foreign key to “damage_type” table
 * @property int $ability_id Foreign key to “ability” table
 * @property string $poison_type Poison type
 * @property int $dc Difficulty Class (DC)
 *
 * @property Ability $ability
 * @property DamageType $damageType
 * @property Item $item
 */
class Poison extends Item
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'poison';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['item_id', 'damage_type_id', 'ability_id', 'poison_type'], 'required'],
            [['item_id', 'damage_type_id', 'ability_id', 'dc'], 'integer'],
            [['poison_type'], 'string', 'max' => 64],
            [['item_id'], 'unique'],
            [
                ['item_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Item::class,
                'targetAttribute' => ['item_id' => 'id'],
            ],
            [
                ['ability_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Ability::class,
                'targetAttribute' => ['ability_id' => 'id'],
            ],
            [
                ['damage_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => DamageType::class,
                'targetAttribute' => ['damage_type_id' => 'id'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'item_id' => 'Foreign key to “item” table',
            'damage_type_id' => 'Foreign key to “damage_type” table',
            'ability_id' => 'Foreign key to “ability” table',
            'poison_type' => 'Poison type',
            'dc' => 'Difficulty Class (DC)',
        ]);
    }

    /**
     * Gets query for [[Ability]].
     *
     * @return \yii\db\ActiveQuery<Ability>
     */
    public function getAbility()
    {
        return $this->hasOne(Ability::class, ['id' => 'ability_id']);
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
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
    }
}
