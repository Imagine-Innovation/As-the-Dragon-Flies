<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "npc_type".
 *
 * @property int $id Primary key
 * @property string $name NPC type
 * @property string|null $description Short description
 * @property int|null $hp Hit Points: The average amount of damage a NPC can withstand before being defeated
 * @property string|null $hit_dice The dice roll that determines the damage inflicted by a NPC
 * @property float $cr Challenge Rating. This is a measure of how difficult the NPC is to defeat for a party of adventurers
 * @property int $bonus Proficiency bonus determined by the creature’s challenge rating
 * @property int $xp Experience Points awarded to the party for defeating the creature
 *
 * @property Npc[] $npcs
 */
class NpcType extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'npc_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'hp', 'hit_dice'], 'default', 'value' => null],
            [['cr'], 'default', 'value' => 0.000],
            [['xp'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['hp', 'bonus', 'xp'], 'integer'],
            [['cr'], 'number'],
            [['name'], 'string', 'max' => 64],
            [['hit_dice'], 'string', 'max' => 16],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'name' => 'NPC type',
            'description' => 'Short description',
            'hp' => 'Hit Points: The average amount of damage a NPC can withstand before being defeated',
            'hit_dice' => 'The dice roll that determines the damage inflicted by a NPC',
            'cr' => 'Challenge Rating. This is a measure of how difficult the NPC is to defeat for a party of adventurers',
            'bonus' => 'Proficiency bonus determined by the creature’s challenge rating',
            'xp' => 'Experience Points awarded to the party for defeating the creature',
        ];
    }

    /**
     * Gets query for [[Npcs]].
     *
     * @return \yii\db\ActiveQuery<Npc>
     */
    public function getNpcs()
    {
        return $this->hasMany(Npc::class, ['npc_type_id' => 'id']);
    }
}
