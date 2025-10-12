<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "passage".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to “mission” table
 * @property string $name Passage
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property int $found Give the probability of finding the passage (%)
 *
 * @property Action[] $actions
 * @property Mission $mission
 */
class Passage extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'passage';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['found'], 'default', 'value' => 25],
            [['mission_id', 'name'], 'required'],
            [['mission_id', 'found'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 64],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to “mission” table',
            'name' => 'Passage',
            'description' => 'Short description',
            'image' => 'Image',
            'found' => 'Give the probability of finding the passage (%)',
        ];
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActions() {
        return $this->hasMany(Action::class, ['passage_id' => 'id']);
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
