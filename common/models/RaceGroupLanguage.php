<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "race_group_language".
 *
 * @property int $race_group_id Foreign key to “race_group” table
 * @property int $language_id Foreign key to “language” table
 *
 * @property Language $language
 * @property RaceGroup $raceGroup
 */
class RaceGroupLanguage extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'race_group_language';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['race_group_id', 'language_id'], 'required'],
            [['race_group_id', 'language_id'], 'integer'],
            [['race_group_id', 'language_id'], 'unique', 'targetAttribute' => ['race_group_id', 'language_id']],
            [['race_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => RaceGroup::class, 'targetAttribute' => ['race_group_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'race_group_id' => 'Foreign key to “race_group” table',
            'language_id' => 'Foreign key to “language” table',
        ];
    }

    /**
     * Gets query for [[Language]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage() {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    /**
     * Gets query for [[RaceGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroup() {
        return $this->hasOne(RaceGroup::class, ['id' => 'race_group_id']);
    }

}
