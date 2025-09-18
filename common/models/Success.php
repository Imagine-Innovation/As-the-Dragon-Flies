<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "success".
 *
 * @property int $id Primary key
 * @property int $next_mission_id Foreign key to "mission" table
 * @property int $interaction_id Foreign key to "interaction" table
 * @property int $xp Gained Experience Points (XP)
 * @property int $gp Gained Gold Pieces (GP)
 *
 * @property Interaction $interaction
 * @property Mission $nextMission
 */
class Success extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'success';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['gp'], 'default', 'value' => 0],
            [['next_mission_id', 'interaction_id'], 'required'],
            [['next_mission_id', 'interaction_id', 'xp', 'gp'], 'integer'],
            [['interaction_id'], 'exist', 'skipOnError' => true, 'targetClass' => Interaction::class, 'targetAttribute' => ['interaction_id' => 'id']],
            [['next_mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['next_mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'next_mission_id' => 'Foreign key to \"mission\" table',
            'interaction_id' => 'Foreign key to \"interaction\" table',
            'xp' => 'Gained Experience Points (XP)',
            'gp' => 'Gained Gold Pieces (GP)',
        ];
    }

    /**
     * Gets query for [[Interaction]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInteraction() {
        return $this->hasOne(Interaction::class, ['id' => 'interaction_id']);
    }

    /**
     * Gets query for [[NextMission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNextMission() {
        return $this->hasOne(Mission::class, ['id' => 'next_mission_id']);
    }

}
