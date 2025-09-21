<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "interaction_item".
 *
 * @property int $item_id Foreign key to "item" table
 * @property int $interaction_id Foreign key to "interaction" table
 * @property int $is_mandatory Indicates that the object is mandatory to do the interaction
 *
 * @property Interaction $interaction
 * @property Item $item
 */
class InteractionItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'interaction_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['is_mandatory'], 'default', 'value' => 0],
            [['item_id', 'interaction_id'], 'required'],
            [['item_id', 'interaction_id', 'is_mandatory'], 'integer'],
            [['item_id', 'interaction_id'], 'unique', 'targetAttribute' => ['item_id', 'interaction_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['interaction_id'], 'exist', 'skipOnError' => true, 'targetClass' => Interaction::class, 'targetAttribute' => ['interaction_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'item_id' => 'Foreign key to "item" table',
            'interaction_id' => 'Foreign key to "interaction" table',
            'is_mandatory' => 'Indicates that the object is mandatory to do the interaction',
        ];
    }

    /**
     * Gets query for [[Interaction]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInteraction() {
        return $this->hasOne(Interaction::class, ['id' => 'interaction_id']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem() {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
    }

}
