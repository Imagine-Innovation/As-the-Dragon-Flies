<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "shape_language".
 *
 * @property int $shape_id Foreign key to “shape” table
 * @property int $language_id Foreign key to “language” table
 *
 * @property Language $language
 * @property Shape $shape
 */
class ShapeLanguage extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'shape_language';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['shape_id', 'language_id'], 'required'],
            [['shape_id', 'language_id'], 'integer'],
            [['shape_id', 'language_id'], 'unique', 'targetAttribute' => ['shape_id', 'language_id']],
            [['shape_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shape::class, 'targetAttribute' => ['shape_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'shape_id' => 'Foreign key to “shape” table',
            'language_id' => 'Foreign key to “language” table',
        ];
    }

    /**
     * Gets query for [[Language]].
     *
     * @return \yii\db\ActiveQuery<Language>
     */
    public function getLanguage() {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    /**
     * Gets query for [[Shape]].
     *
     * @return \yii\db\ActiveQuery<Shape>
     */
    public function getShape() {
        return $this->hasOne(Shape::class, ['id' => 'shape_id']);
    }
}
