<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "creature_condition".
 *
 * @property int $id Primary key
 * @property string $name Condition name
 * @property string|null $description Short description
 *
 * @property CreatureImmunization[] $creatureImmunizations
 * @property Creature[] $creatures
 * @property GridCreatureCondition[] $gridCreatureConditions
 * @property GridShape[] $grids
 * @property PlayerCondition[] $playerConditions
 * @property Player[] $players
 */
class CreatureCondition extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'creature_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Condition name',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[CreatureImmunizations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureImmunizations() {
        return $this->hasMany(CreatureImmunization::class, ['condition_id' => 'id']);
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatures() {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->via('creatureImmunizations');
    }

    /**
     * Gets query for [[GridCreatureConditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGridCreatureConditions() {
        return $this->hasMany(GridCreatureCondition::class, ['condition_id' => 'id']);
    }

    /**
     * Gets query for [[Grids]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrids() {
        return $this->hasMany(GridShape::class, ['grid_id' => 'grid_id', 'shape_id' => 'shape_id'])->via('gridCreatureConditions');
    }

    /**
     * Gets query for [[PlayerConditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerConditions() {
        return $this->hasMany(PlayerCondition::class, ['condition_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('playerConditions');
    }
}
