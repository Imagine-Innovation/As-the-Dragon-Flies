<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $id Primary key
 * @property string $name Category
 * @property string|null $description Short description
 *
 * @property BackgroundItem[] $backgroundItems
 * @property ClassEquipment[] $classEquipments
 * @property ItemCategory[] $itemCategories
 * @property Item[] $items
 */
class Category extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Category',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[BackgroundItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackgroundItems() {
        return $this->hasMany(BackgroundItem::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[ClassEquipments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClassEquipments() {
        return $this->hasMany(ClassEquipment::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[ItemCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemCategories() {
        return $this->hasMany(ItemCategory::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->via('itemCategories');
    }
}
