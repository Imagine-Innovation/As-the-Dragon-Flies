<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_item".
 *
 * @property int $player_id Foreign key to “player” table
 * @property int $item_id Foreign key to “item” table
 * @property string $item_name Item name
 * @property string $item_type Item type
 * @property string|null $image Image
 * @property int $quantity Quantity
 * @property int $is_carrying Indicates that the item is currently in the player back bag
 * @property int $is_proficient Indicates that the player is proficient with the item
 * @property int $is_two_handed This weapon requires two hands when you attack with it
 * @property int|null $attack_modifier Score you add to a d20 roll when attempting to attack with a weapon
 * @property string|null $damage Amount of damage you do to the target on a successful hit
 *
 * @property Item $item
 * @property Player $player
 * @property PlayerBody $playerBody
 * @property PlayerBody $PlayerHead
 * @property PlayerBody $playerChest
 * @property PlayerBody $playerRightHand
 * @property PlayerBody $playerLeftHand
 * @property PlayerBody $playerBack
 *
 * Custom properties for Item inherited sub-classes
 *
 * @property Armor $armor
 * @property Pack $pack
 * @property Poison $poison
 * @property Weapon $weapon
 *
 */
class PlayerItem extends \yii\db\ActiveRecord
{

    const BODY_HEAD_ZONE = 'equipmentHeadZone';
    const BODY_CHEST_ZONE = 'equipmentChestZone';
    const BODY_RIGHT_HAND_ZONE = 'equipmentRightHandZone';
    const BODY_LEFT_HAND_ZONE = 'equipmentLeftHandZone';
    const BODY_BACK_ZONE = 'equipmentBackZone';
    // Define the properties to be used based on hand laterality
    const HAND_PROPERTIES = [
        self::BODY_RIGHT_HAND_ZONE => [
            'otherHandProperty' => 'leftHand',
            'otherHandItemIdField' => 'left_hand_item_id',
            'otherHandBodyZone' => self::BODY_LEFT_HAND_ZONE,
            'itemIdField' => 'right_hand_item_id'
        ],
        self::BODY_LEFT_HAND_ZONE => [
            'otherHandProperty' => 'rightHand',
            'otherHandItemIdField' => 'right_hand_item_id',
            'otherHandBodyZone' => self::BODY_RIGHT_HAND_ZONE,
            'itemIdField' => 'left_hand_item_id'
        ],
    ];
    // Match the properties of the PlayerBody object to the image area
    const BODY_ZONE = [
        'head' => self::BODY_HEAD_ZONE,
        'chest' => self::BODY_CHEST_ZONE,
        'rightHand' => self::BODY_RIGHT_HAND_ZONE,
        'leftHand' => self::BODY_LEFT_HAND_ZONE,
        'back' => self::BODY_BACK_ZONE,
    ];
    // Match the body zone with the PlayerBody properties.
    const BODY_PROPERTIES = [
        self::BODY_HEAD_ZONE => [
            'itemIdField' => 'head_item_id',
            'property' => 'head'
        ],
        self::BODY_CHEST_ZONE => [
            'itemIdField' => 'chest_item_id',
            'property' => 'chest'
        ],
        self::BODY_RIGHT_HAND_ZONE => [
            'itemIdField' => 'right_hand_item_id',
            'property' => 'rightHand'
        ],
        self::BODY_LEFT_HAND_ZONE => [
            'itemIdField' => 'left_hand_item_id',
            'property' => 'leftHand'
        ],
        self::BODY_BACK_ZONE => [
            'itemIdField' => 'back_item_id',
            'property' => 'back'
        ],
    ];

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
            [['image', 'attack_modifier', 'damage'], 'default', 'value' => null],
            [['quantity'], 'default', 'value' => 1],
            [['is_proficient', 'is_two_handed'], 'default', 'value' => 0],
            [['player_id', 'item_id', 'item_name', 'item_type'], 'required'],
            [['player_id', 'item_id', 'quantity', 'is_carrying', 'is_proficient', 'is_two_handed', 'attack_modifier'], 'integer'],
            [['item_name', 'item_type', 'image', 'damage'], 'string', 'max' => 64],
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
            'player_id' => 'Foreign key to “player” table',
            'item_id' => 'Foreign key to “item” table',
            'item_name' => 'Item name',
            'item_type' => 'Item type',
            'image' => 'Image',
            'quantity' => 'Quantity',
            'is_carrying' => 'Indicates that the item is currently in the player back bag',
            'is_proficient' => 'Indicates that the player is proficient with the item',
            'is_two_handed' => 'This weapon requires two hands when you attack with it',
            'attack_modifier' => 'Score you add to a d20 roll when attempting to attack with a weapon',
            'damage' => 'Amount of damage you do to the target on a successful hit',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getItem() {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
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
     * Gets query for [[PlayerBody]].
     *
     * @return \yii\db\ActiveQuery<PlayerBody>
     */
    public function getPlayerBody() {
        return $this->hasOne(PlayerBody::class, ['player_id' => 'player_id']);
    }

    /**
     * Gets query for [[PlayerHead]].
     *
     * @return \yii\db\ActiveQuery<PlayerBody>
     */
    public function getPlayerHead() {
        return $this->hasOne(PlayerBody::class, ['player_id' => 'player_id', 'head_item_id' => 'item_id']);
    }

    /**
     * Gets query for [[PlayerChest]].
     *
     * @return \yii\db\ActiveQuery<PlayerBody>
     */
    public function getPlayerChest() {
        return $this->hasOne(PlayerBody::class, ['player_id' => 'player_id', 'chest_item_id' => 'item_id']);
    }

    /**
     * Gets query for [[PlayerRightHand]].
     *
     * @return \yii\db\ActiveQuery<PlayerBody>
     */
    public function getPlayerRightHand() {
        return $this->hasOne(PlayerBody::class, ['player_id' => 'player_id', 'right_hand_item_id' => 'item_id']);
    }

    /**
     * Gets query for [[PlayerLeftHand]].
     *
     * @return \yii\db\ActiveQuery<PlayerBody>
     */
    public function getPlayerLeftHand() {
        return $this->hasOne(PlayerBody::class, ['player_id' => 'player_id', 'left_hand_item_id' => 'item_id']);
    }

    /**
     * Gets query for [[PlayerBack]].
     *
     * @return \yii\db\ActiveQuery<PlayerBody>
     */
    public function getPlayerBack() {
        return $this->hasOne(PlayerBody::class, ['player_id' => 'player_id', 'back_item_id' => 'item_id']);
    }

    /**
     * Custom properties
     */

    /**
     * Gets query for [[Armor]].
     *
     * @return \yii\db\ActiveQuery<Armor>
     */
    public function getArmor() {
        return $this->hasOne(Armor::class, ['item_id' => 'item_id']);
    }

    /**
     * Gets query for [[Pack]].
     *
     * @return \yii\db\ActiveQuery<Pack>
     */
    public function getPack() {
        return $this->hasOne(Pack::class, ['parent_item_id' => 'item_id']);
    }

    /**
     * Gets query for [[Poison]].
     *
     * @return \yii\db\ActiveQuery<Poison>
     */
    public function getPoison() {
        return $this->hasOne(Poison::class, ['item_id' => 'item_id']);
    }

    /**
     * Gets query for [[Weapon]].
     *
     * @return \yii\db\ActiveQuery<Weapon>
     */
    public function getWeapon() {
        return $this->hasOne(Weapon::class, ['item_id' => 'item_id']);
    }
}
