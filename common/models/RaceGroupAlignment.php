<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "race_group_alignment".
 *
 * @property int $race_group_id Foreign key to “race_group” table
 * @property int $alignment_id Foreign key to “alignment” table
 *
 * @property Alignment $alignment
 * @property RaceGroup $raceGroup
 */
class RaceGroupAlignment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'race_group_alignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['race_group_id', 'alignment_id'], 'required'],
            [['race_group_id', 'alignment_id'], 'integer'],
            [['race_group_id', 'alignment_id'], 'unique', 'targetAttribute' => ['race_group_id', 'alignment_id']],
            [
                ['race_group_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => RaceGroup::class,
                'targetAttribute' => ['race_group_id' => 'id'],
            ],
            [
                ['alignment_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Alignment::class,
                'targetAttribute' => ['alignment_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'race_group_id' => 'Foreign key to “race_group” table',
            'alignment_id' => 'Foreign key to “alignment” table',
        ];
    }

    /**
     * Gets query for [[Alignment]].
     *
     * @return \yii\db\ActiveQuery<Alignment>
     */
    public function getAlignment()
    {
        return $this->hasOne(Alignment::class, ['id' => 'alignment_id']);
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
