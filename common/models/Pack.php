<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pack".
 *
 * @property int $parent_item_id Foreign key to "item" table. The table's record will be the container.
 * @property int $item_id Foreign key to "item" table. The table's record will be the content.
 * @property int $quantity Quantity contained
 *
 * @property Item[] $items
 * @property Item $parentItem
 */
class Pack extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'pack';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['parent_item_id', 'item_id', 'quantity'], 'required'],
            [['parent_item_id', 'item_id', 'quantity'], 'integer'],
            [['parent_item_id', 'item_id'], 'unique', 'targetAttribute' => ['parent_item_id', 'item_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['parent_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['parent_item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'parent_item_id' => 'Foreign key to \"item\" table. The table\'s record will be the container.',
            'item_id' => 'Foreign key to \"item\" table. The table\'s record will be the content.',
            'quantity' => 'Quantity contained',
        ];
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id']);
    }

    /**
     * Gets query for [[ParentItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParentItem() {
        return $this->hasOne(Item::class, ['id' => 'parent_item_id']);
    }
}
