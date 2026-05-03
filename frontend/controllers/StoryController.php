<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\AccessRightsManager;
use common\models\Story;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

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
                        'allow' => AccessRightsManager::isRouteAllowed($this),
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
        $stories = Story::find()
                ->where(['status' => AppStatus::PUBLISHED->value])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();

        return $this->render('index', [
                    'stories' => $stories,
        ]);
    }
}
