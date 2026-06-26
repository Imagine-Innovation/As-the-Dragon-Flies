<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\AccessRightsManager;
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
     * @return string
     */
    public function actionIndex(): string
    {
        $lang = Yii::$app->request->get('lang');

        $query = Story::find()
                ->where(['status' => AppStatus::PUBLISHED->value]);

        if ($lang && array_key_exists($lang, \common\components\LanguageSelector::SUPPORTED_LANGUAGES)) {
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

        if ($lang && array_key_exists($lang, \common\components\LanguageSelector::SUPPORTED_LANGUAGES)) {
            $cookie = new \yii\web\Cookie([
                'name' => 'story-lang-filter',
                'value' => $lang,
                'expire' => time() + 86400 * 30, // 30 days
            ]);
            Yii::$app->response->cookies->add($cookie);
            $msg = "Filter successfully set to {$lang}";
        } else {
            Yii::$app->response->cookies->remove('story-lang-filter');
            $msg = "Filter successfully cleared";
        }

        return ['error' => false, 'msg' => $msg];
    }
}
