<?php

namespace common\models;

use common\components\AppStatus;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id Primary key
 * @property string $username Username
 * @property string|null $fullname Fullname
 * @property string|null $auth_key Auth Key
 * @property string $password_hash Password hash
 * @property string|null $password_reset_token Password reset token
 * @property string $verification_token Verification token
 * @property string $email email
 * @property int $status Status
 * @property int $is_admin This flag indicates that the user can access the admin part of the application
 * @property int $is_designer This flag indicates that the user can access configuration functions of the application
 * @property int $is_player This flag indicates that the user can play the game
 * @property int|null $current_player_id Optional foreign key to "player" table
 * @property int|null $created_at Created at
 * @property int|null $updated_at Updated at
 * @property int|null $backend_last_login_at Last login to the backend at
 * @property int|null $frontend_last_login_at Last login to the frontend at
 * @property string $password write-only password
 *
 * @property AccessRight[] $accessRights
 * @property int $hasPlayers
 * @property Player[] $players
 * @property UserLogin[] $userLogins
 * @property UserLog[] $userLogs
 * @property WebsocketConnection[] $websocketConnections
 */
class User extends ActiveRecord implements IdentityInterface {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['username', 'password_hash', 'verification_token', 'email'], 'required'],
            [['status', 'is_admin', 'is_designer', 'is_player', 'current_player_id', 'created_at', 'updated_at', 'backend_last_login_at', 'frontend_last_login_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'verification_token', 'email'], 'string', 'max' => 255],
            [['fullname'], 'string', 'max' => 64],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['current_player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['current_player_id' => 'id']],
            ['status', 'default', 'value' => AppStatus::INACTIVE->value],
            ['status', 'in', 'range' => AppStatus::getValuesForUser()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'username' => 'Username',
            'fullname' => 'Fullname',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password hash',
            'password_reset_token' => 'Password reset token',
            'verification_token' => 'Verification token',
            'email' => 'email',
            'status' => 'Status',
            'is_admin' => 'This flag indicates that the user can access the admin part of the application',
            'is_designer' => 'This flag indicates that the user can access configuration functions of the application',
            'is_player' => 'This flag indicates that the user can play the game',
            'current_player_id' => 'Optional foreign key to \"player\" table',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
            'backend_last_login_at' => 'Last login to the backend at',
            'frontend_last_login_at' => 'Last login to the frontend at',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id) {
        return static::findOne(['id' => $id, 'status' => AppStatus::ACTIVE->value]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username) {
        return static::findOne(['username' => $username, 'status' => AppStatus::ACTIVE->value]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                    'password_reset_token' => $token,
                    'status' => AppStatus::ACTIVE->value,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
                    'verification_token' => $token,
                    'status' => AppStatus::INACTIVE->value
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token) {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken() {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }

    public static function getAppUser($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Gets query for [[AccessRights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessRights() {
        return $this->hasOne(AccessRight::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[CurrentPlayer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentPlayer() {
        return $this->hasOne(Player::class, ['id' => 'current_player_id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserLogs() {
        return $this->hasMany(UserLog::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserLogins]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserLogins() {
        return $this->hasMany(UserLogin::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[HasPlayers]].
     *
     * @return int
     */
    public function hasPlayers() {
        return $this->getPlayers()->count() > 0;
    }

    /**
     * Gets query for [[WebsocketConnections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWebsocketConnections() {
        return $this->hasMany(WebsocketConnection::class, ['user_id' => 'id']);
    }
}
