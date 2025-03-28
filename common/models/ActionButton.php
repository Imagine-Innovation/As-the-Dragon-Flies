<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "action_button".
 *
 * @property int $id Primary key
 * @property string|null $route Route
 * @property string $action Action
 * @property string $icon Icon
 * @property string|null $tooltip Tooltip
 * @property int $in_table Displayed in a table
 * @property int $in_view Displayed in a view
 * @property int $sort_order Sort order
 *
 * @property AccessRightActionButton[] $accessRightActionButtons
 */
class ActionButton extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'action_button';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['action', 'icon'], 'required'],
            [['in_table', 'in_view', 'sort_order'], 'integer'],
            [['route', 'action', 'icon'], 'string', 'max' => 32],
            [['tooltip'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'route' => 'Route',
            'action' => 'Action',
            'icon' => 'Icon',
            'tooltip' => 'Tooltip',
            'in_table' => 'Displayed in a table',
            'in_view' => 'Displayed in a view',
            'sort_order' => 'Sort order',
        ];
    }

    /**
     * Gets query for [[AccessRightActionButtons]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessRightActionButtons() {
        return $this->hasMany(AccessRightActionButton::class, ['action_button_id' => 'id']);
    }
}
