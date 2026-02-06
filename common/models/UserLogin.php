<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_login".
 *
 * @property int $user_id Primary Key and Foreign Key to the [User] entity
 * @property string $application Application logged to
 * @property int $login_at Login at
 * @property int|null $logout_at Logout at
 * @property string|null $ip_address IP address
 *
 * @property User $user
 */
class UserLogin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_login';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['logout_at', 'ip_address'], 'default', 'value' => null],
            [['user_id', 'application', 'login_at'], 'required'],
            [['user_id', 'login_at', 'logout_at'], 'integer'],
            [['application'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 64],
            [
                ['user_id', 'application', 'login_at'],
                'unique',
                'targetAttribute' => ['user_id', 'application', 'login_at'],
            ],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Primary Key and Foreign Key to the [User] entity',
            'application' => 'Application logged to',
            'login_at' => 'Login at',
            'logout_at' => 'Logout at',
            'ip_address' => 'IP address',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery<User>
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
