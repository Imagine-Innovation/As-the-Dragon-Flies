<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "room_attribute".
 *
 * @property int $id Primary key
 * @property int $room_id Foreign key to "room" table
 * @property string $name Attribute
 * @property string|null $description Short description
 *
 * @property Room $room
 */
class RoomAttribute extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'room_attribute';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['room_id', 'name'], 'required'],
            [['room_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::class, 'targetAttribute' => ['room_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'room_id' => 'Foreign key to \"room\" table',
            'name' => 'Attribute',
            'description' => 'Short description',
        ];
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
