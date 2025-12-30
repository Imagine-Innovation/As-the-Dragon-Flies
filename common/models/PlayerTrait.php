<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_trait".
 *
 * @property int $player_id Foreign key to “player” table
 * @property int $trait_id Foreign key to “character_trait” table
 * @property string $description Description of the trait
 *
 * @property Player $player
 * @property CharacterTrait $trait
 */
class PlayerTrait extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_trait';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['player_id', 'trait_id', 'description'], 'required'],
            [['player_id', 'trait_id'], 'integer'],
            [['description'], 'string'],
            [['player_id', 'trait_id'], 'unique', 'targetAttribute' => ['player_id', 'trait_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['trait_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterTrait::class, 'targetAttribute' => ['trait_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to “player” table',
            'trait_id' => 'Foreign key to “character_trait” table',
            'description' => 'Description of the trait',
        ];
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }

    /**
     * Gets query for [[Trait]].
     *
     * @return \yii\db\ActiveQuery<CharacterTrait>
     */
    public function getTrait() {
        return $this->hasOne(CharacterTrait::class, ['id' => 'trait_id']);
    }
}
