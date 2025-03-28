<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "skill".
 *
 * @property int $id Primary key
 * @property int $ability_id Foreign key to "ability" table
 * @property string $name Skill
 * @property string|null $description Short description
 *
 * @property Ability $ability
 * @property BackgroundSkill[] $backgroundSkills
 * @property Background[] $backgrounds
 * @property ClassSkill[] $classSkills
 * @property Class[] $classes
 * @property CreatureSkill[] $creatureSkills
 * @property Creature[] $creatures
 * @property PassageStatusSkill[] $passageStatusSkills
 * @property PlayerSkill[] $playerSkills
 * @property Player[] $players
 * @property PassageStatus[] $statuses
 */
class Skill extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'skill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['ability_id', 'name'], 'required'],
            [['ability_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
            [['ability_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ability::class, 'targetAttribute' => ['ability_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'ability_id' => 'Foreign key to \"ability\" table',
            'name' => 'Skill',
            'description' => 'Short description',
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
     * Gets query for [[BackgroundSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackgroundSkills() {
        return $this->hasMany(BackgroundSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Backgrounds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackgrounds() {
        return $this->hasMany(Background::class, ['id' => 'background_id'])->via('backgroundSkills');
    }

    /**
     * Gets query for [[ClassSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClassSkills() {
        return $this->hasMany(ClassSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Classes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClasses() {
        return $this->hasMany(CharacterClass::class, ['id' => 'class_id'])->via('classSkills');
    }

    /**
     * Gets query for [[CreatureSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureSkills() {
        return $this->hasMany(CreatureSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatures() {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->via('creatureSkills');
    }

    /**
     * Gets query for [[PassageStatusSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassageStatusSkills() {
        return $this->hasMany(PassageStatusSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerSkills() {
        return $this->hasMany(PlayerSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('playerSkills');
    }

    /**
     * Gets query for [[Statuses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatuses() {
        return $this->hasMany(PassageStatus::class, ['id' => 'status_id'])->via('passageStatusSkills');
    }
}
