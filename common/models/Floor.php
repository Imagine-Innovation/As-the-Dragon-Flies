<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "floor".
 *
 * @property int $id Primary key
 * @property int|null $image_id Optional foreign key to "image" table
 * @property string $name Floor
 * @property string|null $description Short description
 *
 * @property Image $image
 * @property Room[] $rooms
 */
class Floor extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'floor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['image_id'], 'integer'],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'image_id' => 'Optional foreign key to \"image\" table',
            'name' => 'Floor',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage() {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

    /**
     * Gets query for [[Rooms]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRooms() {
        return $this->hasMany(Room::class, ['floor_id' => 'id']);
    }
}
