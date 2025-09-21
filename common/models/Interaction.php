<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "interaction".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to "mission" table
 * @property int|null $skill_id Optional foreign key to "skill" table
 * @property int|null $passage_id Optional foreign key to "passage" table
 * @property int|null $npc_id Optional foreign key to "mission_npc" table
 * @property int|null $reply_id Optional foreign key to "reply" table
 * @property string $name Action to do
 * @property string|null $icon Icon
 * @property string $action_type Action type (search, speak, use...)
 * @property int $dc Difficulty Class (DC)
 *
 * @property InteractionItem[] $interactionItems
 * @property Item[] $items
 * @property Mission $mission
 * @property MissionNpc $npc
 * @property Passage $passage
 * @property Reply $reply
 * @property Skill $skill
 * @property Success[] $successes
 */
class Interaction extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'interaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['skill_id', 'passage_id', 'npc_id', 'reply_id', 'icon'], 'default', 'value' => null],
            [['mission_id', 'name', 'action_type', 'dc'], 'required'],
            [['mission_id', 'skill_id', 'passage_id', 'npc_id', 'reply_id', 'dc'], 'integer'],
            [['name', 'icon', 'action_type'], 'string', 'max' => 32],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
            [['skill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::class, 'targetAttribute' => ['skill_id' => 'id']],
            [['passage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Passage::class, 'targetAttribute' => ['passage_id' => 'id']],
            [['reply_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reply::class, 'targetAttribute' => ['reply_id' => 'id']],
            [['npc_id', 'mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => MissionNpc::class, 'targetAttribute' => ['npc_id' => 'npc_id', 'mission_id' => 'mission_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to \"mission\" table',
            'skill_id' => 'Optional foreign key to \"skill\" table',
            'passage_id' => 'Optional foreign key to \"passage\" table',
            'npc_id' => 'Optional foreign key to \"mission_npc\" table',
            'reply_id' => 'Optional foreign key to \"reply\" table',
            'name' => 'Action to do',
            'icon' => 'Icon',
            'action_type' => 'Action type (search, speak, use...)',
            'dc' => 'Difficulty Class (DC)',
        ];
    }

    /**
     * Gets query for [[InteractionItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInteractionItems() {
        return $this->hasMany(InteractionItem::class, ['interaction_id' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->viaTable('interaction_item', ['interaction_id' => 'id']);
    }

    /**
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMission() {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

    /**
     * Gets query for [[Npc]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNpc() {
        return $this->hasOne(MissionNpc::class, ['npc_id' => 'npc_id', 'mission_id' => 'mission_id']);
    }

    /**
     * Gets query for [[Passage]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPassage() {
        return $this->hasOne(Passage::class, ['id' => 'passage_id']);
    }

    /**
     * Gets query for [[Reply]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReply() {
        return $this->hasOne(Reply::class, ['id' => 'reply_id']);
    }

    /**
     * Gets query for [[Skill]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkill() {
        return $this->hasOne(Skill::class, ['id' => 'skill_id']);
    }

    /**
     * Gets query for [[Successes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSuccesses() {
        return $this->hasMany(Success::class, ['interaction_id' => 'id']);
    }

}
