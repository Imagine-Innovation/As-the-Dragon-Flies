<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\helpers\MixedHelper;
use common\models\CharacterClass;
use common\models\ClassFeature;
use common\models\Feature;
use common\models\ClassProficiency;
use common\models\Level;
use common\models\Proficiency;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * CharacterClassController implements the CRUD actions for CharacterClass model.
 */
class CharacterClassController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors() {
        /** @phpstan-ignore-next-line */
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
                                'actions' => ['index', 'ajax-wizard', 'view'],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                    'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                            'delete' => ['POST'],
                        ],
                    ],
                ]
        );
    }

    /**
     * Lists all CharacterClass models.
     *
     * @return string
     */
    public function actionIndex(): string {
        $dataProvider = new ActiveDataProvider([
            'query' => CharacterClass::find(),
        ]);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxWizard() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $id = MixedHelper::toInt($request->post('id'));

        $model = $this->findModel($id);

        $content = $this->renderPartial('ajax/wizard', [
            'model' => $model,
        ]);

        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     *
     * @param CharacterClass $model
     * @return bool
     */
    private function hasSpell(CharacterClass $model): bool {
        // Find proficiency ID for Spell to avoid nested sub query
        // in the "foreach" statement
        $spellProficieny = Proficiency::find()
                ->select('id')
                ->where(['name' => 'Spell'])
                ->one();

        if (!$spellProficieny) {
            // Spell proficiency not found
            return false;
        }

        foreach ($model->classProficiencies as $proficiency) {
            if ($proficiency->proficiency_id === (int) $spellProficieny->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Displays a single CharacterClass model.
     *
     * @param int $id ID
     * @return string
     */
    public function actionView(int $id): string {
        $model = $this->findModel($id);
        /** @var Level[] $levels */
        $levels = Level::find()->all();

        $proficiencyHeaders = $this->prepareProficiencyHeaders($model);
        $initialProficiencies = $this->initializeProficiencyLevels($levels, $proficiencyHeaders);
        $proficiencies = $this->populateProficiencies($initialProficiencies, $model);

        $hasSpell = $this->hasSpell($model);

        $spellsByLevel = [];
        if ($hasSpell) {
            $spellsByLevel = $this->getGroupedSpells($model);
        }

        return $this->render('view', [
                    'model' => $model,
                    'hasSpell' => $hasSpell,
                    'proficiencyHeaders' => $proficiencyHeaders,
                    'proficiencies' => $proficiencies,
                    'spellsByLevel' => $spellsByLevel,
        ]);
    }

    /**
     *
     * @param CharacterClass $model
     * @return array<int, string>
     */
    private function prepareProficiencyHeaders(CharacterClass $model): array {
        /** @var array<int, string> $headers */
        $headers = ['Level', 'Proficiency Bonus', 'Features'];

        foreach ($model->classProficiencies as $proficiency) {
            // sort_order starts at 1. Add 2 to let the 3 first initial headers at the beginning of the list
            $colId = 2 + (int) $proficiency->sort_order;
            if (!isset($headers[$colId])) {
                $name = $proficiency->proficiency->name;
                $headers[$colId] = ($name === 'Spell') ? "Spell L{$proficiency->spell_level}" : $name;
            }
        }

        ksort($headers);
        return $headers;
    }

    /**
     * @param Level[] $levels
     * @param array<int, string> $headers
     * @return array<int, array<int, array{value: string, is_header: bool}>>
     */
    private function initializeProficiencyLevels(array $levels, array $headers): array {
        $proficiencies = [];
        foreach ($levels as $level) {
            $levelId = (int) $level->id;
            foreach ($headers as $colId => $colName) {
                $proficiencies[$levelId][$colId] = [
                    'value' => $this->getStaticCellValue($level, $colId),
                    'is_header' => ($colId === 0)
                ];
            }
        }
        return $proficiencies;
    }

    /**
     *
     * @param Level $level
     * @param int $colId
     * @return string
     */
    private function getStaticCellValue(Level $level, int $colId): string {
        return match ($colId) {
            0 => (string) ($level->name ?? ''),
            1 => '+' . ($level->proficiency_bonus ?? 0),
            default => '',
        };
    }

    /**
     * @param array<int, array<int, array{value: string, is_header: bool}>> $proficiencies
     * @param CharacterClass $model
     * @return array<int, array<int, array{value: string, is_header: bool}>>
     */
    private function populateProficiencies(array &$proficiencies, CharacterClass $model): array {
        // Features
        foreach ($model->classFeatures as $feature) {
            $rowId = (int) $feature->level_id;
            if (isset($proficiencies[$rowId][2])) {
                $proficiencies[$rowId][2]['value'] = $this->formatFeature($feature);
            }
        }

        // Proficiencies
        foreach ($model->classProficiencies as $proficiency) {
            $rowId = (int) $proficiency->level_id;
            $colId = 2 + (int) $proficiency->sort_order;
            if (isset($proficiencies[$rowId][$colId])) {
                $proficiencies[$rowId][$colId]['value'] = (string) $proficiency->bonus . (string) $proficiency->dice . (string) $proficiency->spell_slot;
            }
        }

        return $proficiencies;
    }

    /**
     *
     * @param ClassFeature $feature
     * @return string
     */
    private function formatFeature(ClassFeature $feature): string {
        $details = [];
        if ($feature->cr > 0) {
            $details[] = "CR " . $feature->cr;
        }
        if (!empty($feature->dice)) {
            $details[] = (string) $feature->dice;
        }
        if ($feature->weapon_dice > 0) {
            $details[] = "{$feature->weapon_dice} weapon dice";
        }
        if ($feature->times_used > 1) {
            $details[] = "used {$feature->times_used} times";
        }
        if ($feature->spell_level > 0) {
            $details[] = "spell level {$feature->spell_level}";
        }

        $name = $feature->feature->name;
        $detail = implode(', ', $details);
        return empty($details) ? $name : "{$name} ({$detail})";
    }

    /**
     * Groups spells by their level and sorts by level ascending.
     *
     * @param CharacterClass $model
     * @return array<int, array<int, array{id: int|string, name: string}>>
     */
    private function getGroupedSpells(CharacterClass $model): array {
        $grouped = [];

        foreach ($model->spells as $spell) {
            $level = (int) $spell->spell_level;

            $grouped[$level][] = [
                'id' => $spell->id,
                'name' => (string) $spell->name,
            ];
        }

        // Sort keys numerically so Level 0 (Cantrips) comes before Level 1, etc.
        ksort($grouped);

        return $grouped;
    }

    /**
     * Finds the CharacterClass model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return CharacterClass the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): CharacterClass {
        if (($model = CharacterClass::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The character class you are looking for does not exist.');
    }
}
