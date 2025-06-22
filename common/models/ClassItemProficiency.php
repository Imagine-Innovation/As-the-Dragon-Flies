<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_item_proficiency".
 *
 * @property int $class_id Foreign key to "class" table
 * @property int|null $category_id Optional foreign key to "category" table
 * @property int|null $item_id Optional foreign key to "item" table
 *
 * @property Category $category
 * @property Class $class
 * @property Item $item
 */
class ClassItemProficiency extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'class_item_proficiency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['category_id', 'item_id'], 'default', 'value' => null],
            [['class_id'], 'required'],
            [['class_id', 'category_id', 'item_id'], 'integer'],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'class_id' => 'Foreign key to \"class\" table',
            'category_id' => 'Optional foreign key to \"category\" table',
            'item_id' => 'Optional foreign key to \"item\" table',
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory() {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
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
