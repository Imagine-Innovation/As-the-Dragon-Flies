<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "character_trait".
 *
 * @property int $id Primary key
 * @property string $name Character trait
 * @property string $description Description
 * @property string $dice Dice to roll to randomly assign a trait to a player regarding its background
 *
 * @property BackgroundTrait[] $backgroundTraits
 * @property PlayerTrait[] $playerTraits
 * @property Player[] $players
 */
class CharacterTrait extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'character_trait';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name', 'description', 'dice'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['dice'], 'string', 'max' => 8],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Character trait',
            'description' => 'Description',
            'dice' => 'Dice to roll to randomly assign a trait to a player regarding its background',
        ];
    }

    /**
     * Gets query for [[BackgroundTraits]].
     *
     * @return \yii\db\ActiveQuery<BackgroundTrait>
     */
    public function getBackgroundTraits() {
        return $this->hasMany(BackgroundTrait::class, ['trait_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerTraits]].
     *
     * @return \yii\db\ActiveQuery<PlayerTrait>
     */
    public function getPlayerTraits() {
        return $this->hasMany(PlayerTrait::class, ['trait_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('player_trait', ['trait_id' => 'id']);
    }

}
