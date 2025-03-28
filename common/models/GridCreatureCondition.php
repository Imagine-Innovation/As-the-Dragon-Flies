<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "grid_creature_condition".
 *
 * @property int $shape_id Foreign key to "shape" table
 * @property int $grid_id Foreign key to "grid" table
 * @property int $condition_id Foreign key to "creature_condition" table
 *
 * @property CreatureCondition $condition
 * @property GridShape $grid
 */
class GridCreatureCondition extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'grid_creature_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['shape_id', 'grid_id', 'condition_id'], 'required'],
            [['shape_id', 'grid_id', 'condition_id'], 'integer'],
            [['shape_id', 'grid_id', 'condition_id'], 'unique', 'targetAttribute' => ['shape_id', 'grid_id', 'condition_id']],
            [['grid_id', 'shape_id'], 'exist', 'skipOnError' => true, 'targetClass' => GridShape::class, 'targetAttribute' => ['grid_id' => 'grid_id', 'shape_id' => 'shape_id']],
            [['condition_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreatureCondition::class, 'targetAttribute' => ['condition_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'shape_id' => 'Foreign key to \"shape\" table',
            'grid_id' => 'Foreign key to \"grid\" table',
            'condition_id' => 'Foreign key to \"creature_condition\" table',
        ];
    }

    /**
     * Gets query for [[Condition]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCondition() {
        return $this->hasOne(CreatureCondition::class, ['id' => 'condition_id']);
    }

    /**
     * Gets query for [[Grid]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrid() {
        return $this->hasOne(GridShape::class, ['grid_id' => 'grid_id', 'shape_id' => 'shape_id']);
    }
}
