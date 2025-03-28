<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "grid".
 *
 * @property int $id Primary key
 * @property int $room_id Foreign key to "room" table
 * @property int $width Width of the grid (in tile)
 * @property int $length Lenght of the grid (in tile)
 * @property int $tile_size Side length of a square tile (ft.)
 * @property string $shape Grid shape. Can be "R" for rectangle or "C" for circle
 *
 * @property GridItem[] $gridItems
 * @property GridShape[] $gridShapes
 * @property Item[] $items
 * @property Room $room
 * @property Shape[] $shapes
 * @property Tile[] $tiles
 */
class Grid extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'grid';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['room_id', 'shape'], 'required'],
            [['room_id', 'width', 'length', 'tile_size'], 'integer'],
            [['shape'], 'string'],
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
            'width' => 'Width of the grid (in tile)',
            'length' => 'Lenght of the grid (in tile)',
            'tile_size' => 'Side length of a square tile (ft.)',
            'shape' => 'Grid shape. Can be \"R\" for rectangle or \"C\" for circle',
        ];
    }

    /**
     * Gets query for [[GridItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGridItems() {
        return $this->hasMany(GridItem::class, ['grid_id' => 'id']);
    }

    /**
     * Gets query for [[GridShapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGridShapes() {
        return $this->hasMany(GridShape::class, ['grid_id' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->via('gridItems');
    }

    /**
     * Gets query for [[Room]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoom() {
        return $this->hasOne(Room::class, ['id' => 'room_id']);
    }

    /**
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapes() {
        return $this->hasMany(Shape::class, ['id' => 'shape_id'])->via('gridShapes');
    }

    /**
     * Gets query for [[Tiles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTiles() {
        return $this->hasMany(Tile::class, ['grid_id' => 'id']);
    }
}
