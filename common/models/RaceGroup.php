<?php

namespace common\models;

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
 * @property string|null $randomImage
 */
class RaceGroup extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'race_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Race group',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[AbilityDefaults]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAbilityDefaults() {
        return $this->hasMany(AbilityDefault::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[Alignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlignments() {
        return $this->hasMany(Alignment::class, ['id' => 'alignment_id'])->viaTable('race_group_alignment', ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[Ethnicities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEthnicities() {
        return $this->hasMany(Ethnicity::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[Images]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImages() {
        return $this->hasMany(Image::class, ['id' => 'image_id'])->viaTable('race_group_image', ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[Languages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages() {
        return $this->hasMany(Language::class, ['id' => 'language_id'])->viaTable('race_group_language', ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroupAlignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroupAlignments() {
        return $this->hasMany(RaceGroupAlignment::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroupImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroupImages() {
        return $this->hasMany(RaceGroupImage::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroupLanguages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroupLanguages() {
        return $this->hasMany(RaceGroupLanguage::class, ['race_group_id' => 'id']);
    }

    /**
     * Gets query for [[Races]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaces() {
        return $this->hasMany(Race::class, ['race_group_id' => 'id']);
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
