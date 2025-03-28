<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tile".
 *
 * @property int $id Primary key
 * @property int $lighting_id Foreign key to "lighting" table
 * @property int $grid_id Foreign key to "grid" table
 * @property int $x Abscissa from left of screen
 * @property int $y Ordinate from top of screen
 * @property int $is_free Flag that indicates that the tile is free
 *
 * @property EntryPoint[] $entryPoints
 * @property Grid $grid
 * @property GridItem[] $gridItems
 * @property GridShape[] $gridShapes
 * @property Lighting $lighting
 * @property Passage[] $passages
 * @property Passage[] $passages0
 * @property Story[] $stories
 * @property Trap[] $traps
 */
class Tile extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'tile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['lighting_id', 'grid_id'], 'required'],
            [['lighting_id', 'grid_id', 'x', 'y', 'is_free'], 'integer'],
            [['grid_id'], 'exist', 'skipOnError' => true, 'targetClass' => Grid::class, 'targetAttribute' => ['grid_id' => 'id']],
            [['lighting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lighting::class, 'targetAttribute' => ['lighting_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'lighting_id' => 'Foreign key to \"lighting\" table',
            'grid_id' => 'Foreign key to \"grid\" table',
            'x' => 'Abscissa from left of screen',
            'y' => 'Ordinate from top of screen',
            'is_free' => 'Flag that indicates that the tile is free',
        ];
    }

    /**
     * Gets query for [[EntryPoints]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEntryPoints() {
        return $this->hasMany(EntryPoint::class, ['tile_id' => 'id']);
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
     * Gets query for [[GridItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGridItems() {
        return $this->hasMany(GridItem::class, ['tile_id' => 'id']);
    }

    /**
     * Gets query for [[GridShapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGridShapes() {
        return $this->hasMany(GridShape::class, ['tile_id' => 'id']);
    }

    /**
     * Gets query for [[Lighting]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLighting() {
        return $this->hasOne(Lighting::class, ['id' => 'lighting_id']);
    }

    /**
     * Gets query for [[Passages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassages() {
        return $this->hasMany(Passage::class, ['tile_to_id' => 'id']);
    }

    /**
     * Gets query for [[Passages0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassages0() {
        return $this->hasMany(Passage::class, ['tile_from_id' => 'id']);
    }

    /**
     * Gets query for [[Stories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStories() {
        return $this->hasMany(Story::class, ['id' => 'story_id'])->via('entryPoints');
    }

    /**
     * Gets query for [[Traps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraps() {
        return $this->hasMany(Trap::class, ['tile_id' => 'id']);
    }
}
