<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "monster".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to "mission" table
 * @property int $creature_id Foreign key to "creature" table
 * @property string $name Monster name
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property int $found Percentage chance that the item will be found
 * @property int $identified Percentage chance that the item will be identified
 *
 * @property Creature $creature
 * @property Mission $mission
 */
class Monster extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'monster';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['found'], 'default', 'value' => 25],
            [['identified'], 'default', 'value' => 50],
            [['mission_id', 'creature_id', 'name'], 'required'],
            [['mission_id', 'creature_id', 'found', 'identified'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 32],
            [['creature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Creature::class, 'targetAttribute' => ['creature_id' => 'id']],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to \"mission\" table',
            'creature_id' => 'Foreign key to \"creature\" table',
            'name' => 'Monster name',
            'description' => 'Short description',
            'image' => 'Image',
            'found' => 'Percentage chance that the monster will be found',
            'identified' => 'Percentage chance that the monster will be identified',
        ];
    }

    /**
     * Gets query for [[Creature]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreature() {
        return $this->hasOne(Creature::class, ['id' => 'creature_id']);
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
