<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "intro_attribute".
 *
 * @property int $id Primary key
 * @property int $intro_id Foreign key to "story_intro" table
 * @property string $name Introduction attribute
 * @property string|null $description Short description
 *
 * @property StoryIntro $intro
 */
class IntroAttribute extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'intro_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['intro_id', 'name'], 'required'],
            [['intro_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['intro_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoryIntro::class, 'targetAttribute' => ['intro_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'intro_id' => 'Foreign key to \"story_intro\" table',
            'name' => 'Introduction attribute',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Intro]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIntro() {
        return $this->hasOne(StoryIntro::class, ['id' => 'intro_id']);
    }
}
