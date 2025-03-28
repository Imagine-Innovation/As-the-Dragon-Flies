<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "grid_shape".
 *
 * @property int $grid_id Foreign key to "grid" table
 * @property int $shape_id Foreign key to "shape" table
 * @property int $tile_id Foreign key to "tile" table to get the monster's position on the grid
 * @property string $description Short description of what the player can see
 * @property int $present Indicates the probability that the monster is present in the room on the grid
 * @property int $seen Gives the percentage chance that the player will see the monster
 * @property int $identified Gives the percentage chance that the player will recognize the monster
 * @property int|null $hp Actual hit points
 *
 * @property CreatureCondition[] $conditions
 * @property Grid $grid
 * @property GridCreatureCondition[] $gridCreatureConditions
 * @property Shape $shape
 * @property Tile $tile
 */
class GridShape extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'grid_shape';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['grid_id', 'shape_id', 'tile_id', 'description', 'identified'], 'required'],
            [['grid_id', 'shape_id', 'tile_id', 'present', 'seen', 'identified', 'hp'], 'integer'],
            [['description'], 'string'],
            [['grid_id', 'shape_id'], 'unique', 'targetAttribute' => ['grid_id', 'shape_id']],
            [['grid_id'], 'exist', 'skipOnError' => true, 'targetClass' => Grid::class, 'targetAttribute' => ['grid_id' => 'id']],
            [['tile_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tile::class, 'targetAttribute' => ['tile_id' => 'id']],
            [['shape_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shape::class, 'targetAttribute' => ['shape_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'grid_id' => 'Foreign key to \"grid\" table',
            'shape_id' => 'Foreign key to \"shape\" table',
            'tile_id' => 'Foreign key to \"tile\" table to get the monster\'s position on the grid',
            'description' => 'Short description of what the player can see',
            'present' => 'Indicates the probability that the monster is present in the room on the grid',
            'seen' => 'Gives the percentage chance that the player will see the monster',
            'identified' => 'Gives the percentage chance that the player will recognize the monster',
            'hp' => 'Actual hit points',
        ];
    }

    /**
     * Gets query for [[Conditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConditions() {
        return $this->hasMany(CreatureCondition::class, ['id' => 'condition_id'])->via('gridCreatureConditions');
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
     * Gets query for [[GridCreatureConditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGridCreatureConditions() {
        return $this->hasMany(GridCreatureCondition::class, ['grid_id' => 'grid_id', 'shape_id' => 'shape_id']);
    }

    /**
     * Gets query for [[Shape]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShape() {
        return $this->hasOne(Shape::class, ['id' => 'shape_id']);
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
