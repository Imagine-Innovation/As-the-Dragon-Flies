<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "spell_damage_type".
 *
 * @property int $spell_id Foreign key to "spell" table
 * @property int $damage_type_id Foreign key to "damage_type" table
 *
 * @property DamageType $damageType
 * @property Spell $spell
 */
class SpellDamageType extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'spell_damage_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['spell_id', 'damage_type_id'], 'required'],
            [['spell_id', 'damage_type_id'], 'integer'],
            [['spell_id', 'damage_type_id'], 'unique', 'targetAttribute' => ['spell_id', 'damage_type_id']],
            [['spell_id'], 'exist', 'skipOnError' => true, 'targetClass' => Spell::class, 'targetAttribute' => ['spell_id' => 'id']],
            [['damage_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DamageType::class, 'targetAttribute' => ['damage_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'spell_id' => 'Foreign key to "spell" table',
            'damage_type_id' => 'Foreign key to "damage_type" table',
        ];
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
     * Gets query for [[Spell]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpell() {
        return $this->hasOne(Spell::class, ['id' => 'spell_id']);
    }
}
