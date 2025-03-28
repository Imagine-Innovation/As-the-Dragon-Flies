<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "websocket_connection".
 *
 * @property int $id
 * @property int $user_id
 * @property string $connection_id
 * @property int $created_at
 * @property int|null $last_ping_at
 *
 * @property User $user
 */
class WebsocketConnection extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'websocket_connection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['user_id', 'connection_id'], 'required'],
            [['user_id', 'created_at', 'last_ping_at'], 'integer'],
            [['connection_id'], 'string', 'max' => 255],
            [['user_id', 'connection_id'], 'unique', 'targetAttribute' => ['user_id', 'connection_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'connection_id' => 'Connection ID',
            'created_at' => 'Created At',
            'last_ping_at' => 'Last Ping At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
