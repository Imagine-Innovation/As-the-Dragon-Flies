<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "condition".
 *
 * @property int $id Primary key
 * @property string $name Condition name
 * @property string|null $description Short description
 *
 * @property CreatureImmunization[] $creatureImmunizations
 * @property Creature[] $creatures
 * @property QuestPlayerCondition[] $questPlayerConditions
 * @property QuestPlayer[] $quests
 */
class Condition extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'name' => 'Condition name',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[CreatureImmunizations]].
     *
     * @return \yii\db\ActiveQuery<CreatureImmunization>
     */
    public function getCreatureImmunizations()
    {
        return $this->hasMany(CreatureImmunization::class, ['condition_id' => 'id']);
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery<Creature>
     */
    public function getCreatures()
    {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->viaTable('creature_immunization', [
            'condition_id' => 'id',
        ]);
    }

    /**
     * Gets query for [[QuestPlayerConditions]].
     *
     * @return \yii\db\ActiveQuery<QuestPlayerCondition>
     */
    public function getQuestPlayerConditions()
    {
        return $this->hasMany(QuestPlayerCondition::class, ['condition_id' => 'id']);
    }

    /**
     * Gets query for [[Quests]].
     *
     * @return \yii\db\ActiveQuery<QuestPlayer>
     */
    public function getQuests()
    {
        return $this->hasMany(QuestPlayer::class, [
            'quest_id' => 'quest_id',
            'player_id' => 'player_id',
        ])->viaTable('quest_player_condition', ['condition_id' => 'id']);
    }
}
