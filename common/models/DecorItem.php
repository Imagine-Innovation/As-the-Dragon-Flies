<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "decor_item".
 *
 * @property int $id Primary key
 * @property int $decor_id Foreign key to “decor” table
 * @property int $item_id Foreign key to “item” table
 * @property string $name Item name in the mission
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property int $found The percentage chance that the item will be found
 * @property int $identified The percentage chance that the item will be recognized
 *
 * @property Action[] $actions
 * @property Decor $decor
 * @property Item $item
 */
class DecorItem extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'decor_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['found'], 'default', 'value' => 25],
            [['identified'], 'default', 'value' => 50],
            [['decor_id', 'item_id', 'name'], 'required'],
            [['decor_id', 'item_id', 'found', 'identified'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 64],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['decor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Decor::class, 'targetAttribute' => ['decor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'decor_id' => 'Foreign key to “decor” table',
            'item_id' => 'Foreign key to “item” table',
            'name' => 'Item name in the mission',
            'description' => 'Short description',
            'image' => 'Image',
            'found' => 'The percentage chance that the item will be found',
            'identified' => 'The percentage chance that the item will be recognized',
        ];
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery<Action>
     */
    public function getActions() {
        return $this->hasMany(Action::class, ['decor_item_id' => 'id']);
    }

    /**
     * Gets query for [[Decor]].
     *
     * @return \yii\db\ActiveQuery<Decor>
     */
    public function getDecor() {
        return $this->hasOne(Decor::class, ['id' => 'decor_id']);
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
