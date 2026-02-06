<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_right".
 *
 * @property int $id Primary key
 * @property string $route Route
 * @property string $action Action
 * @property int $is_admin An admin can access
 * @property int $is_designer A designer can can access
 * @property int $is_player A player can access
 * @property int $has_player A selected player can access
 * @property int $in_quest A player involved in a quest can access
 *
 * @property AccessRightActionButton[] $accessRightActionButtons
 * @property Menu $menu
 * @property UserLog[] $userLogs
 */
class AccessRight extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_right';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['route', 'action', 'is_admin', 'is_designer', 'is_player', 'has_player', 'in_quest'], 'required'],
            [['is_admin', 'is_designer', 'is_player', 'has_player', 'in_quest'], 'integer'],
            [['route', 'action'], 'string', 'max' => 64],
            [['route', 'action'], 'unique', 'targetAttribute' => ['route', 'action']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'route' => 'Route',
            'action' => 'Action',
            'is_admin' => 'An admin can access',
            'is_designer' => 'A designer can can access',
            'is_player' => 'A player can access',
            'has_player' => 'A selected player can access',
            'in_quest' => 'A player involved in a quest can access',
        ];
    }

    /**
     * Gets query for [[AccessRightActionButtons]].
     *
     * @return \yii\db\ActiveQuery<AccessRightActionButton>
     */
    public function getAccessRightActionButtons()
    {
        return $this->hasMany(AccessRightActionButton::class, ['access_right_id' => 'id']);
    }

    /**
     * Gets query for [[Menu]].
     *
     * @return \yii\db\ActiveQuery<Menu>
     */
    public function getMenu()
    {
        return $this->hasOne(Menu::class, ['access_right_id' => 'id']);
    }

    /**
     * Gets query for [[UserLogs]].
     *
     * @return \yii\db\ActiveQuery<UserLog>
     */
    public function getUserLogs()
    {
        return $this->hasMany(UserLog::class, ['access_right_id' => 'id']);
    }
}
