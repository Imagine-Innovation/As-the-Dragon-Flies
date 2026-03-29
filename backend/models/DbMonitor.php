<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "db_monitor".
 *
 * @property int $id Primary key
 * @property string $sql_text SQL statement
 * @property string $hash Hashed identifier of a SQL statement
 * @property int $avg_runtime_ms Average runtime (ms)
 * @property int $calls_last_hour Calls during the last hour
 * @property int $last_seen Last seen in pico seconds
 */
class DbMonitor extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'db_monitor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['sql_text', 'hash', 'avg_runtime_ms', 'calls_last_hour', 'last_seen'], 'required'],
            [['sql_text'], 'string'],
            [['avg_runtime_ms', 'calls_last_hour', 'last_seen'], 'integer'],
            [['hash'], 'string', 'max' => 64],
            [['hash'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'sql_text' => 'SQL statement',
            'hash' => 'Hashed identifier of a SQL statement',
            'avg_runtime_ms' => 'Average runtime (ms)',
            'calls_last_hour' => 'Calls during the last hour',
            'last_seen' => 'Last seen in pico seconds',
        ];
    }
}
