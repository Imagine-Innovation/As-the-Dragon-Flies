<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "last_name".
 *
 * @property int $id Primary key
 * @property int $ethnicity_id Foreign key to “ethnicity” table
 * @property string $name Last name
 *
 * @property Ethnicity $ethnicity
 */
class LastName extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'last_name';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ethnicity_id', 'name'], 'required'],
            [['ethnicity_id'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [
                ['ethnicity_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Ethnicity::class,
                'targetAttribute' => ['ethnicity_id' => 'id'],
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
            'ethnicity_id' => 'Foreign key to “ethnicity” table',
            'name' => 'Last name',
        ];
    }

    /**
     * Gets query for [[Ethnicity]].
     *
     * @return \yii\db\ActiveQuery<Ethnicity>
     */
    public function getEthnicity()
    {
        return $this->hasOne(Ethnicity::class, ['id' => 'ethnicity_id']);
    }
}
