<?php

namespace frontend\controllers;

use common\components\AccessRightsManager;
use common\components\ContextManager;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class UserController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        /** @phpstan-ignore-next-line */
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['*'],
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['profile'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return AccessRightsManager::isRouteAllowed($action->controller);
                        },
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function actionProfile()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $request = Yii::$app->request;
        if ($request->isPost) {
            $post = $request->post('User');
            $user->fullname = $post['fullname'] ?? $user->fullname;
            $user->language = $post['language'] ?? $user->language;

            if ($user->save()) {
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
