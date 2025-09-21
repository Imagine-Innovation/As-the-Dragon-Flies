<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "background_trait".
 *
 * @property int $background_id Foreign key to "background" table
 * @property int $trait_id Foreign key to "trait" table
 * @property int $score Value of the die roll used to determine the trait
 * @property string|null $description Short description
 *
 * @property Background $background
 * @property Trait $trait
 */
class BackgroundTrait extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'background_trait';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['background_id', 'trait_id', 'score'], 'required'],
            [['background_id', 'trait_id', 'score'], 'integer'],
            [['description'], 'string'],
            [['background_id', 'trait_id', 'score'], 'unique', 'targetAttribute' => ['background_id', 'trait_id', 'score']],
            [['background_id'], 'exist', 'skipOnError' => true, 'targetClass' => Background::class, 'targetAttribute' => ['background_id' => 'id']],
            [['trait_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterTrait::class, 'targetAttribute' => ['trait_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'background_id' => 'Foreign key to "background" table',
            'trait_id' => 'Foreign key to "trait" table',
            'score' => 'Value of the die roll used to determine the trait',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Background]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackground() {
        return $this->hasOne(Background::class, ['id' => 'background_id']);
    }

    /**
     * Gets query for [[Trait]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTrait() {
        return $this->hasOne(CharacterTrait::class, ['id' => 'trait_id']);
    }
}
