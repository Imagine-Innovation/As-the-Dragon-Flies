<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "damage_type".
 *
 * @property int $id Primary key
 * @property int $group_id Foreign key to "damage_group" table
 * @property string $name Damage type
 * @property string|null $description Short description
 *
 * @property CreatureDamageType[] $creatureDamageTypes
 * @property Creature[] $creatures
 * @property DamageGroup $group
 * @property Poison[] $poisons
 * @property ShapeAttack[] $shapeAttacks
 * @property SpellDamageType[] $spellDamageTypes
 * @property Spell[] $spells
 * @property Trap[] $traps
 * @property Weapon[] $weapons
 */
class DamageType extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'damage_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['group_id', 'name'], 'required'],
            [['group_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => DamageGroup::class, 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'group_id' => 'Foreign key to "damage_group" table',
            'name' => 'Damage type',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[CreatureDamageTypes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureDamageTypes() {
        return $this->hasMany(CreatureDamageType::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatures() {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->via('creatureDamageTypes');
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroup() {
        return $this->hasOne(DamageGroup::class, ['id' => 'group_id']);
    }

    /**
     * Gets query for [[Poisons]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPoisons() {
        return $this->hasMany(Poison::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[ShapeAttacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeAttacks() {
        return $this->hasMany(ShapeAttack::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[SpellDamageTypes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpellDamageTypes() {
        return $this->hasMany(SpellDamageType::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['id' => 'spell_id'])->via('spellDamageTypes');
    }

    /**
     * Gets query for [[Traps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraps() {
        return $this->hasMany(Trap::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[Weapons]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWeapons() {
        return $this->hasMany(Weapon::class, ['damage_type_id' => 'id']);
    }
}
