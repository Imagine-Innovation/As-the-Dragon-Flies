<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "poison".
 *
 * @property int $item_id Foreign key to "item" table
 * @property int $damage_type_id Foreign key to "damage_type" table
 * @property int $ability_id Foreign key to "ability" table
 * @property string $poison_type Poison type
 * @property int $dc Difficulty Class (DC)
 *
 * @property Ability $ability
 * @property DamageType $damageType
 * @property Item $item
 */
class Poison extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'poison';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['item_id', 'damage_type_id', 'ability_id', 'poison_type'], 'required'],
            [['item_id', 'damage_type_id', 'ability_id', 'dc'], 'integer'],
            [['poison_type'], 'string', 'max' => 32],
            [['item_id'], 'unique'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['ability_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ability::class, 'targetAttribute' => ['ability_id' => 'id']],
            [['damage_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DamageType::class, 'targetAttribute' => ['damage_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'item_id' => 'Foreign key to \"item\" table',
            'damage_type_id' => 'Foreign key to \"damage_type\" table',
            'ability_id' => 'Foreign key to \"ability\" table',
            'poison_type' => 'Poison type',
            'dc' => 'Difficulty Class (DC)',
        ];
    }

    /**
     * Gets query for [[Ability]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAbility() {
        return $this->hasOne(Ability::class, ['id' => 'ability_id']);
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
}
