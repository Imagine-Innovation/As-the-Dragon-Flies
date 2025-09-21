<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mission_item".
 *
 * @property int $item_id Foreign key to "item" table
 * @property int $mission_id Foreign key to "mission" table
 * @property string $name Item name in the mission
 * @property string|null $description Short description
 * @property int $found The percentage chance that the item will be found
 * @property int $identified The percentage chance that the item will be recognized
 *
 * @property Item $item
 * @property Mission $mission
 */
class MissionItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'mission_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['found'], 'default', 'value' => 25],
            [['identified'], 'default', 'value' => 50],
            [['item_id', 'mission_id', 'name'], 'required'],
            [['item_id', 'mission_id', 'found', 'identified'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['item_id', 'mission_id'], 'unique', 'targetAttribute' => ['item_id', 'mission_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'item_id' => 'Foreign key to "item" table',
            'mission_id' => 'Foreign key to "mission" table',
            'name' => 'Item name in the mission',
            'description' => 'Short description',
            'found' => 'The percentage chance that the item will be found',
            'identified' => 'The percentage chance that the item will be recognized',
        ];
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
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMission() {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

}
