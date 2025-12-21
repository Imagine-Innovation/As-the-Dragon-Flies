<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "creature".
 *
 * @property int $id Primary key
 * @property int $type_id Foreign key to “creature_type” table
 * @property string $name Creature
 * @property string|null $description Short description
 * @property int $is_shapechanger Indicates that the creature can change shape
 * @property int|null $hp Hit Points: The average amount of damage a creature can withstand before being defeated.
 * @property string|null $hit_dice The dice roll that determines the damage inflicted by a creature.
 * @property float $cr Challenge Rating. This is a measure of how difficult the creature is to defeat for a party of adventurers.
 * @property int $bonus Proficiency bonus determined by the creature’s challenge rating.
 * @property int $xp Experience Points awarded to the party for defeating the creature.
 *
 * @property Ability[] $abilities
 * @property Ability[] $abilities0
 * @property Alignment[] $alignments
 * @property CreatureCondition[] $conditions
 * @property CreatureAbility[] $creatureAbilities
 * @property CreatureAlignment[] $creatureAlignments
 * @property CreatureDamageType[] $creatureDamageTypes
 * @property CreatureImmunization[] $creatureImmunizations
 * @property CreatureSavingThrow[] $creatureSavingThrows
 * @property CreatureSkill[] $creatureSkills
 * @property DamageType[] $damageTypes
 * @property Monster[] $monsters
 * @property Shape[] $shapes
 * @property Skill[] $skills
 * @property CreatureType $type
 */
class Creature extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'creature';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'hp', 'hit_dice'], 'default', 'value' => null],
            [['xp'], 'default', 'value' => 0],
            [['cr'], 'default', 'value' => 0.000],
            [['type_id', 'name'], 'required'],
            [['type_id', 'is_shapechanger', 'hp', 'bonus', 'xp'], 'integer'],
            [['description'], 'string'],
            [['cr'], 'number'],
            [['name'], 'string', 'max' => 64],
            [['hit_dice'], 'string', 'max' => 16],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreatureType::class, 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'type_id' => 'Foreign key to “creature_type” table',
            'name' => 'Creature',
            'description' => 'Short description',
            'is_shapechanger' => 'Indicates that the creature can change shape',
            'hp' => 'Hit Points: The average amount of damage a creature can withstand before being defeated.',
            'hit_dice' => 'The dice roll that determines the damage inflicted by a creature.',
            'cr' => 'Challenge Rating. This is a measure of how difficult the creature is to defeat for a party of adventurers.',
            'bonus' => 'Proficiency bonus determined by the creature’s challenge rating.',
            'xp' => 'Experience Points awarded to the party for defeating the creature.',
        ];
    }

    /**
     * Gets query for [[Abilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAbilities() {
        return $this->hasMany(Ability::class, ['id' => 'ability_id'])->viaTable('creature_ability', ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[Abilities0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAbilities0() {
        return $this->hasMany(Ability::class, ['id' => 'ability_id'])->viaTable('creature_saving_throw', ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[Alignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlignments() {
        return $this->hasMany(Alignment::class, ['id' => 'alignment_id'])->viaTable('creature_alignment', ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[CreatureAbilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureAbilities() {
        return $this->hasMany(CreatureAbility::class, ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[CreatureAlignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureAlignments() {
        return $this->hasMany(CreatureAlignment::class, ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[CreatureDamageTypes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureDamageTypes() {
        return $this->hasMany(CreatureDamageType::class, ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[CreatureImmunizations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureImmunizations() {
        return $this->hasMany(CreatureImmunization::class, ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[CreatureSavingThrows]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureSavingThrows() {
        return $this->hasMany(CreatureSavingThrow::class, ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[CreatureSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureSkills() {
        return $this->hasMany(CreatureSkill::class, ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[DamageTypes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDamageTypes() {
        return $this->hasMany(DamageType::class, ['id' => 'damage_type_id'])->viaTable('creature_damage_type', ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[Monsters]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMonsters() {
        return $this->hasMany(Monster::class, ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapes() {
        return $this->hasMany(Shape::class, ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['id' => 'skill_id'])->viaTable('creature_skill', ['creature_id' => 'id']);
    }

    /**
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType() {
        return $this->hasOne(CreatureType::class, ['id' => 'type_id']);
    }
}
