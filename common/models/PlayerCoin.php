<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_coin".
 *
 * @property int $player_id Foreign key to "player" table
 * @property string $coin Currency
 * @property int $quantity Quantity
 *
 * @property Player $player
 */
class PlayerCoin extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_coin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['player_id', 'coin'], 'required'],
            [['player_id', 'quantity'], 'integer'],
            [['coin'], 'string', 'max' => 2],
            [['player_id', 'coin'], 'unique', 'targetAttribute' => ['player_id', 'coin']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to "player" table',
            'coin' => 'Currency',
            'quantity' => 'Quantity',
        ];
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }
}
