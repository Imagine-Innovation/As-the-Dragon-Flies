<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_ability".
 *
 * @property int $player_id Foreign key to “player” table
 * @property int $ability_id Foreign key to “ability” table
 * @property int $score Ability score
 * @property int $bonus Ability bonus
 * @property int $modifier Modifier
 * @property int $is_primary_ability The ability is the player's primary one
 * @property int $is_saving_throw Indicates that it can be used for a saving throw
 *
 * @property Ability $ability
 * @property Player $player
 */
class PlayerAbility extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_ability';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['score'], 'default', 'value' => 10],
            [['is_saving_throw'], 'default', 'value' => 0],
            [['player_id', 'ability_id'], 'required'],
            [['player_id', 'ability_id', 'score', 'bonus', 'modifier', 'is_primary_ability', 'is_saving_throw'], 'integer'],
            [['player_id', 'ability_id'], 'unique', 'targetAttribute' => ['player_id', 'ability_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['ability_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ability::class, 'targetAttribute' => ['ability_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to “player” table',
            'ability_id' => 'Foreign key to “ability” table',
            'score' => 'Ability score',
            'bonus' => 'Ability bonus',
            'modifier' => 'Modifier',
            'is_primary_ability' => 'The ability is the player\'s primary one',
            'is_saving_throw' => 'Indicates that it can be used for a saving throw',
        ];
    }

    /**
     * Gets query for [[Ability]].
     *
     * @return \yii\db\ActiveQuery<Ability>
     */
    public function getAbility() {
        return $this->hasOne(Ability::class, ['id' => 'ability_id']);
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }
}
