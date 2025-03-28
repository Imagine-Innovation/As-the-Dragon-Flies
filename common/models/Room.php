<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "room".
 *
 * @property int $id Primary key
 * @property int $floor_id Foreign key to "floor" table
 * @property string $name Room
 * @property string|null $description Short description
 * @property int $width Width of the room (ft.)
 * @property int $length Length of the room (ft.)
 * @property int $height Height of the room (ft.)
 * @property int $additional_creatures Number of creatures present in the room at random, in addition to those provided for in the story.
 * @property int $additional_items Number of items present in the room at random, in addition to those provided for in the story.
 *
 * @property Floor $floor
 * @property Grid[] $grs
 * @property Image[] $images
 * @property RoomAttribute[] $roomAttributes
 * @property RoomImage[] $roomImages
 */
class Room extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'room';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['floor_id', 'name'], 'required'],
            [['floor_id', 'width', 'length', 'height', 'additional_creatures', 'additional_items'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['floor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Floor::class, 'targetAttribute' => ['floor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'floor_id' => 'Foreign key to \"floor\" table',
            'name' => 'Room',
            'description' => 'Short description',
            'width' => 'Width of the room (ft.)',
            'length' => 'Length of the room (ft.)',
            'height' => 'Height of the room (ft.)',
            'additional_creatures' => 'Number of creatures present in the room at random, in addition to those provided for in the story.',
            'additional_items' => 'Number of items present in the room at random, in addition to those provided for in the story.',
        ];
    }

    /**
     * Gets query for [[Floor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFloor() {
        return $this->hasOne(Floor::class, ['id' => 'floor_id']);
    }

    /**
     * Gets query for [[Grs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrs() {
        return $this->hasMany(Grid::class, ['room_id' => 'id']);
    }

    /**
     * Gets query for [[Images]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImages() {
        return $this->hasMany(Image::class, ['id' => 'image_id'])->via('roomImages');
    }

    /**
     * Gets query for [[RoomAttributes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoomAttributes() {
        return $this->hasMany(RoomAttribute::class, ['room_id' => 'id']);
    }

    /**
     * Gets query for [[RoomImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoomImages() {
        return $this->hasMany(RoomImage::class, ['room_id' => 'id']);
    }
}
