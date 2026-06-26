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
        $langFilter = Yii::$app->request->cookies->getValue('story-lang-filter', 'false') === 'true';

        $query = Story::find()
                ->where(['status' => AppStatus::PUBLISHED->value]);

        if ($langFilter) {
            $query->andWhere(['language' => Yii::$app->language]);
        }

        $stories = $query->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();

        return $this->render('index', [
                    'stories' => $stories,
                    'langFilter' => $langFilter,
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

        $filter = Yii::$app->request->post('filter');
        $value = $filter === 'true' ? 'true' : 'false';

        $cookie = new \yii\web\Cookie([
            'name' => 'story-lang-filter',
            'value' => $value,
            'expire' => time() + 86400 * 30, // 30 days
        ]);
        Yii::$app->response->cookies->add($cookie);

        return ['error' => false, 'msg' => "Filter successfully set to {$value}"];
    }
}
