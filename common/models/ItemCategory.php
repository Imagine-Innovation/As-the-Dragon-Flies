<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "item_category".
 *
 * @property int $item_id Foreign key to “item” table
 * @property int $category_id Foreign key to “category” table
 * @property int $is_main Is main category
 *
 * @property Category $category
 * @property Item $item
 */
class ItemCategory extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'item_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['is_main'], 'default', 'value' => 0],
            [['item_id', 'category_id'], 'required'],
            [['item_id', 'category_id', 'is_main'], 'integer'],
            [['item_id', 'category_id'], 'unique', 'targetAttribute' => ['item_id', 'category_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'item_id' => 'Foreign key to “item” table',
            'category_id' => 'Foreign key to “category” table',
            'is_main' => 'Is main category',
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery<Category>
     */
    public function getCategory() {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getItem() {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
    }
}
