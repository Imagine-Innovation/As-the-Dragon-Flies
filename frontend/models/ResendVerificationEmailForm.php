<?php

namespace frontend\models;

use common\models\User;
use common\components\AppStatus;
use Yii;
use yii\base\Model;

class ResendVerificationEmailForm extends Model {

    /**
     * @var string
     */
    public $email;

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\common\models\User',
                'filter' => ['status' => AppStatus::INACTIVE->value],
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * Sends confirmation email to user
     *
     * @return bool whether the email was sent
     */
    public function sendEmail() {
        $user = User::findOne([
            'email' => $this->email,
            'status' => AppStatus::INACTIVE->value
        ]);

        if ($user === null) {
            return false;
        }

        return Yii::$app
                        ->mailer
                        ->compose(
                                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                                ['user' => $user]
                        )
                        ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                        ->setTo($this->email)
                        ->setSubject('Account registration at ' . Yii::$app->name)
                        ->send();
    }
}
