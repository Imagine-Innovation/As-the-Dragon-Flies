<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public ?string $username = null;
    public ?string $fullname = null;
    public ?string $email = null;
    public ?string $password = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'fullname', 'email'], 'trim'],
            [['username', 'email', 'password'], 'required'],
            [
                'username',
                'unique',
                'targetClass' => '\common\models\User',
                'message' => 'This username has already been taken.',
            ],
            ['username', 'string', 'min' => 2, 'max' => 255],
            [
                'username',
                'match',
                'pattern' => '/^[a-zA-Z0-9]+$/',
                'message' => 'Username can only contain alphanumeric characters.',
            ],
            [
                'fullname',
                'unique',
                'targetClass' => '\common\models\User',
                'message' => 'This full name has already been taken.',
            ],
            ['fullname', 'string', 'max' => 64],
            [
                'fullname',
                'match',
                'pattern' => '/^[a-zA-Z0-9]+$/',
                'message' => 'Username can only contain alphanumeric characters.',
            ],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email',
                'unique',
                'targetClass' => '\common\models\User',
                'message' => 'This email address has already been taken.',
            ],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool|null whether the creating new account was successful and email was sent
     */
    public function signup(): ?bool
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        /** @phpstan-ignore-next-line */
        $user->username = $this->username;
        $user->fullname = $this->fullname;
        /** @phpstan-ignore-next-line */
        $user->email = $this->email;
        /** @phpstan-ignore-next-line */
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();

        $ret = $user->save() && $this->sendEmail($user);
        return $ret;
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail(User $user): bool
    {
        return Yii::$app
            ->mailer
            ->compose(['html' => 'emailVerify-html', 'text' => 'emailVerify-text'], ['user' => $user])
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
