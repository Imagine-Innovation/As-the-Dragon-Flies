<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "shape_armor".
 *
 * @property int $shape_id Foreign key to “shape” table
 * @property int $armor_id Foreign key to “armor” table
 *
 * @property Armor $armor
 * @property Shape $shape
 */
class ShapeArmor extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'shape_armor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['shape_id', 'armor_id'], 'required'],
            [['shape_id', 'armor_id'], 'integer'],
            [['shape_id', 'armor_id'], 'unique', 'targetAttribute' => ['shape_id', 'armor_id']],
            [['shape_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shape::class, 'targetAttribute' => ['shape_id' => 'id']],
            [['armor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Armor::class, 'targetAttribute' => ['armor_id' => 'item_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'shape_id' => 'Foreign key to “shape” table',
            'armor_id' => 'Foreign key to “armor” table',
        ];
    }

    /**
     * Gets query for [[Armor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArmor() {
        return $this->hasOne(Armor::class, ['item_id' => 'armor_id']);
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
