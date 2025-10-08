<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "spell".
 *
 * @property int $id Primary key
 * @property string $name Spell
 * @property string|null $description Detailed description of the spell's effects, including any damage, conditions, or other consequences.
 * @property int $spell_level Spell level (between 0 and 9)
 * @property int $school_id Foreign key to “spell_school” table
 * @property int $range_id Foreign key to “spell_range” table
 * @property int $casting_time_id Foreign key to “spell_casting_time” table
 * @property int $duration_id Foreign key to “spell_duration” table
 * @property int $is_ritual
 *
 * @property SpellCastingTime $castingTime
 * @property ClassSpell[] $classSpells
 * @property CharacterClass[] $classes
 * @property Component[] $components
 * @property DamageType[] $damageTypes
 * @property SpellDuration $duration
 * @property PlayerSpell[] $playerSpells
 * @property Player[] $players
 * @property SpellRange $range
 * @property SpellSchool $school
 * @property SpellComponent[] $spellComponents
 * @property SpellDamageType[] $spellDamageTypes
 * @property SpellDoc[] $spellDocs
 */
class Spell extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'spell';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['is_ritual'], 'default', 'value' => 0],
            [['name', 'school_id', 'range_id', 'casting_time_id', 'duration_id'], 'required'],
            [['description'], 'string'],
            [['spell_level', 'school_id', 'range_id', 'casting_time_id', 'duration_id', 'is_ritual'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
            [['school_id'], 'exist', 'skipOnError' => true, 'targetClass' => SpellSchool::class, 'targetAttribute' => ['school_id' => 'id']],
            [['range_id'], 'exist', 'skipOnError' => true, 'targetClass' => SpellRange::class, 'targetAttribute' => ['range_id' => 'id']],
            [['casting_time_id'], 'exist', 'skipOnError' => true, 'targetClass' => SpellCastingTime::class, 'targetAttribute' => ['casting_time_id' => 'id']],
            [['duration_id'], 'exist', 'skipOnError' => true, 'targetClass' => SpellDuration::class, 'targetAttribute' => ['duration_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Spell',
            'description' => 'Detailed description of the spell\'s effects, including any damage, conditions, or other consequences.',
            'spell_level' => 'Spell level (between 0 and 9)',
            'school_id' => 'Foreign key to “spell_school” table',
            'range_id' => 'Foreign key to “spell_range” table',
            'casting_time_id' => 'Foreign key to “spell_casting_time” table',
            'duration_id' => 'Foreign key to “spell_duration” table',
            'is_ritual' => 'Is Ritual',
        ];
    }

    /**
     * Gets query for [[CastingTime]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCastingTime() {
        return $this->hasOne(SpellCastingTime::class, ['id' => 'casting_time_id']);
    }

    /**
     * Gets query for [[ClassSpells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClassSpells() {
        return $this->hasMany(ClassSpell::class, ['spell_id' => 'id']);
    }

    /**
     * Gets query for [[Classes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClasses() {
        return $this->hasMany(CharacterClass::class, ['id' => 'class_id'])->viaTable('class_spell', ['spell_id' => 'id']);
    }

    /**
     * Gets query for [[Components]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComponents() {
        return $this->hasMany(Component::class, ['id' => 'component_id'])->viaTable('spell_component', ['spell_id' => 'id']);
    }

    /**
     * Gets query for [[DamageTypes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDamageTypes() {
        return $this->hasMany(DamageType::class, ['id' => 'damage_type_id'])->viaTable('spell_damage_type', ['spell_id' => 'id']);
    }

    /**
     * Gets query for [[Duration]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDuration() {
        return $this->hasOne(SpellDuration::class, ['id' => 'duration_id']);
    }

    /**
     * Gets query for [[PlayerSpells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerSpells() {
        return $this->hasMany(PlayerSpell::class, ['spell_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('player_spell', ['spell_id' => 'id']);
    }

    /**
     * Gets query for [[Range]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRange() {
        return $this->hasOne(SpellRange::class, ['id' => 'range_id']);
    }

    /**
     * Gets query for [[School]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSchool() {
        return $this->hasOne(SpellSchool::class, ['id' => 'school_id']);
    }

    /**
     * Gets query for [[SpellComponents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpellComponents() {
        return $this->hasMany(SpellComponent::class, ['spell_id' => 'id']);
    }

    /**
     * Gets query for [[SpellDamageTypes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpellDamageTypes() {
        return $this->hasMany(SpellDamageType::class, ['spell_id' => 'id']);
    }

    /**
     * Gets query for [[SpellDocs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpellDocs() {
        return $this->hasMany(SpellDoc::class, ['spell_id' => 'id']);
    }

}
