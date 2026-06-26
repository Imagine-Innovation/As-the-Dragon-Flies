<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\AccessRightsManager;
use common\components\LanguageSelector;
use common\models\Story;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * StoryController implements the CRUD actions for Story model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class StoryController extends Controller
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
                        'actions' => ['index'],
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
     * Lists all Story models.
     *
     * @return string|Response
     */
    public function actionIndex(): string|Response
    {
        $lang = Yii::$app->request->get('lang');

        if ($lang !== null) {
            if (array_key_exists($lang, LanguageSelector::SUPPORTED_LANGUAGES)) {
                Yii::$app->session->set('story-lang-filter', $lang);
            } else {
                Yii::$app->session->remove('story-lang-filter');
                return $this->redirect(['story/index']);
            }
        } else {
            $sessionLang = Yii::$app->session->get('story-lang-filter');
            if ($sessionLang && array_key_exists($sessionLang, LanguageSelector::SUPPORTED_LANGUAGES)) {
                return $this->redirect(['story/index', 'lang' => $sessionLang]);
            }
        }

        $query = Story::find()
                ->where(['status' => AppStatus::PUBLISHED->value]);

        if ($lang && array_key_exists($lang, LanguageSelector::SUPPORTED_LANGUAGES)) {
            $query->andWhere(['language' => $lang]);
        } else {
            $lang = null;
        }

        $stories = $query->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();

        return $this->render('index', [
                    'stories' => $stories,
                    'langFilter' => $lang,
        ]);
    }
}
