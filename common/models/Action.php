<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "action".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to “mission” table
 * @property int|null $passage_id Optional foreign key to “passage” table. Passage targeted by the action
 * @property int|null $decor_id Optional foreign key to “decor” table. Decor element involved in the action
 * @property int|null $npc_id Optional foreign key to “npc” table. NPC involved in the action
 * @property int|null $reply_id Optional foreign key to “reply” table. First reply the player says
 * @property int|null $item_id Optional foreign key to “decor_item” table. Hidden item in the decor involved in the action
 * @property int|null $trap_id Optional foreign key to “trap” table. Trap involved in the action
 * @property int|null $required_item_id Optional foreign key to “Item” table. Required item to carry out the action
 * @property int|null $skill_id Optional foreign key to “skill” table. Required skill to assess
 * @property string $name Action to do
 * @property string $action_type Action type (search, speak, use...)
 * @property int $dc Difficulty Class (DC)
 *
 * @property Decor $decor
 * @property DecorItem $item
 * @property Mission $mission
 * @property Npc $npc
 * @property Passage $passage
 * @property Reply $reply
 * @property Item $requiredItem
 * @property Skill $skill
 * @property Success[] $successes
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
            [['passage_id', 'decor_id', 'npc_id', 'reply_id', 'item_id', 'trap_id', 'required_item_id', 'skill_id'], 'default', 'value' => null],
            [['dc'], 'default', 'value' => 0],
            [['mission_id', 'name', 'action_type'], 'required'],
            [['mission_id', 'passage_id', 'decor_id', 'npc_id', 'reply_id', 'item_id', 'trap_id', 'required_item_id', 'skill_id', 'dc'], 'integer'],
            [['name', 'action_type'], 'string', 'max' => 32],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
            [['skill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Skill::class, 'targetAttribute' => ['skill_id' => 'id']],
            [['passage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Passage::class, 'targetAttribute' => ['passage_id' => 'id']],
            [['reply_id'], 'exist', 'skipOnError' => true, 'targetClass' => Reply::class, 'targetAttribute' => ['reply_id' => 'id']],
            [['npc_id'], 'exist', 'skipOnError' => true, 'targetClass' => Npc::class, 'targetAttribute' => ['npc_id' => 'id']],
            [['required_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['required_item_id' => 'id']],
            [['trap_id'], 'exist', 'skipOnError' => true, 'targetClass' => Trap::class, 'targetAttribute' => ['trap_id' => 'id']],
            [['decor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Decor::class, 'targetAttribute' => ['decor_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => DecorItem::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to “mission” table',
            'passage_id' => 'Optional foreign key to “passage” table. Passage targeted by the action',
            'decor_id' => 'Optional foreign key to “decor” table. Decor element involved in the action',
            'npc_id' => 'Optional foreign key to “npc” table. NPC involved in the action',
            'reply_id' => 'Optional foreign key to “reply” table. First reply the player says',
            'item_id' => 'Optional foreign key to “decor_item” table. Hidden item in the decor involved in the action',
            'trap_id' => 'Optional foreign key to “trap” table. Trap involved in the action',
            'required_item_id' => 'Optional foreign key to “Item” table. Required item to carry out the action',
            'skill_id' => 'Optional foreign key to “skill” table. Required skill to assess',
            'name' => 'Action to do',
            'action_type' => 'Action type (search, speak, use...)',
            'dc' => 'Difficulty Class (DC)',
        ];
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
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem() {
        return $this->hasOne(DecorItem::class, ['id' => 'item_id']);
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
        return $this->hasOne(Npc::class, ['id' => 'npc_id']);
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
     * Gets query for [[RequiredItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequiredItem() {
        return $this->hasOne(Item::class, ['id' => 'required_item_id']);
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
        return $this->hasMany(Success::class, ['action_id' => 'id']);
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
