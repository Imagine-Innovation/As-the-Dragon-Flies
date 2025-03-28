<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_item".
 *
 * @property int $class_id Foreign key to "class" table
 * @property int $item_id Foreign key to "item" table
 *
 * @property Class $class
 * @property Item $item
 */
class ClassItem extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'class_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['class_id', 'item_id'], 'required'],
            [['class_id', 'item_id'], 'integer'],
            [['class_id', 'item_id'], 'unique', 'targetAttribute' => ['class_id', 'item_id']],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'class_id' => 'Foreign key to \"class\" table',
            'item_id' => 'Foreign key to \"item\" table',
        ];
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClass() {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
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
