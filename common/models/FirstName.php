<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "first_name".
 *
 * @property int $id Primary key
 * @property int $ethnicity_id Foreign key to “ethnicity” table
 * @property string $name First name
 * @property string $gender Gender
 *
 * @property Ethnicity $ethnicity
 */
class FirstName extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'first_name';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['ethnicity_id', 'name', 'gender'], 'required'],
            [['ethnicity_id'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['gender'], 'string', 'max' => 1],
            [['ethnicity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ethnicity::class, 'targetAttribute' => ['ethnicity_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'ethnicity_id' => 'Foreign key to “ethnicity” table',
            'name' => 'First name',
            'gender' => 'Gender',
        ];
    }

    /**
     * Gets query for [[Ethnicity]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEthnicity() {
        return $this->hasOne(Ethnicity::class, ['id' => 'ethnicity_id']);
    }

}
