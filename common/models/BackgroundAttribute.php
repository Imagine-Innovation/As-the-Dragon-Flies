<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "background_attribute".
 *
 * @property int $id Primary key
 * @property int $background_id Foreign key to “background” table
 * @property string $attribute_type Attribute type
 * @property string|null $name Attribute
 * @property string|null $description Short description
 *
 * @property Background $background
 */
class BackgroundAttribute extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'background_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'description'], 'default', 'value' => null],
            [['background_id', 'attribute_type'], 'required'],
            [['background_id'], 'integer'],
            [['description'], 'string'],
            [['attribute_type', 'name'], 'string', 'max' => 32],
            [['background_id'], 'exist', 'skipOnError' => true, 'targetClass' => Background::class, 'targetAttribute' => ['background_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'background_id' => 'Foreign key to “background” table',
            'attribute_type' => 'Attribute type',
            'name' => 'Attribute',
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

}
