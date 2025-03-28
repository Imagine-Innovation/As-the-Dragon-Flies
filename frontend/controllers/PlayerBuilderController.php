<?php

namespace frontend\controllers;

use common\models\Player;
use common\models\PlayerItem;
use frontend\models\PlayerBuilder;
use common\models\PlayerSkill;
use common\models\ClassEquipment;
use common\models\Image;
use frontend\components\AjaxRequest;
use frontend\components\BuilderTool;
use common\components\ManageAccessRights;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

//use yii\behaviors\AttributeBehavior;
//use yii\db\ActiveRecord;

/**
 * PlayerController implements the CRUD actions for Player model.
 */
class PlayerBuilderController extends Controller {

    /**
     * @inheritDoc
     */
    public function behaviors() {
        return array_merge(
                parent::behaviors(),
                [
                    'access' => [
                        'class' => AccessControl::class,
                        'rules' => [
                            [
                                'actions' => ['*'],
                                'allow' => false,
                                'roles' => ['?'],
                            ],
                            [
                                'actions' => [
                                    'index', 'create', 'update', 'save-abilities', 'validate', 'restore',
                                    'ajax', 'ajax-admin', 'ajax-lite', 'ajax-age', 'ajax-names', 'ajax-images',
                                    'ajax-skills', 'ajax-traits', 'ajax-endowment', 'ajax-item-category', 'ajax-equipment',
                                    'ajax-save-equipment', 'ajax-save-skills', 'ajax-save-abilities',
                                    'ajax-set-context', 'view', 'delete', 'possessions', 'admin',
                                ],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                    'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                            'delete' => ['POST'],
                            'save-abilities' => ['POST'],
                            'validate' => ['POST'],
                        ],
                    ],
                ]
        );
    }

    public function actionAjaxAge() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $raceId = $request->post('raceId');
        $age = $request->post('age', 0);
        Yii::debug("*** Debug *** actionAjaxAge raceId=$raceId, age=$age", __METHOD__);

        $ageTable = BuilderTool::loadAgeTable($raceId);

        $content = $this->renderPartial('ajax-age', [
            'age' => $age,
            'ageTable' => $ageTable
        ]);

        return [
            'error' => false,
            'content' => $content,
            'ageTable' => json_encode($ageTable['labels']),
        ];
    }

    public function actionAjaxNames() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $raceId = $request->post('raceId');
        $gender = $request->post('gender', 'M');
        $n = $request->post('n', 3);

        return [
            'error' => false,
            'content' => $this->renderPartial('ajax-names', [
                'names' => BuilderTool::loadRandomNames($raceId, $gender, $n)
            ]),
        ];
    }

    public function actionAjaxImages() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $raceId = $request->post('raceId');
        $classId = $request->post('classId');
        $gender = $request->post('gender');

        if ($raceId && $classId && $gender) {
            $images = Image::find()
                    ->select('image.*')
                    ->innerJoin('class_image', 'image.id = class_image.image_id')
                    ->innerJoin('race_group_image', 'image.id = race_group_image.image_id')
                    ->innerJoin('race_group', 'race_group_image.race_group_id = race_group.id')
                    ->innerJoin('race', 'race_group.id = race.race_group_id')
                    ->andWhere(['class_image.class_id' => $classId])
                    ->andWhere(['race.id' => $raceId])
                    ->andWhere(['race_group_image.gender' => $gender])
                    ->all();

            return [
                'error' => false, 'msg' => '',
                'content' => $this->renderPartial('ajax-image', ['images' => $images])];
        }
        return ['error' => true, 'msg' => 'Missing argument:'
            . ($raceId ? '' : 'race') . ' '
            . ($classId ? '' : 'class') . ' '
            . ($gender ? '' : 'gender')];
    }

    public function actionAjaxSkills() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);
        return [
            'error' => false,
            'content' => $this->renderPartial('ajax-skills', [
                'player' => $player,
                'background_skills' => $player->background->skills,
                'class_skills' => $player->class->skills,
                'n' => $player->class->max_skills,
            ]),
        ];
    }

    public function actionAjaxTraits() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;

        $player = $this->findModel($request->post('playerId'));
        $success = $player->initTraits();

        if ($success) {
            return [
                'error' => false,
                'content' => $this->renderPartial('ajax-traits', [
                    'player' => $player,
                ]),
            ];
        }
        return ['error' => true, 'msg' => 'Unable to generate player traits and bounds'];
    }

    public function actionAjaxEndowment() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);
        $endowmentTable = $player->loadInitialEndowment();
        $choices = max(array_keys($endowmentTable));
        return [
            'error' => false,
            'content' => $this->renderPartial('ajax-endowment', [
                'player' => $player,
                'endowments' => $endowmentTable,
                'choices' => $choices,
            ]),
        ];
    }

    public function actionAjaxEquipment() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $choice = $request->post('choice');
        $endowmentId = $request->post('endowmentId');
        $equipments = ClassEquipment::findAll(['endowment_id' => $endowmentId]);
        $items = [];
        $categories = [];
        foreach ($equipments as $equipment) {
            if ($equipment->item_id) {
                Yii::debug("*** Debug *** actionAjaxGetEquipment equipment->item_id=$equipment->item_id");
                $items[] = $equipment->item_id . '|' . $equipment->quantity;
            }
            if ($equipment->category_id) {
                Yii::debug("*** Debug *** actionAjaxGetEquipment equipment->category_id=$equipment->category_id");
                $categories[] = $equipment->category_id . '|' . $equipment->quantity;
            }
        }
        return [
            'error' => false,
            'content' => ['choice' => $choice, 'items' => implode(',', $items), 'categories' => implode(',', $categories)],
        ];
    }

    private function getItemsFromJson($itemIds) {
        $items = [];
        foreach ($itemIds as $itemId) {
            $selections = explode(',', $itemId);
            foreach ($selections as $selection) {
                $data = explode('|', $selection);
                $item['id'] = $data[0];
                $item['quantity'] = $data[1];
                $items[] = $item;
            }
        }
        return $items;
    }

    public function actionAjaxSaveEquipment() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $itemIds = $request->post('itemIds');

        // first of all, delete existing items.
        // As a side effect, we must assume that the player has no other items
        // purchased or collected during the setup process.
        PlayerItem::deleteAll(['player_id' => $playerId]);

        $items = $this->getItemsFromJson($itemIds);

        $success = true;
        foreach ($items as $item) {
            $playerItem = new PlayerItem([
                'player_id' => $playerId,
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'is_carrying' => 1,
                'is_equiped' => 1,
            ]);
            $save = $playerItem->save();
            $success = $success && $save;
        }

        return ['error' => !$success, 'msg' => $success ? 'Initial items are saved' : 'Could not save initial items'];
    }

    public function actionAjaxItemCategory() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        // First split by comma to get each pair
        $pairs = explode(',', $request->post('categoryIds'));
        $quantity = explode('|', $pairs[0])[1];
        // Extract first elements (ids) using array_map
        $categoryIds = array_map(function ($pair) {
            return explode('|', $pair)[0];
        }, $pairs);

        $param = [
            'modelName' => 'ItemCategory',
            'render' => 'ajax-item-category',
            'with' => ['item', 'image'],
            'param' => ['choice' => $request->post('choice'), 'alreadySelectedItems' => $request->post('alreadySelectedItems'), 'quantity' => $quantity],
            'filter' => ['category_id' => $categoryIds],
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse($request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxSaveSkills() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        // before anything else, delete the existing skills
        $n = PlayerSkill::find()->where(['player_id' => $playerId])->count();
        $success = $n > 0 ? PlayerSkill::deleteAll(['player_id' => $playerId]) : true;

        // get the list of skill ids from the ajax param "skills"
        $skills = $request->post('skills');
        Yii::debug($skills, 'actionAjaxSaveSkills', 'from JSON');
        foreach ($skills as $skillId) {
            $playerSkill = new PlayerSkill(['player_id' => $playerId, 'skill_id' => $skillId]);
            $save = $playerSkill->save();
            $success = $success && $save;
        }

        return ['error' => !$success, 'msg' => $success ? 'Skills are saved' : 'Could not save skills'];
    }

    public function actionAjaxSaveAbilities() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $model = $this->findModel($request->post('playerId'));
        $success = true;
        if ($model) {
            $abilities = $request->post('abilities');
            Yii::debug($abilities, 'arrays', 'Save abilities');
            foreach ($model->playerAbilities as $playerAbility) {
                $ability_id = $playerAbility->ability_id;
                $playerAbility->score = $abilities[$ability_id];
                if (!$playerAbility->validate()) {
                    Yii::$app->session->setFlash('error',);
                }
                $success = $success && $playerAbility->save();
            }
        }

        return ['error' => !$success, 'msg' => $success ? 'Abilities are saved' : 'Could not save abilities'];
    }

    /**
     * Creates a new Player model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new PlayerBuilder();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['update', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
            $model->user_id = Yii::$app->user->identity->id;
        }

        return $this->render('create', [
                    'model' => $model
        ]);
    }

    /**
     * Updates an existing Player model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->status === Player::STATUS_ACTIVE) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model
        ]);
    }

    /**
     * Displays a single ActionButton model.
     * @param int $id Primary key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Player model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Player the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {

        $query = PlayerBuilder::find()
                ->with(['race', 'class', 'background', 'history', 'playerAbilities', 'playerSkills', 'playerTraits'])
                ->where(['id' => $id]);

        $user = Yii::$app->user->identity;
        if (!$user->is_admin) {
            $query->andWhere(['user_id' => $user->id]);
        }

        if (($model = $query->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
