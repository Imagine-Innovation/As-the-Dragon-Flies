<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_plot_npc".
 *
 * @property int $npc_id Foreign key to "npc" table
 * @property int $plot_id Foreign key to "story_plot" table
 * @property string $name NPC name
 *
 * @property Npc $npc
 * @property StoryPlot $plot
 */
class StoryPlotNpc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'story_plot_npc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['npc_id', 'plot_id', 'name'], 'required'],
            [['npc_id', 'plot_id'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['npc_id', 'plot_id'], 'unique', 'targetAttribute' => ['npc_id', 'plot_id']],
            [['plot_id'], 'exist', 'skipOnError' => true, 'targetClass' => StoryPlot::class, 'targetAttribute' => ['plot_id' => 'id']],
            [['npc_id'], 'exist', 'skipOnError' => true, 'targetClass' => Npc::class, 'targetAttribute' => ['npc_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'npc_id' => 'Foreign key to \"npc\" table',
            'plot_id' => 'Foreign key to \"story_plot\" table',
            'name' => 'NPC name',
        ];
    }

    /**
     * Gets query for [[Npc]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNpc() {
        return $this->hasOne(Npc::class, ['id' => 'npc_id']);
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
