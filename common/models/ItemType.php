<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "item_type".
 *
 * @property int $id Primary key
 * @property string $name Item type
 * @property string|null $description Short description
 * @property int $sort_order Sort order
 *
 * @property Item[] $items
 */
class ItemType extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'item_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['sort_order'], 'integer'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Item type',
            'description' => 'Short description',
            'sort_order' => 'Sort order',
        ];
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['item_type_id' => 'id']);
    }
}
