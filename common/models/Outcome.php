<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "outcome".
 *
 * @property int $id Primary key
 * @property int $action_id Foreign key to “action” table
 * @property int|null $next_mission_id Optional foreign key to “mission” table
 * @property int|null $item_id Optional foreign key to “item” table
 * @property int $status Outcome status: 2=success, 1=partial, 4=failure, 3=not failed, 5=not succeeded, 7=any status
 * @property string $name Outcome title
 * @property string|null $description Short description
 * @property int $gained_gp Gained Gold Pieces (GP) when succeeded
 * @property int $gained_xp Gained Experience Points (XP) when succeeded
 * @property int $hp_loss Lost HP when failed
 *
 * @property Action $action
 * @property Dialog[] $dialogs
 * @property Item $item
 * @property Mission $nextMission
 */
class Outcome extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'outcome';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['next_mission_id', 'item_id', 'description'], 'default', 'value' => null],
            [['hp_loss'], 'default', 'value' => 0],
            [['action_id', 'status', 'name'], 'required'],
            [['action_id', 'next_mission_id', 'item_id', 'status', 'gained_gp', 'gained_xp', 'hp_loss'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['action_id'], 'exist', 'skipOnError' => true, 'targetClass' => Action::class, 'targetAttribute' => ['action_id' => 'id']],
            [['next_mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['next_mission_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'action_id' => 'Foreign key to “action” table',
            'next_mission_id' => 'Optional foreign key to “mission” table',
            'item_id' => 'Optional foreign key to “item” table',
            'status' => 'Outcome status: 2=success, 1=partial, 4=failure, 3=not failed, 5=not succeeded, 7=any status',
            'name' => 'Outcome title',
            'description' => 'Short description',
            'gained_gp' => 'Gained Gold Pieces (GP) when succeeded',
            'gained_xp' => 'Gained Experience Points (XP) when succeeded',
            'hp_loss' => 'Lost HP when failed',
        ];
    }

    /**
     * Gets query for [[Action]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAction() {
        return $this->hasOne(Action::class, ['id' => 'action_id']);
    }

    /**
     * Gets query for [[Dialogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDialogs() {
        return $this->hasMany(Dialog::class, ['outcome_id' => 'id']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem() {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
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
