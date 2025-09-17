<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mission_shape".
 *
 * @property int $shape_id Foreign key to "shape" table
 * @property int $mission_id Foreign key to "mission" table
 * @property string|null $name Monster name
 * @property string|null $description Short description
 * @property int $found Percentage chance that the item will be found
 * @property int $identified Percentage chance that the item will be identified
 *
 * @property Mission $mission
 * @property Shape $shape
 */
class MissionShape extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'mission_shape';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'description'], 'default', 'value' => null],
            [['found'], 'default', 'value' => 25],
            [['identified'], 'default', 'value' => 50],
            [['shape_id', 'mission_id'], 'required'],
            [['shape_id', 'mission_id', 'found', 'identified'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['shape_id', 'mission_id'], 'unique', 'targetAttribute' => ['shape_id', 'mission_id']],
            [['shape_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shape::class, 'targetAttribute' => ['shape_id' => 'id']],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'shape_id' => 'Foreign key to \"shape\" table',
            'mission_id' => 'Foreign key to \"mission\" table',
            'name' => 'Monster name',
            'description' => 'Short description',
            'found' => 'Percentage chance that the item will be found',
            'identified' => 'Percentage chance that the item will be identified',
        ];
    }

    /**
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMission() {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

    /**
     * Gets query for [[Shape]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShape() {
        return $this->hasOne(Shape::class, ['id' => 'shape_id']);
    }

}
