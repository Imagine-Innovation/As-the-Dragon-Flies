<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ethnicity".
 *
 * @property int $id Primary key
 * @property int $race_group_id Foreign key to “race_group” table
 * @property string $name Ethnicity
 *
 * @property FirstName[] $firstNames
 * @property LastName[] $lastNames
 * @property RaceGroup $raceGroup
 */
class Ethnicity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ethnicity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['race_group_id', 'name'], 'required'],
            [['race_group_id'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [
                ['race_group_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => RaceGroup::class,
                'targetAttribute' => ['race_group_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'race_group_id' => 'Foreign key to “race_group” table',
            'name' => 'Ethnicity',
        ];
    }

    /**
     * Gets query for [[FirstNames]].
     *
     * @return \yii\db\ActiveQuery<FirstName>
     */
    public function getFirstNames()
    {
        return $this->hasMany(FirstName::class, ['ethnicity_id' => 'id']);
    }

    /**
     * Gets query for [[LastNames]].
     *
     * @return \yii\db\ActiveQuery<LastName>
     */
    public function getLastNames()
    {
        return $this->hasMany(LastName::class, ['ethnicity_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroup]].
     *
     * @return \yii\db\ActiveQuery<RaceGroup>
     */
    public function getRaceGroup()
    {
        return $this->hasOne(RaceGroup::class, ['id' => 'race_group_id']);
    }
}
