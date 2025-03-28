<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_item".
 *
 * @property int $player_id Foreign key to "player" table
 * @property int $item_id Foreign key to "item" table
 * @property int $quantity Quantity
 * @property int $is_carrying Indicates that the item is currently in the player back bag
 * @property int $is_equiped Indicated that the item is currently equiped and the player can use it
 *
 * @property Item $item
 * @property Player $player
 */
class PlayerItem extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['player_id', 'item_id'], 'required'],
            [['player_id', 'item_id', 'quantity', 'is_carrying', 'is_equiped'], 'integer'],
            [['player_id', 'item_id'], 'unique', 'targetAttribute' => ['player_id', 'item_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to \"player\" table',
            'item_id' => 'Foreign key to \"item\" table',
            'quantity' => 'Quantity',
            'is_carrying' => 'Indicates that the item is currently in the player back bag',
            'is_equiped' => 'Indicated that the item is currently equiped and the player can use it',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem() {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
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
