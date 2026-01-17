<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ManageAccessRights;
use common\models\BackgroundItem;
use common\models\ClassEquipment;
use common\models\Image;
use common\models\Item;
use common\models\Player;
use common\models\PlayerItem;
use common\models\PlayerLanguage;
use common\models\Skill;
use frontend\components\AjaxRequest;
use frontend\components\BuilderComponent;
use frontend\components\PlayerComponent;
use frontend\models\PlayerBuilder;
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
class PlayerBuilderController extends Controller
{

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
                                    'ajax-names', 'ajax-skills', 'ajax-traits', 'ajax-languages',
                                    'ajax-item-category', 'ajax-update-skill', 'ajax-save-abilities',
                                    'ajax-save-equipment', 'ajax-update-language',
                                ],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                ]
        );
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string, ageTable?: mixed}
     */
    public function actionAjaxAge(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $raceId = $request->post('raceId');
        $age = $request->post('age', 0);
        Yii::debug("*** Debug *** actionAjaxAge raceId={$raceId}, age={$age}", __METHOD__);

        $ageTable = BuilderComponent::loadAgeTable($raceId);

        $content = $this->renderPartial('ajax/age', [
            'age' => $age,
            'ageTable' => $ageTable
        ]);

        return [
            'error' => false, 'msg' => '',
            'content' => $content,
            'ageTable' => json_encode($ageTable['labels']),
        ];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxNames(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $raceId = $request->post('raceId');
        $gender = $request->post('gender', 'M');
        $n = $request->post('n', 3);

        return [
            'error' => false, 'msg' => '',
            'content' => $this->renderPartial('ajax/names', [
                'names' => BuilderComponent::loadRandomNames($raceId, $gender, $n)
            ]),
        ];
    }

    /**
     *
     * @param int $imageId
     * @param int $raceId
     * @param int $classId
     * @param string $gender
     * @return array{error: bool, msg: string, content?: string}
     */
    private function renderImages(int $imageId, int $raceId, int $classId, string $gender): array {
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
            'content' => $this->renderPartial('ajax/image', ['imageId' => $imageId, 'images' => $images])];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxImages(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $raceId = $request->post('raceId');
        $classId = $request->post('classId');
        $gender = $request->post('gender');

        if ($raceId > 0 && $classId > 0 && $gender) {
            $imageId = $request->post('imageId');
            return $this->renderImages($imageId, $raceId, $classId, $gender);
        }
        return ['error' => true, 'msg' => 'Missing argument: '
            . ($raceId ? '' : 'race ')
            . ($classId ? '' : 'class ')
            . ($gender ? '' : 'gender')];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxSkills(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);

        $skills = BuilderComponent::initPlayerSkills($player);
        return [
            'error' => false, 'msg' => '',
            'content' => $this->renderPartial('ajax/skills', [
                'player' => $player,
                'backgroundSkills' => $skills['BackgroundSkills'],
                'classSkills' => $skills['ClassSkills'],
                'n' => $player->class->max_skills,
            ]),
        ];
    }

    /**
     * Player Languages handling
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxLanguages(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);

        $languages = BuilderComponent::initPlayerLanguages($player);
        $this->saveLanguages($playerId, $languages['RaceLanguages']);
        return [
            'error' => false, 'msg' => '',
            'content' => $this->renderPartial('ajax/languages', [
                'player' => $player,
                'raceLanguages' => $languages['RaceLanguages'],
                'otherLanguages' => $languages['OtherLanguages'],
                'n' => $player->background->languages,
            ]),
            'n' => $player->background->languages,
        ];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxUpdateLanguage(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $languageId = $request->post('languageId');
        $selected = $request->post('selected');
        Yii::debug("*** debug *** actionAjaxUpdateLanguage - playerId={$playerId}, languageId=[$languageId}, selected={$selected}");
        if ($selected) {
            $this->addLanguage($playerId, $languageId);
        } else {
            PlayerLanguage::deleteAll([
                'player_id' => $playerId,
                'language_id' => $languageId
            ]);
        }

        return ['error' => false, 'msg' => 'Language is updated'];
    }

    /**
     *
     * @param int $playerId
     * @param array<int, array{language_id: int, name: string}>|array{} $languages
     * @return void
     */
    private function saveLanguages(int $playerId, array $languages): void {
        Yii::debug("*** debug *** saveLanguages - playerId={$playerId}, languageId=" . print_r($languages, true));
        foreach ($languages as $lang) {
            $this->addLanguage($playerId, $lang['language_id']);
        }
    }

    /**
     *
     * @param int $playerId
     * @param int $languageId
     * @return void
     * @throws \Exception
     */
    private function addLanguage(int $playerId, int $languageId): void {
        Yii::debug("*** debug *** addLanguage - playerId={$playerId}, languageId=[$languageId}");
        $playerLanguage = PlayerLanguage::findOne([
            'player_id' => $playerId,
            'language_id' => $languageId
        ]);

        if (!$playerLanguage) {
            $playerLanguage = new PlayerLanguage([
                'player_id' => $playerId,
                'language_id' => $languageId
            ]);

            if ($playerLanguage->save()) {
                return;
            }
            throw new \Exception(implode("<br/>", ArrayHelper::getColumn($playerLanguage->errors, 0, false)));
        }
    }

    /**
     * Player Traits handling
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxTraits(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);
        BuilderComponent::initTraits($player);

        return [
            'error' => false, 'msg' => '',
            'content' => $this->renderPartial('ajax/traits', [
                'player' => $player,
            ]),
        ];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxEndowment(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);

        $endowmentTable = $player->getInitialEndowment();
        /** @phpstan-ignore-next-line */
        $choices = max(array_keys($endowmentTable));
        Yii::debug($endowmentTable);
        return [
            'error' => false, 'msg' => '',
            'content' => $this->renderPartial('ajax/endowment', [
                'endowments' => $endowmentTable,
                'choices' => $choices,
            ]),
            'endowments' => $endowmentTable,
            'choices' => $choices,
        ];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: mixed}
     */
    public function actionAjaxBackgroundEquipment(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $backgroundId = $request->post('backgroundId');
        $equipments = BackgroundItem::findAll(['background_id' => $backgroundId]);

        $content = BuilderComponent::setEquipmentResponse($equipments);
        return [
            'error' => false, 'msg' => '',
            'content' => $content,
        ];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: mixed}
     */
    public function actionAjaxEquipment(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $choice = $request->post('choice');
        $endowmentId = $request->post('endowmentId');
        $equipments = ClassEquipment::findAll(['endowment_id' => $endowmentId]);

        $content = BuilderComponent::setEquipmentResponse($equipments, $choice);
        return [
            'error' => false, 'msg' => '',
            'content' => $content,
        ];
    }

    /**
     * Parse the json string into an associative array
     *
     * @param string[] $itemIds
     * @return list<array{itemId: int, quantity: int}> Associative array with the following structure ['itemId' => 123, 'quantity' => 1]
     */
    private function getItemQuantityFromJson(array $itemIds): array {
        $itemQuantity = [];
        foreach ($itemIds as $itemId) {
            $selections = explode(',', $itemId);
            foreach ($selections as $selection) {
                $data = explode('|', $selection);
                $itemQuantity[] = [
                    'itemId' => (int) $data[0],
                    'quantity' => (int) $data[1]
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
     * @return void
     * @throws \Exception
     */
    private function addItem(Player &$player, int $itemId, int $quantity): void {

        $item = Item::findOne(['id' => $itemId]);
        if ($item === null) {
            return;
        }

        $itemType = $item->itemType->name;
        if ($itemType === 'Pack') {
            $this->unpack($player, $item, $quantity);
        } else {
            $playerItem = PlayerItem::findOne([
                'player_id' => $player->id,
                'item_id' => $itemId
            ]);

            if ($playerItem) {
                $item = $playerItem->item;
                $playerItem->quantity += $item->quantity * $quantity;
            } else {
                $playerItem = $this->newPlayerItem($player, $item, $quantity);
            }

            if ($playerItem->save()) {
                return;
            }
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($playerItem->errors, 0, false)));
        }
    }

    /**
     * Create and initialize a new instance of PlayerItem object
     *
     * @param Player $player
     * @param Item $item
     * @param int $quantity
     * @return PlayerItem
     */
    private function newPlayerItem(Player &$player, Item &$item, int $quantity): PlayerItem {
        $isProficient = PlayerComponent::isProficient($player->class_id, $item->id) ? 1 : 0;
        $proficiencyModifier = $isProficient ? $player->level->proficiency_bonus : 0;

        $weaponProperties = PlayerComponent::getPlayerWeaponProperties($player->id, $item->id, $proficiencyModifier);
        $itemType = $item->itemType->name;

        return new PlayerItem([
            'player_id' => $player->id,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_type' => $itemType,
            'image' => $item->image,
            'quantity' => ($item->quantity ?? 1) * $quantity,
            'is_carrying' => 1,
            'is_proficient' => $isProficient,
            'is_two_handed' => $weaponProperties['isTwoHanded'],
            'attack_modifier' => $weaponProperties['attackModifier'],
            'damage' => $weaponProperties['damage'],
        ]);
    }

    /**
     *
     * @param Player $player
     * @param Item $pack
     * @param int $quantity
     * @return void
     */
    private function unpack(Player &$player, Item &$pack, int $quantity): void {
        $packItems = $pack->packItems;
        foreach ($packItems as $item) {
            $this->addItem($player, $item->id, $quantity);
        }
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxSaveEquipment(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);

        $itemIds = $request->post('itemIds');

        // first of all, delete existing items.
        // As a side effect, we must assume that the player has no other items
        // purchased or collected during the setup process.
        PlayerItem::deleteAll(['player_id' => $playerId]);

        if (empty($itemIds)) {
            return ['error' => true, 'msg' => 'Missing item ids'];
        }

        $itemQuantity = $this->getItemQuantityFromJson($itemIds);
        foreach ($itemQuantity as $itemQuantity) {
            $this->addItem($player, $itemQuantity['itemId'], $itemQuantity['quantity']);
        }

        return ['error' => false, 'msg' => 'Initial items are saved'];
    }

    /**
     *
     * @param \yii\web\Request $request
     * @return AjaxRequest
     */
    private function getItemsCategory(\yii\web\Request $request): AjaxRequest {
        // First split by comma to get each pair
        $idsPost = $request->post('categoryIds');
        $idsString = is_string($idsPost) ? (string) $idsPost : '';
        $pairs = explode(',', $idsString);

        $quantity = explode('|', $pairs[0])[1];

        // Extract first elements (ids) using array_map
        $categoryIds = array_map(function ($pair) {
            return explode('|', $pair)[0];
        }, $pairs);

        $param = [
            'modelName' => 'ItemCategory',
            'render' => 'item-category',
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

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxItemCategory(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $ajaxRequest = $this->getItemsCategory($request);

        if ($ajaxRequest->makeResponse($request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxUpdateSkill(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);

        $skillId = $request->post('skillId');
        $skill = $this->findSkill($skillId);
        $isProficient = $request->post('isProficient');

        BuilderComponent::updateSkill($player, $skill, $isProficient);

        return ['error' => false, 'msg' => 'Skill is updated'];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     * @throws \Exception
     */
    public function actionAjaxSaveAbilities(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $playerId = $request->post('playerId');
        $player = $this->findModel($playerId);
        if (!$player->isNewRecord) {
            $abilities = $request->post('abilities');
            foreach ($player->playerAbilities as $playerAbility) {
                $ability_id = $playerAbility->ability_id;
                $score = $abilities[$ability_id];
                $playerAbility->score = $score;
                $playerAbility->modifier = PlayerComponent::calcAbilityModifier($score);
                if (!$playerAbility->save()) {
                    throw new \Exception(implode("<br />", ArrayHelper::getColumn($playerAbility->errors, 0, false)));
                }
            }
        }

        return ['error' => false, 'msg' => 'Abilities are saved'];
    }

    /**
     * Creates a new Player model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate(): string|Response {
        $playerBuilder = new PlayerBuilder();

        if ($this->request->isPost) {
            $post = (array) $this->request->post();
            if ($playerBuilder->load($post) && $playerBuilder->save()) {
                return $this->redirect(['update', 'id' => $playerBuilder->id]);
            }
        } else {
            $playerBuilder->loadDefaultValues();
            $playerBuilder->user_id = Yii::$app->user->identity->id;
        }

        return $this->render('create', [
                    'model' => $playerBuilder,
        ]);
    }

    /**
     * Updates an existing Player model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id): string|Response {
        $model = $this->findModel($id);

        if ($model->status === AppStatus::ACTIVE->value) {
            return $this->redirect(['player/view', 'id' => $id]);
        }

        $post = (array) $this->request->post();
        if ($this->request->isPost && $model->load($post) && $model->save()) {
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
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): Response {
        return $this->redirect(['player/view', 'id' => $id]);
    }

    /**
     * Finds the Player model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PlayerBuilder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): PlayerBuilder {

        $query = PlayerBuilder::find()
                ->with(['race', 'class', 'background', 'playerAbilities', 'playerSkills', 'playerTraits'])
                ->where(['id' => $id]);

        $user = Yii::$app->user->identity;
        if (!$user->is_admin) {
            $query->andWhere(['user_id' => $user->id]);
        }

        if (($model = $query->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The player you are looking for does not exist.');
    }

    /**
     *
     * @param int $id
     * @return Skill
     * @throws NotFoundHttpException
     */
    protected function findSkill(int $id): Skill {
        if (($model = Skill::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The skill you are looking for does not exist.');
    }

    /**
     *
     * @param int $id
     * @return Item
     * @throws NotFoundHttpException
     */
    protected function findItem(int $id): Item {
        if (($model = Item::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The item you are looking for does not exist.');
    }
}
