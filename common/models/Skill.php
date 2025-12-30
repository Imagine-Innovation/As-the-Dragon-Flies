<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "skill".
 *
 * @property int $id Primary key
 * @property int $ability_id Foreign key to “ability” table
 * @property string $name Skill
 * @property string|null $description Short description
 *
 * @property Ability $ability
 * @property ActionTypeSkill[] $actionTypeSkills
 * @property ActionType[] $actionTypes
 * @property BackgroundSkill[] $backgroundSkills
 * @property Background[] $backgrounds
 * @property ClassSkill[] $classSkills
 * @property CharacterClass[] $classes
 * @property CreatureSkill[] $creatureSkills
 * @property Creature[] $creatures
 * @property PlayerSkill[] $playerSkills
 * @property Player[] $players
 */
class Skill extends \yii\db\ActiveRecord
{

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
            [['description'], 'default', 'value' => null],
            [['ability_id', 'name'], 'required'],
            [['ability_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 64],
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
            'ability_id' => 'Foreign key to “ability” table',
            'name' => 'Skill',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Ability]].
     *
     * @return \yii\db\ActiveQuery<Ability>
     */
    public function getAbility() {
        return $this->hasOne(Ability::class, ['id' => 'ability_id']);
    }

    /**
     * Gets query for [[ActionTypeSkills]].
     *
     * @return \yii\db\ActiveQuery<ActionTypeSkill>
     */
    public function getActionTypeSkills() {
        return $this->hasMany(ActionTypeSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[ActionTypes]].
     *
     * @return \yii\db\ActiveQuery<ActionType>
     */
    public function getActionTypes() {
        return $this->hasMany(ActionType::class, ['id' => 'action_type_id'])->viaTable('action_type_skill', ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[BackgroundSkills]].
     *
     * @return \yii\db\ActiveQuery<BackgroundSkill>
     */
    public function getBackgroundSkills() {
        return $this->hasMany(BackgroundSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Backgrounds]].
     *
     * @return \yii\db\ActiveQuery<Background>
     */
    public function getBackgrounds() {
        return $this->hasMany(Background::class, ['id' => 'background_id'])->viaTable('background_skill', ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[ClassSkills]].
     *
     * @return \yii\db\ActiveQuery<ClassSkill>
     */
    public function getClassSkills() {
        return $this->hasMany(ClassSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Classes]].
     *
     * @return \yii\db\ActiveQuery<CharacterClass>
     */
    public function getClasses() {
        return $this->hasMany(CharacterClass::class, ['id' => 'class_id'])->viaTable('class_skill', ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[CreatureSkills]].
     *
     * @return \yii\db\ActiveQuery<CreatureSkill>
     */
    public function getCreatureSkills() {
        return $this->hasMany(CreatureSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery<Creature>
     */
    public function getCreatures() {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->viaTable('creature_skill', ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerSkills]].
     *
     * @return \yii\db\ActiveQuery<PlayerSkill>
     */
    public function getPlayerSkills() {
        return $this->hasMany(PlayerSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('player_skill', ['skill_id' => 'id']);
    }
}
