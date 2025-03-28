<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "room_image".
 *
 * @property int $room_id Foreign key to "room" table
 * @property int $image_id Foreign key to "image" table
 * @property string $name Legend (wall, floor, map...)
 * @property string|null $compass_point Indicates the cardinal point of what is represented (N, NE, E, SE, S, SW or W)
 *
 * @property Image $image
 * @property Room $room
 */
class RoomImage extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'room_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['room_id', 'image_id', 'name'], 'required'],
            [['room_id', 'image_id'], 'integer'],
            [['compass_point'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['room_id', 'image_id'], 'unique', 'targetAttribute' => ['room_id', 'image_id']],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::class, 'targetAttribute' => ['room_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'room_id' => 'Foreign key to \"room\" table',
            'image_id' => 'Foreign key to \"image\" table',
            'name' => 'Legend (wall, floor, map...)',
            'compass_point' => 'Indicates the cardinal point of what is represented (N, NE, E, SE, S, SW or W)',
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
     * Gets query for [[Room]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoom() {
        return $this->hasOne(Room::class, ['id' => 'room_id']);
    }
}
