<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "race_group_image".
 *
 * @property int $race_group_id Foreign key to “race” table
 * @property int $image_id Foreign key to “image” table
 * @property string $gender Gender. Can be “M” for male or “F” for female
 *
 * @property Image $image
 * @property RaceGroup $raceGroup
 */
class RaceGroupImage extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'race_group_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['race_group_id', 'image_id', 'gender'], 'required'],
            [['race_group_id', 'image_id'], 'integer'],
            [['gender'], 'string', 'max' => 1],
            [['race_group_id', 'image_id'], 'unique', 'targetAttribute' => ['race_group_id', 'image_id']],
            [['race_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => RaceGroup::class, 'targetAttribute' => ['race_group_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'race_group_id' => 'Foreign key to “race” table',
            'image_id' => 'Foreign key to “image” table',
            'gender' => 'Gender. Can be “M” for male or “F” for female',
        ];
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery<Image>
     */
    public function getImage() {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

    /**
     * Gets query for [[RaceGroup]].
     *
     * @return \yii\db\ActiveQuery<RaceGroup>
     */
    public function getRaceGroup() {
        return $this->hasOne(RaceGroup::class, ['id' => 'race_group_id']);
    }
}
