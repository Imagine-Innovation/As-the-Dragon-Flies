<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "passage_status_item".
 *
 * @property int $status_id Foreign key to "passage_status" table
 * @property int $item_id Foreign key to "item" table
 * @property int $is_mandatory Indicates that the object is mandatory to open the passageway
 * @property int $bonus Bonus for using an object to open the door
 *
 * @property Item $item
 * @property PassageStatus $status
 */
class PassageStatusItem extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'passage_status_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['status_id', 'item_id'], 'required'],
            [['status_id', 'item_id', 'is_mandatory', 'bonus'], 'integer'],
            [['status_id', 'item_id'], 'unique', 'targetAttribute' => ['status_id', 'item_id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => PassageStatus::class, 'targetAttribute' => ['status_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'status_id' => 'Foreign key to \"passage_status\" table',
            'item_id' => 'Foreign key to \"item\" table',
            'is_mandatory' => 'Indicates that the object is mandatory to open the passageway',
            'bonus' => 'Bonus for using an object to open the door',
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
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus() {
        return $this->hasOne(PassageStatus::class, ['id' => 'status_id']);
    }
}
