<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_plot_item".
 *
 * @property int $plot_id Foreign key to "story_plot" table
 * @property int $item_id Foreign key to "item" table
 * @property string $name Item name in the plot
 * @property string|null $description Short description
 * @property int $found The percentage chance that the item will be found
 * @property int $identified The percentage chance that the item will be recognized
 *
 * @property Item $item
 * @property StoryPlot $plot
 */
class StoryPlotItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'story_plot_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['found'], 'default', 'value' => 25],
            [['identified'], 'default', 'value' => 50],
            [['plot_id', 'item_id', 'name'], 'required'],
            [['plot_id', 'item_id', 'found', 'identified'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['plot_id', 'item_id'], 'unique', 'targetAttribute' => ['plot_id', 'item_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['plot_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoryPlot::class, 'targetAttribute' => ['plot_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'plot_id' => 'Foreign key to \"story_plot\" table',
            'item_id' => 'Foreign key to \"item\" table',
            'name' => 'Item name in the plot',
            'description' => 'Short description',
            'found' => 'The percentage chance that the item will be found',
            'identified' => 'The percentage chance that the item will be recognized',
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
     * Gets query for [[Plot]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlot() {
        return $this->hasOne(StoryPlot::class, ['id' => 'plot_id']);
    }

}
