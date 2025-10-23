<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "action".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to “mission” table
 * @property int|null $action_type_id Optional foreign key to “action_type” table
 * @property int|null $passage_id Optional foreign key to “passage” table. Passage targeted by the action
 * @property int|null $decor_id Optional foreign key to “decor” table. Decor element involved in the action
 * @property int|null $decor_item_id Optional foreign key to “decor_item” table. Hidden item in the decor involved in the action
 * @property int|null $npc_id Optional foreign key to “npc” table. NPC involved in the action
 * @property int|null $reply_id Optional foreign key to “reply” table. First reply the player says
 * @property int|null $trap_id Optional foreign key to “trap” table. Trap involved in the action
 * @property int|null $required_item_id Optional foreign key to “Item” table. Required item to carry out the action
 * @property string $name Action to do
 * @property string|null $description Short description
 * @property int $dc Difficulty Class (DC)
 * @property int|null $partial_dc Optional Difficulty Class (DC) for partial success
 * @property int $is_single_action The action should not be played again
 * @property int $is_free Is a free action
 *
 * @property ActionFlow[] $triggers
 * @property ActionFlow[] $prerequisites
 * @property ActionType $actionType
 * @property Decor $decor
 * @property DecorItem $decorItem
 * @property Mission $mission
 * @property Action[] $nextActions
 * @property Npc $npc
 * @property Outcome[] $outcomes
 * @property Passage $passage
 * @property Action[] $previousActions
 * @property QuestAction[] $questActions
 * @property QuestProgress[] $questProgresses
 * @property Reply $reply
 * @property Item $requiredItem
 * @property Trap $trap
 */
class Action extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'action';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['action_type_id', 'passage_id', 'decor_id', 'decor_item_id', 'npc_id', 'reply_id', 'trap_id', 'required_item_id', 'description', 'partial_dc'], 'default', 'value' => null],
            [['dc'], 'default', 'value' => 10],
            [['is_single_action'], 'default', 'value' => 1],
            [['is_free'], 'default', 'value' => 0],
            [['mission_id', 'name'], 'required'],
            [['mission_id', 'action_type_id', 'passage_id', 'decor_id', 'decor_item_id', 'npc_id', 'reply_id', 'trap_id', 'required_item_id', 'dc', 'partial_dc', 'is_single_action', 'is_free'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
            [['passage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Passage::class, 'targetAttribute' => ['passage_id' => 'id']],
            [['reply_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reply::class, 'targetAttribute' => ['reply_id' => 'id']],
            [['npc_id'], 'exist', 'skipOnError' => true, 'targetClass' => Npc::class, 'targetAttribute' => ['npc_id' => 'id']],
            [['required_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['required_item_id' => 'id']],
            [['trap_id'], 'exist', 'skipOnError' => true, 'targetClass' => Trap::class, 'targetAttribute' => ['trap_id' => 'id']],
            [['decor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Decor::class, 'targetAttribute' => ['decor_id' => 'id']],
            [['decor_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => DecorItem::class, 'targetAttribute' => ['decor_item_id' => 'id']],
            [['action_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ActionType::class, 'targetAttribute' => ['action_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to “mission” table',
            'action_type_id' => 'Optional foreign key to “action_type” table',
            'passage_id' => 'Optional foreign key to “passage” table. Passage targeted by the action',
            'decor_id' => 'Optional foreign key to “decor” table. Decor element involved in the action',
            'decor_item_id' => 'Optional foreign key to “decor_item” table. Hidden item in the decor involved in the action',
            'npc_id' => 'Optional foreign key to “npc” table. NPC involved in the action',
            'reply_id' => 'Optional foreign key to “reply” table. First reply the player says',
            'trap_id' => 'Optional foreign key to “trap” table. Trap involved in the action',
            'required_item_id' => 'Optional foreign key to “Item” table. Required item to carry out the action',
            'name' => 'Action to do',
            'description' => 'Short description',
            'dc' => 'Difficulty Class (DC)',
            'partial_dc' => 'Optional Difficulty Class (DC) for partial success',
            'is_single_action' => 'The action should not be played again',
            'is_free' => 'Is a free action',
        ];
    }

    /**
     * Gets query for [[Triggers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTriggers() {
        return $this->hasMany(ActionFlow::class, ['previous_action_id' => 'id']);
    }

    /**
     * Gets query for [[Prerequisites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrerequisites() {
        return $this->hasMany(ActionFlow::class, ['next_action_id' => 'id']);
    }

    /**
     * Gets query for [[ActionType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActionType() {
        return $this->hasOne(ActionType::class, ['id' => 'action_type_id']);
    }

    /**
     * Gets query for [[Decor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecor() {
        return $this->hasOne(Decor::class, ['id' => 'decor_id']);
    }

    /**
     * Gets query for [[DecorItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecorItem() {
        return $this->hasOne(DecorItem::class, ['id' => 'decor_item_id']);
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
     * Gets query for [[NextActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNextActions() {
        return $this->hasMany(Action::class, ['id' => 'next_action_id'])->viaTable('action_flow', ['previous_action_id' => 'id']);
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
     * Gets query for [[Outcomes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOutcomes() {
        return $this->hasMany(Outcome::class, ['action_id' => 'id']);
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
     * Gets query for [[PreviousActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreviousActions() {
        return $this->hasMany(Action::class, ['id' => 'previous_action_id'])->viaTable('action_flow', ['next_action_id' => 'id']);
    }

    /**
     * Gets query for [[QuestActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestActions() {
        return $this->hasMany(QuestAction::class, ['action_id' => 'id']);
    }

    /**
     * Gets query for [[QuestProgresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestProgresses() {
        return $this->hasMany(QuestProgress::class, ['id' => 'quest_progress_id'])->viaTable('quest_action', ['action_id' => 'id']);
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
     * Gets query for [[RequiredItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequiredItem() {
        return $this->hasOne(Item::class, ['id' => 'required_item_id']);
    }

    /**
     * Gets query for [[Trap]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTrap() {
        return $this->hasOne(Trap::class, ['id' => 'trap_id']);
    }
}
