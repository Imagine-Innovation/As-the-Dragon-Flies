<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_equipment".
 *
 * @property int $endowment_id Foreign key to "class_endowment" table
 * @property int|null $item_id Optional foreign key to "item" table
 * @property int|null $category_id Optional foreign key to "category" table
 * @property int $if_proficient Can be chosen only if proficient
 * @property int $quantity Quantity
 *
 * @property Category $category
 * @property ClassEndowment $endowment
 * @property Item $item
 */
class ClassEquipment extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'class_equipment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['endowment_id'], 'required'],
            [['endowment_id', 'item_id', 'category_id', 'if_proficient', 'quantity'], 'integer'],
            [['endowment_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClassEndowment::class, 'targetAttribute' => ['endowment_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'endowment_id' => 'Foreign key to "class_endowment" table',
            'item_id' => 'Optional foreign key to "item" table',
            'category_id' => 'Optional foreign key to "category" table',
            'if_proficient' => 'Can be chosen only if proficient',
            'quantity' => 'Quantity',
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
     * Gets query for [[Endowment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEndowment() {
        return $this->hasOne(ClassEndowment::class, ['id' => 'endowment_id']);
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
