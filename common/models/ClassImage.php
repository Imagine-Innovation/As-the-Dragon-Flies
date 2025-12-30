<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_image".
 *
 * @property int $class_id Foreign key to “character_class” table
 * @property int $image_id Foreign key to “image” table
 *
 * @property CharacterClass $class
 * @property Image $image
 */
class ClassImage extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'class_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['class_id', 'image_id'], 'required'],
            [['class_id', 'image_id'], 'integer'],
            [['class_id', 'image_id'], 'unique', 'targetAttribute' => ['class_id', 'image_id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'class_id' => 'Foreign key to “character_class” table',
            'image_id' => 'Foreign key to “image” table',
        ];
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery<CharacterClass>
     */
    public function getClass() {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery<Image>
     */
    public function getImage() {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }
}
