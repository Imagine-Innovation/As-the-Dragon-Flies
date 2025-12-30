<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "shape_movement".
 *
 * @property int $shape_id Foreign key to “shape” table
 * @property int $movement_id Foreign key to “movement” table
 * @property int $speed A creature’s speed tells you how far it can move on its turn
 * @property int $can_hover Indicates that the creature can hover in this shape
 *
 * @property Movement $movement
 * @property Shape $shape
 */
class ShapeMovement extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'shape_movement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['can_hover'], 'default', 'value' => 0],
            [['shape_id', 'movement_id'], 'required'],
            [['shape_id', 'movement_id', 'speed', 'can_hover'], 'integer'],
            [['shape_id', 'movement_id'], 'unique', 'targetAttribute' => ['shape_id', 'movement_id']],
            [['shape_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shape::class, 'targetAttribute' => ['shape_id' => 'id']],
            [['movement_id'], 'exist', 'skipOnError' => true, 'targetClass' => Movement::class, 'targetAttribute' => ['movement_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'shape_id' => 'Foreign key to “shape” table',
            'movement_id' => 'Foreign key to “movement” table',
            'speed' => 'A creature’s speed tells you how far it can move on its turn',
            'can_hover' => 'Indicates that the creature can hover in this shape',
        ];
    }

    /**
     * Gets query for [[Movement]].
     *
     * @return \yii\db\ActiveQuery<Movement>
     */
    public function getMovement() {
        return $this->hasOne(Movement::class, ['id' => 'movement_id']);
    }

    /**
     * Gets query for [[Shape]].
     *
     * @return \yii\db\ActiveQuery<Shape>
     */
    public function getShape() {
        return $this->hasOne(Shape::class, ['id' => 'shape_id']);
    }
}
