<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "race_group".
 *
 * @property int $id Primary key
 * @property string $name Race group
 * @property string|null $description Short description
 *
 * @property AbilityDefault[] $abilityDefaults
 * @property Alignment[] $alignments
 * @property Ethnicity[] $ethnicities
 * @property Image[] $images
 * @property Language[] $languages
 * @property RaceGroupAlignment[] $raceGroupAlignments
 * @property RaceGroupImage[] $raceGroupImages
 * @property RaceGroupLanguage[] $raceGroupLanguages
 * @property Race[] $races
 *
 * Custom Properties
 *
 * @property string $randomImage
 */
class RaceGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'race_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'name' => 'Race group',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[AbilityDefaults]].
     *
     * @return \yii\db\ActiveQuery<AbilityDefault>
     */
    public function getAbilityDefaults()
    {
        return $this->hasMany(AbilityDefault::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[Alignments]].
     *
     * @return \yii\db\ActiveQuery<Alignment>
     */
    public function getAlignments()
    {
        return $this->hasMany(Alignment::class, ['id' => 'alignment_id'])->viaTable('race_group_alignment', [
            'race_group_id' => 'id',
        ]);
    }

    /**
     * Gets query for [[Ethnicities]].
     *
     * @return \yii\db\ActiveQuery<Ethnicity>
     */
    public function getEthnicities()
    {
        return $this->hasMany(Ethnicity::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[Images]].
     *
     * @return \yii\db\ActiveQuery<Image>
     */
    public function getImages()
    {
        return $this->hasMany(Image::class, ['id' => 'image_id'])->viaTable('race_group_image', [
            'race_group_id' => 'id',
        ]);
    }

    /**
     * Gets query for [[Languages]].
     *
     * @return \yii\db\ActiveQuery<Language>
     */
    public function getLanguages()
    {
        return $this->hasMany(Language::class, ['id' => 'language_id'])->viaTable('race_group_language', [
            'race_group_id' => 'id',
        ]);
    }

    /**
     * Gets query for [[RaceGroupAlignments]].
     *
     * @return \yii\db\ActiveQuery<RaceGroupAlignment>
     */
    public function getRaceGroupAlignments()
    {
        return $this->hasMany(RaceGroupAlignment::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroupImages]].
     *
     * @return \yii\db\ActiveQuery<RaceGroupImage>
     */
    public function getRaceGroupImages()
    {
        return $this->hasMany(RaceGroupImage::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroupLanguages]].
     *
     * @return \yii\db\ActiveQuery<RaceGroupLanguage>
     */
    public function getRaceGroupLanguages()
    {
        return $this->hasMany(RaceGroupLanguage::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[Races]].
     *
     * @return \yii\db\ActiveQuery<Race>
     */
    public function getRaces()
    {
        return $this->hasMany(Race::class, ['race_group_id' => 'id']);
    }

    /**
     * *********** Custom Properties *************
     */

    /**
     * Gets query for [[$randomImage]].
     *
     * @return string
     */
    public function getRandomImage(): string
    {
        $images = $this->images;
        if ($images) {
            $count = count($images);
            $image = $images[mt_rand(0, $count - 1)];
            return $image->file_name;
        }
        return 'halfelf-male-13.png';
    }
}
