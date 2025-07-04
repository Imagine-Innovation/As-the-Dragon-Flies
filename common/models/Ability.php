<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ability".
 *
 * @property int $id Primary key
 * @property string $code Ability code (CHA, CON, DEX...)
 * @property string $name Ability
 * @property string|null $description Short description
 *
 * @property ClassAbility[] $classAbilities
 * @property Class[] $classes
 * @property CreatureAbility[] $creatureAbilities
 * @property CreatureSavingThrow[] $creatureSavingThrows
 * @property Creature[] $creatures
 * @property Creature[] $creatures0
 * @property PlayerAbility[] $playerAbilities
 * @property Player[] $players
 * @property Poison[] $poisons
 * @property RaceAbility[] $raceAbilities
 * @property Race[] $races
 * @property Skill[] $skills
 */
class Ability extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'ability';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['code', 'name'], 'required'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'code' => 'Ability code (CHA, CON, DEX...)',
            'name' => 'Ability',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[ClassAbilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClassAbilities() {
        return $this->hasMany(ClassAbility::class, ['ability_id' => 'id']);
    }

    /**
     * Gets query for [[Classes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClasses() {
        return $this->hasMany(CharacterClass ::class, ['id' => 'class_id'])->via('classAbilities');
    }

    /**
     * Gets query for [[CreatureAbilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureAbilities() {
        return $this->hasMany(CreatureAbility::class, ['ability_id' => 'id']);
    }

    /**
     * Gets query for [[CreatureSavingThrows]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureSavingThrows() {
        return $this->hasMany(CreatureSavingThrow::class, ['ability_id' => 'id']);
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatures() {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->via('creatureAbilities');
    }

    /**
     * Gets query for [[Creatures0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatures0() {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->via('creatureSavingThrows');
    }

    /**
     * Gets query for [[PlayerAbilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerAbilities() {
        return $this->hasMany(PlayerAbility::class, ['ability_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('playerAbilities');
    }

    /**
     * Gets query for [[Poisons]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPoisons() {
        return $this->hasMany(Poison::class, ['ability_id' => 'id']);
    }

    /**
     * Gets query for [[RaceAbilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceAbilities() {
        return $this->hasMany(RaceAbility::class, ['ability_id' => 'id']);
    }

    /**
     * Gets query for [[Races]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaces() {
        return $this->hasMany(Race::class, ['id' => 'race_id'])->via('raceAbilities');
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['ability_id' => 'id']);
    }
}
