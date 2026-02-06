<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "background_item".
 *
 * @property int $background_id Foreign key to “background” table
 * @property int|null $item_id Optional foreign key to “item” table
 * @property int|null $category_id Optional foreign key to “category” table
 * @property int $quantity Quantity
 * @property int|null $funding Initial funding
 * @property string|null $coin Coin
 *
 * @property Background $background
 * @property Category $category
 * @property Item $item
 */
class BackgroundItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'background_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_id', 'category_id', 'funding', 'coin'], 'default', 'value' => null],
            [['quantity'], 'default', 'value' => 1],
            [['background_id'], 'required'],
            [['background_id', 'item_id', 'category_id', 'quantity', 'funding'], 'integer'],
            [['coin'], 'string', 'max' => 2],
            [
                ['background_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Background::class,
                'targetAttribute' => ['background_id' => 'id'],
            ],
            [
                ['item_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Item::class,
                'targetAttribute' => ['item_id' => 'id'],
            ],
            [
                ['category_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::class,
                'targetAttribute' => ['category_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'background_id' => 'Foreign key to “background” table',
            'item_id' => 'Optional foreign key to “item” table',
            'category_id' => 'Optional foreign key to “category” table',
            'quantity' => 'Quantity',
            'funding' => 'Initial funding',
            'coin' => 'Coin',
        ];
    }

    /**
     * Gets query for [[Background]].
     *
     * @return \yii\db\ActiveQuery<Background>
     */
    public function getBackground()
    {
        return $this->hasOne(Background::class, ['id' => 'background_id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery<Category>
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
    }
}
