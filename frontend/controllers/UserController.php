<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\User;
use common\components\ContextManager;

class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionProfile()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if ($this->request->isPost) {
            if ($user->load($this->request->post()) && $user->save()) {
                ContextManager::initContext($user);
                Yii::$app->session->setFlash('success', Yii::t('app', 'Profile updated successfully.'));
                return $this->refresh();
            }
        }

        return $this->render('profile', [
            'model' => $user,
        ]);
    }
}
