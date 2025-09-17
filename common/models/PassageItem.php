<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "passage_item".
 *
 * @property int $item_id Foreign key to "item" table
 * @property int $passage_id Foreign key to "passage" table
 * @property int $is_mandatory Indicates that the object is mandatory to open the passageway
 * @property int $bonus Bonus for using an object to open the door
 *
 * @property Item $item
 * @property Passage $passage
 */
class PassageItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'passage_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['bonus'], 'default', 'value' => 0],
            [['item_id', 'passage_id'], 'required'],
            [['item_id', 'passage_id', 'is_mandatory', 'bonus'], 'integer'],
            [['item_id', 'passage_id'], 'unique', 'targetAttribute' => ['item_id', 'passage_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['passage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Passage::class, 'targetAttribute' => ['passage_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'item_id' => 'Foreign key to \"item\" table',
            'passage_id' => 'Foreign key to \"passage\" table',
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
     * Gets query for [[Passage]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassage() {
        return $this->hasOne(Passage::class, ['id' => 'passage_id']);
    }

}
