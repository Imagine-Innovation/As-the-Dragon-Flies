<?php

namespace frontend\models;

use common\components\AppStatus;
use common\models\User;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmailForm extends Model
{
    public string $token;

    /** @var User|null $_user */
    private ?User $_user;

    /**
     * Creates a form model with given token.
     *
     * @param string $token
     * @param array<string, mixed> $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct(string $token, array $config = [])
    {
        if (trim($token) === '') {
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }
        $this->_user = User::findByVerificationToken($token);
        if ($this->_user === null) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }
        parent::__construct($config);
    }

    /**
     * Verify email
     *
     * @return User|null the saved model or null if saving fails
     */
    public function verifyEmail(): ?User
    {
        $user = $this->_user;

        if ($user === null) {
            return null;
        }
        $user->status = AppStatus::ACTIVE->value;
        return $user->save(false) ? $user : null;
    }
}
