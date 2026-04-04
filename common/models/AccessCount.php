<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_count".
 *
 * @property string $application Calling application
 * @property string $route Controller route
 * @property string $action Controller action
 * @property int $calls Calls
 * @property int $last_call Last call
 */
class AccessCount extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'access_count';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['calls'], 'default', 'value' => 1],
            [['last_call'], 'default', 'value' => 0],
            [['application', 'route', 'action'], 'required'],
            [['calls', 'last_call'], 'integer'],
            [['application', 'route', 'action'], 'string', 'max' => 64],
            [['application', 'route', 'action'], 'unique', 'targetAttribute' => ['application', 'route', 'action']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'application' => 'Calling application',
            'route' => 'Controller route',
            'action' => 'Controller action',
            'calls' => 'Calls',
            'last_call' => 'Last call',
        ];
    }
}
