<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_plot".
 *
 * @property int $id Primary key
 * @property int $step_id Foreign key to "step" table
 * @property string $name Plot name
 * @property string|null $description Short description
 * @property int $low_bound Low bound of probability that history will go in this direction
 * @property int $high_bound High bound of probability that history will go in this direction
 *
 * @property Item[] $items
 * @property Npc[] $npcs
 * @property PlotItem[] $plotItems
 * @property PlotNpc[] $plotNpcs
 * @property Shape[] $shapes
 * @property Step $step
 * @property StoryPlotShape[] $storyPlotShapes
 */
class StoryPlot extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'story_plot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['low_bound'], 'default', 'value' => 0],
            [['high_bound'], 'default', 'value' => 100],
            [['step_id', 'name'], 'required'],
            [['step_id', 'low_bound', 'high_bound'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['step_id'], 'exist', 'skipOnError' => true, 'targetClass' => Step::class, 'targetAttribute' => ['step_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'step_id' => 'Foreign key to \"step\" table',
            'name' => 'Plot name',
            'description' => 'Short description',
            'low_bound' => 'Low bound of probability that history will go in this direction',
            'high_bound' => 'High bound of probability that history will go in this direction',
        ];
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->viaTable('plot_item', ['plot_id' => 'id']);
    }

    /**
     * Gets query for [[Npcs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNpcs() {
        return $this->hasMany(Npc::class, ['id' => 'npc_id'])->viaTable('plot_npc', ['plot_id' => 'id']);
    }

    /**
     * Gets query for [[PlotItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlotItems() {
        return $this->hasMany(PlotItem::class, ['plot_id' => 'id']);
    }

    /**
     * Gets query for [[PlotNpcs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlotNpcs() {
        return $this->hasMany(PlotNpc::class, ['plot_id' => 'id']);
    }

    /**
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapes() {
        return $this->hasMany(Shape::class, ['id' => 'shape_id'])->viaTable('story_plot_shape', ['plot_id' => 'id']);
    }

    /**
     * Gets query for [[Step]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStep() {
        return $this->hasOne(Step::class, ['id' => 'step_id']);
    }

    /**
     * Gets query for [[StoryPlotShapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStoryPlotShapes() {
        return $this->hasMany(StoryPlotShape::class, ['plot_id' => 'id']);
    }

}
