<?php

namespace frontend\controllers;

use common\models\ClassEquipment;
use common\models\Image;
use common\models\Player;
use common\models\PlayerItem;
use common\models\PlayerSkill;
use common\models\Weapon;
use common\components\AppStatus;
use common\components\ManageAccessRights;
use frontend\models\PlayerBuilder;
use frontend\components\AjaxRequest;
use frontend\components\BuilderTool;
use frontend\components\PlayerTool;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
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
                                    'create', 'update', 'view',
                                    'ajax-age', 'ajax-endowment', 'ajax-equipment', 'ajax-images',
                                    'ajax-item-category', 'ajax-names',
                                    'ajax-save-abilities', 'ajax-save-equipment', 'ajax-save-skills',
                                    'ajax-skills', 'ajax-traits',
                                ],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
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

    private function renderImages($imageId, $raceId, $classId, $gender) {
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
            'content' => $this->renderPartial('ajax-image', ['imageId' => $imageId, 'images' => $images])];
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
            return $this->renderImages($request->post('imageId'), $raceId, $classId, $gender);
        }
        return ['error' => true, 'msg' => 'Missing argument: '
            . ($raceId ? '' : 'race ')
            . ($classId ? '' : 'class ')
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

        $success = BuilderTool::initSkills($player);
        return [
            'error' => $success ? false : true,
            'content' => $this->renderPartial('ajax-skills', [
                'player' => $player,
                'backgroundSkills' => $player->background->backgroundSkills,
                'playerSkills' => $player->playerSkills,
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
        $success = BuilderTool::initTraits($player);

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

        $endowmentTable = $player->initialEndowment;
        $backgroundItems = $player->background->backgroundItems;

        return [
            'error' => false,
            'content' => $this->renderPartial('ajax-endowment', [
                'player' => $player,
                'endowments' => $endowmentTable,
                'backgroundItems' => $backgroundItems,
                'choices' => max(array_keys($endowmentTable)),
            ]),
        ];
    }

    public function actionAjaxBackgroundEquipment() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $equipments = BackgroundItem::findAll(['background_id' => $request->post('backgroundId')]);

        $content = BuilderTool::setEquipmentResponse($equipments);
        return [
            'error' => false,
            'content' => $content,
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

        $equipments = ClassEquipment::findAll(['endowment_id' => $request->post('endowmentId')]);

        $content = BuilderTool::setEquipmentResponse($equipments, $choice);
        return [
            'error' => false,
            'content' => $content,
        ];
    }

    /**
     * Parse the json string into an associative array
     *
     * @param string[] $itemIds
     * @return array Associative array with the following structure ['itemId' => 123, 'quantity' => 1]
     */
    private function getItemQuantityFromJson(array $itemIds): array {
        $itemQuantity = [];
        foreach ($itemIds as $itemId) {
            $selections = explode(',', $itemId);
            foreach ($selections as $selection) {
                $data = explode('|', $selection);
                $itemQuantity[] = [
                    'itemId' => $data[0],
                    'quantity' => $data[1]
                ];
            }
        }
        return $itemQuantity;
    }

    /**
     * Add an item in the player's inventory
     *
     * @param Player $player
     * @param int $itemId
     * @param int $quantity
     * @return bool
     * @throws \Exception
     */
    private function addItem(Player &$player, int $itemId, int $quantity): bool {

        $playerItem = PlayerItem::findOne([
            'player_id' => $player->id,
            'item_id' => $itemId
        ]);

        if ($playerItem) {
            $playerItem->quantity += $quantity;
        } else {
            $playerItem = $this->newPlayerItem($player, $itemId, $quantity);
        }

        if ($playerItem->save()) {
            return true;
        }
        throw new \Exception(implode("<br />", ArrayHelper::getColumn($playerItem->errors, 0, false)));
    }

    /**
     * Create and initialize a new instance of PlayerItem object
     *
     * @param Player $player
     * @param int $itemId
     * @param int $quantity
     * @return PlayerItem
     */
    private function newPlayerItem(Player $player, int $itemId, int $quantity): PlayerItem {
        $isProficient = PlayerTool::isProficient($player->class->id, $itemId) ? 1 : 0;
        $proficiencyModifier = $isProficient ? $player->level->proficiency_bonus : 0;

        $weaponProperties = PlayerTool::getPlayerWeaponProperties($player->id, $itemId, $proficiencyModifier);

        $playerItem = new PlayerItem([
            'player_id' => $player->id,
            'item_id' => $itemId,
            'quantity' => $quantity,
            'is_carrying' => 1,
            'is_equiped' => 1,
            'is_proficient' => $isProficient,
            'attack_modifier' => $weaponProperties['attackModifier'],
            'damage' => $weaponProperties['damage'],
        ]);

        return $playerItem;
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

        if (!$itemIds) {
            return ['error' => true, 'msg' => 'Missing item ids'];
        }

        $itemQuantity = $this->getItemQuantityFromJson($itemIds);
        $player = $this->findModel($playerId);
        foreach ($itemQuantity as $itemQuantity) {
            $this->addItem($player, $itemQuantity['itemId'], $itemQuantity['quantity']);
        }

        return ['error' => 'false', 'msg' => 'Initial items are saved'];
    }

    private function getItemsCategory($request) {
        // First split by comma to get each pair
        $ids = $request->post('categoryIds');
        $pairs = explode(',', $ids);

        $quantity = explode('|', $pairs[0])[1];

        // Extract first elements (ids) using array_map
        $categoryIds = array_map(function ($pair) {
            return explode('|', $pair)[0];
        }, $pairs);

        $param = [
            'modelName' => 'ItemCategory',
            'render' => 'ajax-item-category',
            'with' => ['item', 'image'],
            'param' => [
                'choice' => $request->post('choice'),
                'alreadySelectedItems' => $request->post('alreadySelectedItems'),
                'quantity' => $quantity
            ],
            'filter' => ['category_id' => $categoryIds],
        ];

        return new AjaxRequest($param);
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
        $ajaxRequest = $this->getItemsCategory($request);

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

        // Reset the player skills to 0
        // updateAll does not need "save()" and returns the number of updated rows
        PlayerSkill::updateAll(//            update player_skill
                ['is_proficient' => 0], //   set is_proficient=0
                ['player_id' => $playerId]// where player_id=:playerId
        );

        // get the list of skill ids from the ajax param "skills"
        $skills = $request->post('skills');
        $update = PlayerSkill::updateAll(
                ['is_proficient' => 1],
                ['player_id' => $playerId, 'skill_id' => $skills]
        );

        $success = ($update > 0);
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
        if ($model) {
            $abilities = $request->post('abilities');
            foreach ($model->playerAbilities as $playerAbility) {
                $ability_id = $playerAbility->ability_id;
                $score = $abilities[$ability_id];
                $playerAbility->score = $score;
                $playerAbility->modifier = PlayerTool::calcAbilityModifier($score);
                if (!$playerAbility->save()) {
                    throw new \Exception(implode("<br />", ArrayHelper::getColumn($playerAbility->errors, 0, false)));
                }
            }
        }

        return ['error' => 'false', 'msg' => 'Abilities are saved'];
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
    public function actionUpdate(int $id) {
        $model = $this->findModel($id);

        if ($model->status === AppStatus::ACTIVE->value) {
            return $this->redirect(['player/view', 'id' => $id]);
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $id]);
        }

        return $this->render('update', [
                    'model' => $model
        ]);
    }

    /**
     * Displays a single ActionButton model.
     *
     * @param int $id Primary key
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id) {
        return $this->redirect(['player/view', 'id' => $id]);
    }

    /**
     * Finds the Player model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Player the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id) {

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
