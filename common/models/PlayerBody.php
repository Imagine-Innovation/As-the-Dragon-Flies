<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_body".
 *
 * @property int $player_id Foreign key to “player” table
 * @property int|null $head_item_id Optional foreign key to “player_item” table. Item that protect the head
 * @property int|null $chest_item_id Optional foreign key to “player_item” table. Item that protect the chest
 * @property int|null $right_hand_item_id Optional foreign key to “player_item” table. Item handled in the right hand
 * @property int|null $left_hand_item_id Optional foreign key to “player_item” table. Item handled in the left hand
 * @property int|null $back_item_id Optional foreign key to “player_item” table. Item carried on the back
 *
 * @property Player $player
 * @property PlayerItem $head
 * @property PlayerItem $chest
 * @property PlayerItem $rightHand
 * @property PlayerItem $leftHand
 * @property PlayerItem $back
 */
class PlayerBody extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_body';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['head_item_id', 'chest_item_id', 'right_hand_item_id', 'left_hand_item_id'], 'default', 'value' => null],
            [['player_id'], 'required'],
            [['player_id', 'head_item_id', 'chest_item_id', 'right_hand_item_id', 'left_hand_item_id'], 'integer'],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['player_id', 'head_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PlayerItem::class,
                'targetAttribute' => ['player_id' => 'player_id', 'head_item_id' => 'item_id'],
                'when' => function ($model) {
                    return $model->head_item_id !== null;
                }
            ],
            [['player_id', 'chest_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PlayerItem::class,
                'targetAttribute' => ['player_id' => 'player_id', 'chest_item_id' => 'item_id'],
                'when' => function ($model) {
                    return $model->chest_item_id !== null;
                }
            ],
            [['player_id', 'right_hand_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PlayerItem::class,
                'targetAttribute' => ['player_id' => 'player_id', 'right_hand_item_id' => 'item_id'],
                'when' => function ($model) {
                    return $model->right_hand_item_id !== null;
                }
            ],
            [['player_id', 'left_hand_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PlayerItem::class,
                'targetAttribute' => ['player_id' => 'player_id', 'left_hand_item_id' => 'item_id'],
                'when' => function ($model) {
                    return $model->left_hand_item_id !== null;
                }
            ],
            [['player_id', 'back_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PlayerItem::class,
                'targetAttribute' => ['player_id' => 'player_id', 'back_item_id' => 'item_id'],
                'when' => function ($model) {
                    return $model->back_item_id !== null;
                }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to “player” table',
            'head_item_id' => 'Optional foreign key to “player_item” table. Item that protect the head',
            'chest_item_id' => 'Optional foreign key to “player_item” table. Item that protect the chest',
            'right_hand_item_id' => 'Optional foreign key to “player_item” table. Item handled in the right hand',
            'left_hand_item_id' => 'Optional foreign key to “player_item” table. Item handled in the left hand',
            'back_item_id' => 'Optional foreign key to “player_item” table. Item carried on the back',
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
     * Gets query for [[Head]].
     *
     * @return \yii\db\ActiveQuery<PlayerItem>
     */
    public function getHead() {
        return $this->hasOne(PlayerItem::class, ['player_id' => 'player_id', 'item_id' => 'head_item_id']);
    }

    /**
     * Gets query for [[Chest]].
     *
     * @return \yii\db\ActiveQuery<PlayerItem>
     */
    public function getChest() {
        return $this->hasOne(PlayerItem::class, ['player_id' => 'player_id', 'item_id' => 'chest_item_id']);
    }

    /**
     * Gets query for [[RightHand]].
     *
     * @return \yii\db\ActiveQuery<PlayerItem>
     */
    public function getRightHand() {
        return $this->hasOne(PlayerItem::class, ['player_id' => 'player_id', 'item_id' => 'right_hand_item_id']);
    }

    /**
     * Gets query for [[LeftHand]].
     *
     * @return \yii\db\ActiveQuery<PlayerItem>
     */
    public function getLeftHand() {
        return $this->hasOne(PlayerItem::class, ['player_id' => 'player_id', 'item_id' => 'left_hand_item_id']);
    }

    /**
     * Gets query for [[Back]].
     *
     * @return \yii\db\ActiveQuery<PlayerItem>
     */
    public function getBack() {
        return $this->hasOne(PlayerItem::class, ['player_id' => 'player_id', 'item_id' => 'back_item_id']);
    }
}
