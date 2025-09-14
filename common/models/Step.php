<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "step".
 *
 * @property int $id Primary key
 * @property int $chapter_id
 * @property int $lighting_id
 * @property string $name Step
 * @property string|null $description Short description
 * @property int $width Width of the space representing the step to be completed (ft.)
 * @property int $length Length of the space representing the step to be completed (ft.)
 * @property int $height Height of the space representing the step to be completed (ft.)
 *
 * @property Chapter $chapter
 * @property Lighting $lighting
 * @property Passage[] $passages
 * @property Passage[] $passages0
 * @property StepImage $stepImage
 * @property StoryPlot[] $storyPlots
 * @property Trap[] $traps
 */
class Step extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'step';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['length'], 'default', 'value' => 30],
            [['height'], 'default', 'value' => 10],
            [['chapter_id', 'lighting_id', 'name'], 'required'],
            [['chapter_id', 'lighting_id', 'width', 'length', 'height'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['lighting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lighting::class, 'targetAttribute' => ['lighting_id' => 'id']],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chapter::class, 'targetAttribute' => ['chapter_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'chapter_id' => 'Chapter ID',
            'lighting_id' => 'Lighting ID',
            'name' => 'Step',
            'description' => 'Short description',
            'width' => 'Width of the space representing the step to be completed (ft.)',
            'length' => 'Length of the space representing the step to be completed (ft.)',
            'height' => 'Height of the space representing the step to be completed (ft.)',
        ];
    }

    /**
     * Gets query for [[Chapter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapter() {
        return $this->hasOne(Chapter::class, ['id' => 'chapter_id']);
    }

    /**
     * Gets query for [[Lighting]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLighting() {
        return $this->hasOne(Lighting::class, ['id' => 'lighting_id']);
    }

    /**
     * Gets query for [[Passages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassages() {
        return $this->hasMany(Passage::class, ['from_step_id' => 'id']);
    }

    /**
     * Gets query for [[Passages0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassages0() {
        return $this->hasMany(Passage::class, ['to_step_id' => 'id']);
    }

    /**
     * Gets query for [[StepImage]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStepImage() {
        return $this->hasOne(StepImage::class, ['step_id' => 'id']);
    }

    /**
     * Gets query for [[StoryPlots]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStoryPlots() {
        return $this->hasMany(StoryPlot::class, ['step_id' => 'id']);
    }

    /**
     * Gets query for [[Traps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraps() {
        return $this->hasMany(Trap::class, ['step_id' => 'id']);
    }

}
