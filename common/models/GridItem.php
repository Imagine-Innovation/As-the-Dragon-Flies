<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "grid_item".
 *
 * @property int $grid_id Foreign key to "grid" table
 * @property int $item_id Foreign key to "item" table
 * @property int $tile_id Foreign key to "tile" table to locate the item
 * @property string $description A short description of what the player sees
 * @property int $present The percentage chance that the item is present in the room
 * @property int $found The percentage chance that the item will be found
 * @property int $identified The percentage chance that the item will be recognized
 *
 * @property Grid $grid
 * @property Item $item
 * @property Tile $tile
 */
class GridItem extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'grid_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['grid_id', 'item_id', 'tile_id', 'description'], 'required'],
            [['grid_id', 'item_id', 'tile_id', 'present', 'found', 'identified'], 'integer'],
            [['description'], 'string'],
            [['grid_id', 'item_id'], 'unique', 'targetAttribute' => ['grid_id', 'item_id']],
            [['grid_id'], 'exist', 'skipOnError' => true, 'targetClass' => Grid::class, 'targetAttribute' => ['grid_id' => 'id']],
            [['tile_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tile::class, 'targetAttribute' => ['tile_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'grid_id' => 'Foreign key to \"grid\" table',
            'item_id' => 'Foreign key to \"item\" table',
            'tile_id' => 'Foreign key to \"tile\" table to locate the item',
            'description' => 'A short description of what the player sees',
            'present' => 'The percentage chance that the item is present in the room',
            'found' => 'The percentage chance that the item will be found',
            'identified' => 'The percentage chance that the item will be recognized',
        ];
    }

    /**
     * Gets query for [[Grid]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrid() {
        return $this->hasOne(Grid::class, ['id' => 'grid_id']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem() {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
    }

    /**
     * Gets query for [[Tile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTile() {
        return $this->hasOne(Tile::class, ['id' => 'tile_id']);
    }
}
