<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_count".
 *
 * @property string $route Controller route
 * @property string $action Controller action
 * @property int $calls Calls
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
            [['route', 'action'], 'required'],
            [['calls'], 'integer'],
            [['route', 'action'], 'string', 'max' => 64],
            [['route', 'action'], 'unique', 'targetAttribute' => ['route', 'action']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'route' => 'Controller route',
            'action' => 'Controller action',
            'calls' => 'Calls',
        ];
    }

}
