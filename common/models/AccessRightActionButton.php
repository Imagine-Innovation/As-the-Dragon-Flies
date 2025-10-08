<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_right_action_button".
 *
 * @property int $access_right_id Foreign key to “access_right” table
 * @property int $action_button_id Foreign key to “action_button” table
 * @property int $status Status
 *
 * @property AccessRight $accessRight
 * @property ActionButton $actionButton
 */
class AccessRightActionButton extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'access_right_action_button';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['access_right_id', 'action_button_id', 'status'], 'required'],
            [['access_right_id', 'action_button_id', 'status'], 'integer'],
            [['access_right_id', 'action_button_id', 'status'], 'unique', 'targetAttribute' => ['access_right_id', 'action_button_id', 'status']],
            [['access_right_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccessRight::class, 'targetAttribute' => ['access_right_id' => 'id']],
            [['action_button_id'], 'exist', 'skipOnError' => true, 'targetClass' => ActionButton::class, 'targetAttribute' => ['action_button_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'access_right_id' => 'Foreign key to “access_right” table',
            'action_button_id' => 'Foreign key to “action_button” table',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[AccessRight]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessRight() {
        return $this->hasOne(AccessRight::class, ['id' => 'access_right_id']);
    }

    /**
     * Gets query for [[ActionButton]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActionButton() {
        return $this->hasOne(ActionButton::class, ['id' => 'action_button_id']);
    }

}
