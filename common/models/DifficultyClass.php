<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "difficulty_class".
 *
 * @property int $id Primary key
 * @property string $name Difficulty Class
 * @property int $dc_min Minimum score to succeed, from 5 for "Very easy" to 30 for "Nearly impossible"
 * @property int $dc_max Maximum score for the level of difficulty before accessing the next level
 */
class DifficultyClass extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'difficulty_class';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['dc_min', 'dc_max'], 'integer'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Difficulty Class',
            'dc_min' => 'Minimum score to succeed, from 5 for \"Very easy\" to 30 for \"Nearly impossible\"',
            'dc_max' => 'Maximum score for the level of difficulty before accessing the next level',
        ];
    }
}
