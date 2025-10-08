<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "movement".
 *
 * @property int $id Primary key
 * @property string $name Movement
 * @property string|null $description Short description
 *
 * @property ShapeMovement[] $shapeMovements
 * @property Shape[] $shapes
 */
class Movement extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'movement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Movement',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[ShapeMovements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeMovements() {
        return $this->hasMany(ShapeMovement::class, ['movement_id' => 'id']);
    }

    /**
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapes() {
        return $this->hasMany(Shape::class, ['id' => 'shape_id'])->viaTable('shape_movement', ['movement_id' => 'id']);
    }

}
