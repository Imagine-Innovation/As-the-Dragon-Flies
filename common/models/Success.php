<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "success".
 *
 * @property int $id Primary key
 * @property int $next_mission_id Foreign key to "mission" table
 * @property int $action_id Foreign key to "action" table
 * @property int|null $item_id Optional foreign key to "item" table
 * @property string $name Success
 * @property string|null $description Short description
 * @property int $gp Gained Gold Pieces (GP)
 * @property int $xp Gained Experience Points (XP)
 *
 * @property Dialog[] $dialogs
 * @property Action $action
 * @property Item $item
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
            [['item_id', 'description'], 'default', 'value' => null],
            [['xp'], 'default', 'value' => 0],
            [['next_mission_id', 'action_id', 'name'], 'required'],
            [['next_mission_id', 'action_id', 'item_id', 'gp', 'xp'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
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
            'next_mission_id' => 'Foreign key to "mission" table',
            'action_id' => 'Foreign key to "action" table',
            'item_id' => 'Optional foreign key to "item" table',
            'name' => 'Success',
            'description' => 'Short description',
            'gp' => 'Gained Gold Pieces (GP)',
            'xp' => 'Gained Experience Points (XP)',
        ];
    }

    /**
     * Gets query for [[Dialogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDialogs() {
        return $this->hasMany(Dialog::class, ['success_id' => 'id']);
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
