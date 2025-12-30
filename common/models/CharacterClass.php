<?php

namespace common\models;

/**
 * This is the model class for table "character_class".
 *
 * @property int $id Primary key
 * @property string $name Class
 * @property string|null $description Short description
 * @property string $hit_die Hit die
 * @property int $abilities Number of abilities
 * @property int $max_skills Number of skills for this class
 * @property int $tools Number of tools mastered
 * @property string $initial_funding_dice Initial funding dice
 * @property int $initial_funding_multiplier Initial funding multiplier
 * @property string $initial_funding_coin Initial funding coin
 *
 * @property Ability[] $abilities
 * @property AbilityDefault[] $abilityDefaults
 * @property ClassAbility[] $classAbilities
 * @property ClassEndowment[] $classEndowments
 * @property ClassFeature[] $classFeatures
 * @property ClassImage[] $classImages
 * @property ClassItemProficiency[] $classItemProficiencies
 * @property ClassProficiency[] $classProficiencies
 * @property ClassSkill[] $classSkills
 * @property ClassSpell[] $classSpells
 * @property Image[] $images
 * @property Player[] $players
 * @property Skill[] $skills
 * @property Spell[] $spells
 * @property Story[] $stories
 * @property StoryClass[] $storyClasses
 * @property WizardAnswer[] $wizardAnswers
 *
 * Custom properties
 *
 * @property string|null $randomImage
 */
class CharacterClass extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'character_class';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['tools'], 'default', 'value' => 0],
            [['initial_funding_multiplier'], 'default', 'value' => 1],
            [['name', 'hit_die', 'initial_funding_dice', 'initial_funding_coin'], 'required'],
            [['description'], 'string'],
            [['abilities', 'max_skills', 'tools', 'initial_funding_multiplier'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['hit_die', 'initial_funding_dice'], 'string', 'max' => 8],
            [['initial_funding_coin'], 'string', 'max' => 2],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Class',
            'description' => 'Short description',
            'hit_die' => 'Hit die',
            'abilities' => 'Number of abilities',
            'max_skills' => 'Number of skills for this class',
            'tools' => 'Number of tools mastered',
            'initial_funding_dice' => 'Initial funding dice',
            'initial_funding_multiplier' => 'Initial funding multiplier',
            'initial_funding_coin' => 'Initial funding coin',
        ];
    }

    /**
     * Gets query for [[Abilities]].
     *
     * @return \yii\db\ActiveQuery<Ability>
     */
    public function getAbilities() {
        return $this->hasMany(Ability::class, ['id' => 'ability_id'])->viaTable('class_ability', ['class_id' => 'id']);
    }

    /**
     * Gets query for [[AbilityDefaults]].
     *
     * @return \yii\db\ActiveQuery<AbilityDefault>
     */
    public function getAbilityDefaults() {
        return $this->hasMany(AbilityDefault::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[ClassAbilities]].
     *
     * @return \yii\db\ActiveQuery<ClassAbility>
     */
    public function getClassAbilities() {
        return $this->hasMany(ClassAbility::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[ClassEndowments]].
     *
     * @return \yii\db\ActiveQuery<ClassEndowment>
     */
    public function getClassEndowments() {
        return $this->hasMany(ClassEndowment::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[ClassFeatures]].
     *
     * @return \yii\db\ActiveQuery<ClassFeature>
     */
    public function getClassFeatures() {
        return $this->hasMany(ClassFeature::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[ClassImages]].
     *
     * @return \yii\db\ActiveQuery<ClassImage>
     */
    public function getClassImages() {
        return $this->hasMany(ClassImage::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[ClassItemProficiencies]].
     *
     * @return \yii\db\ActiveQuery<ClassItemProficiency>
     */
    public function getClassItemProficiencies() {
        return $this->hasMany(ClassItemProficiency::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[ClassProficiencies]].
     *
     * @return \yii\db\ActiveQuery<ClassProficiency>
     */
    public function getClassProficiencies() {
        return $this->hasMany(ClassProficiency::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[ClassSkills]].
     *
     * @return \yii\db\ActiveQuery<ClassSkill>
     */
    public function getClassSkills() {
        return $this->hasMany(ClassSkill::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[ClassSpells]].
     *
     * @return \yii\db\ActiveQuery<ClassSpell>
     */
    public function getClassSpells() {
        return $this->hasMany(ClassSpell::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[Images]].
     *
     * @return \yii\db\ActiveQuery<Image>
     */
    public function getImages() {
        return $this->hasMany(Image::class, ['id' => 'image_id'])->viaTable('class_image', ['class_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery<Skill>
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['id' => 'skill_id'])->viaTable('class_skill', ['class_id' => 'id']);
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery<Spell>
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['id' => 'spell_id'])->viaTable('class_spell', ['class_id' => 'id']);
    }

    /**
     * Gets query for [[Stories]].
     *
     * @return \yii\db\ActiveQuery<Story>
     */
    public function getStories() {
        return $this->hasMany(Story::class, ['id' => 'story_id'])->viaTable('story_class', ['class_id' => 'id']);
    }

    /**
     * Gets query for [[StoryClasses]].
     *
     * @return \yii\db\ActiveQuery<StoryClass>
     */
    public function getStoryClasses() {
        return $this->hasMany(StoryClass::class, ['class_id' => 'id']);
    }

    /**
     * Gets query for [[WizardAnswers]].
     *
     * @return \yii\db\ActiveQuery<WizardAnswer>
     */
    public function getWizardAnswers() {
        return $this->hasMany(WizardAnswer::class, ['class_id' => 'id']);
    }

    /**
     * *********** Custom Properties *************
     */

    /**
     * Gets query for [[$randomImage]].
     *
     * @return string|null
     */
    public function getRandomImage(): ?string {
        $images = $this->images;
        if ($images) {
            $count = count($images);
            $image = $images[mt_rand(0, $count - 1)];
            return $image->file_name;
        }
        return null;
    }
}
