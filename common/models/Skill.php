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
 * @property CharacterClass[] $classes
 * @property CreatureSkill[] $creatureSkills
 * @property Creature[] $creatures
 * @property PassageSkill[] $passageSkills
 * @property Passage[] $passages
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
        return $this->hasMany(Background::class, ['id' => 'background_id'])->viaTable('background_skill', ['skill_id' => 'id']);
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
        return $this->hasMany(CharacterClass::class, ['id' => 'class_id'])->viaTable('class_skill', ['skill_id' => 'id']);
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
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->viaTable('creature_skill', ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[PassageSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassageSkills() {
        return $this->hasMany(PassageSkill::class, ['skill_id' => 'id']);
    }

    /**
     * Gets query for [[Passages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassages() {
        return $this->hasMany(Passage::class, ['id' => 'passage_id'])->viaTable('passage_skill', ['skill_id' => 'id']);
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
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('player_skill', ['skill_id' => 'id']);
    }
}
