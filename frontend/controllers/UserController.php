<?php

namespace frontend\controllers;

use common\components\AccessRightsManager;
use common\components\ContextManager;
use common\components\LanguageSelector;
use common\helpers\SaveHelper;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

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
                'only' => ['profile', 'ajax-set-language'],
                'rules' => [
                    [
                        'actions' => ['ajax-set-language'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['profile', 'ajax-set-language'],
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

    /**
     * Sets the language for the current session via AJAX.
     *
     * @return array{error: bool, msg: string}
     */
    public function actionAjaxSetLanguage(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $lang = Yii::$app->request->post('lang');

        if (array_key_exists($lang, LanguageSelector::SUPPORTED_LANGUAGES)) {
            Yii::$app->session->set('language', $lang);

            $user = Yii::$app->user->identity;
            if ($user) {
                $user->language = $lang;
                SaveHelper::save($user);
            }
            return ['error' => false, 'msg' => "Language successfully set to {$lang}"];
        }

        return ['error' => true, 'msg' => 'Invalid language code'];
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
