<?php

namespace common\models;

/**
 * This is the model class for table "race".
 *
 * @property int $id Primary key
 * @property int $race_group_id Foreign key to "race_group" table
 * @property string $name Race (e.g., "Human," "Elf," "Dwarf").
 * @property string|null $description Short description of the main traits of the race
 * @property int $adult_age The age threshold to separate childhood age and adult age
 * @property int $lifespan The average lifespan for the race
 * @property string $size Size code of the race. M=Medium, S=Small
 * @property string|null $base_height Average standard height
 * @property string|null $height_modifier Size + or - modifier
 * @property string|null $base_weight Average standard weight
 * @property string|null $weight_modifier Weight + or - modifier
 * @property int $speed The base movement speed of the race in feet (e.g., 30 feet).
 * @property int $darkvision Distance a character of this race can see in the dark (ft.)
 *
 * @property Ability[] $abilities
 * @property Player[] $players
 * @property RaceAbility[] $raceAbilities
 * @property RaceGroup $raceGroup
 * @property WizardAnswer[] $wizardAnswers
 * 
 * Custom Properties
 * 
 * @property string $randomImage
 */
class Race extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'race';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['race_group_id', 'name', 'size'], 'required'],
            [['race_group_id', 'adult_age', 'lifespan', 'speed', 'darkvision'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['size'], 'string', 'max' => 1],
            [['base_height', 'height_modifier', 'base_weight', 'weight_modifier'], 'string', 'max' => 8],
            [['name'], 'unique'],
            [['race_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => RaceGroup::class, 'targetAttribute' => ['race_group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'race_group_id' => 'Foreign key to \"race_group\" table',
            'name' => 'Race (e.g., \"Human,\" \"Elf,\" \"Dwarf\").',
            'description' => 'Short description of the main traits of the race',
            'adult_age' => 'The age threshold to separate childhood age and adult age',
            'lifespan' => 'The average lifespan for the race',
            'size' => 'Size code of the race. M=Medium, S=Small',
            'base_height' => 'Average standard height',
            'height_modifier' => 'Size + or - modifier',
            'base_weight' => 'Average standard weight',
            'weight_modifier' => 'Weight + or - modifier',
            'speed' => 'The base movement speed of the race in feet (e.g., 30 feet).',
            'darkvision' => 'Distance a character of this race can see in the dark (ft.)',
        ];
    }

    /**
     * Gets query for [[Abilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAbilities() {
        return $this->hasMany(Ability::class, ['id' => 'ability_id'])->via('raceAbilities');
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['race_id' => 'id']);
    }

    /**
     * Gets query for [[RaceAbilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceAbilities() {
        return $this->hasMany(RaceAbility::class, ['race_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroup() {
        return $this->hasOne(RaceGroup::class, ['id' => 'race_group_id']);
    }

    /**
     * Gets query for [[WizardAnswers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWizardAnswers() {
        return $this->hasMany(WizardAnswer::class, ['race_id' => 'id']);
    }

    /**
     * *********** Custom Properties *************
     */

    /**
     * Gets query for [[$randomImage]].
     *
     * @return string
     */
    public function getRandomImage() {
        $raceGroup = $this->raceGroup;
        return $raceGroup->randomImage;
    }
}
