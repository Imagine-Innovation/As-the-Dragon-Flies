<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "npc".
 *
 * @property int $id Primary key
 * @property string $name Non playing character
 * @property string|null $description Short description
 * @property int|null $hp Hit Points: The average amount of damage a NPC can withstand before being defeated
 * @property string|null $hit_dice The dice roll that determines the damage inflicted by a NPC
 * @property float $cr Challenge Rating. This is a measure of how difficult the NPC is to defeat for a party of adventurers
 * @property int $bonus Proficiency bonus determined by the creature’s challenge rating
 * @property int $xp Experience Points awarded to the party for defeating the creature
 *
 * @property PlotNpc[] $plotNpcs
 * @property StoryPlot[] $plots
 */
class Npc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'npc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'hp', 'hit_dice'], 'default', 'value' => null],
            [['cr'], 'default', 'value' => 0.000],
            [['xp'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['description'], 'string'],
            [['hp', 'bonus', 'xp'], 'integer'],
            [['cr'], 'number'],
            [['name'], 'string', 'max' => 32],
            [['hit_dice'], 'string', 'max' => 16],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Non playing character',
            'description' => 'Short description',
            'hp' => 'Hit Points: The average amount of damage a NPC can withstand before being defeated',
            'hit_dice' => 'The dice roll that determines the damage inflicted by a NPC',
            'cr' => 'Challenge Rating. This is a measure of how difficult the NPC is to defeat for a party of adventurers',
            'bonus' => 'Proficiency bonus determined by the creature’s challenge rating',
            'xp' => 'Experience Points awarded to the party for defeating the creature',
        ];
    }

    /**
     * Gets query for [[PlotNpcs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlotNpcs() {
        return $this->hasMany(PlotNpc::class, ['npc_id' => 'id']);
    }

    /**
     * Gets query for [[Plots]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlots() {
        return $this->hasMany(StoryPlot::class, ['id' => 'plot_id'])->viaTable('plot_npc', ['npc_id' => 'id']);
    }

}
