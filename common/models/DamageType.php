<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "damage_type".
 *
 * @property int $id Primary key
 * @property int $group_id Foreign key to “damage_group” table
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
class DamageType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'damage_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['group_id', 'name'], 'required'],
            [['group_id'], 'integer'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name'], 'string', 'max' => 64],
            [
                ['group_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => DamageGroup::class,
                'targetAttribute' => ['group_id' => 'id'],
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
            'group_id' => 'Foreign key to “damage_group” table',
            'name' => 'Damage type',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[CreatureDamageTypes]].
     *
     * @return \yii\db\ActiveQuery<CreatureDamageType>
     */
    public function getCreatureDamageTypes()
    {
        return $this->hasMany(CreatureDamageType::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery<Creature>
     */
    public function getCreatures()
    {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->viaTable('creature_damage_type', [
            'damage_type_id' => 'id',
        ]);
    }

    /**
     * Gets query for [[Group]].
     *
     * @return \yii\db\ActiveQuery<DamageGroup>
     */
    public function getGroup()
    {
        return $this->hasOne(DamageGroup::class, ['id' => 'group_id']);
    }

    /**
     * Gets query for [[Poisons]].
     *
     * @return \yii\db\ActiveQuery<Poison>
     */
    public function getPoisons()
    {
        return $this->hasMany(Poison::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[ShapeAttacks]].
     *
     * @return \yii\db\ActiveQuery<ShapeAttack>
     */
    public function getShapeAttacks()
    {
        return $this->hasMany(ShapeAttack::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[SpellDamageTypes]].
     *
     * @return \yii\db\ActiveQuery<SpellDamageType>
     */
    public function getSpellDamageTypes()
    {
        return $this->hasMany(SpellDamageType::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery<Spell>
     */
    public function getSpells()
    {
        return $this->hasMany(Spell::class, ['id' => 'spell_id'])->viaTable('spell_damage_type', [
            'damage_type_id' => 'id',
        ]);
    }

    /**
     * Gets query for [[Traps]].
     *
     * @return \yii\db\ActiveQuery<Trap>
     */
    public function getTraps()
    {
        return $this->hasMany(Trap::class, ['damage_type_id' => 'id']);
    }

    /**
     * Gets query for [[Weapons]].
     *
     * @return \yii\db\ActiveQuery<Weapon>
     */
    public function getWeapons()
    {
        return $this->hasMany(Weapon::class, ['damage_type_id' => 'id']);
    }
}
