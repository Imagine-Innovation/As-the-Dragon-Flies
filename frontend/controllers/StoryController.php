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
                        'actions' => ['index', 'ajax-set-lang-filter'],
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

        $query = Story::find()
                ->where(['status' => AppStatus::PUBLISHED->value]);

        if ($lang && array_key_exists($lang, LanguageSelector::SUPPORTED_LANGUAGES)) {
            $query->andWhere(['language' => $lang]);
        }

        $stories = $query->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();

        return $this->render('index', [
                    'stories' => $stories,
                    'langFilter' => $lang,
        ]);
    }

    /**
     * Sets the story language filter cookie via AJAX.
     *
     * @return array{error: bool, msg: string}
     */
    public function actionAjaxSetLangFilter(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $lang = Yii::$app->request->post('lang');

        if ($lang && array_key_exists($lang, LanguageSelector::SUPPORTED_LANGUAGES)) {
            Yii::$app->session->set('story-lang-filter', $lang);
            $msg = "Filter successfully set to {$lang}";
        } else {
            Yii::$app->session->remove('story-lang-filter');
            $msg = "Filter successfully cleared";
        }

        return ['error' => false, 'msg' => $msg];
    }
}
