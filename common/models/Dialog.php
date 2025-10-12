<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "dialog".
 *
 * @property int $id Primary key
 * @property int $npc_id Foreign key to “npc” table
 * @property string $text What the NPC says
 * @property int|null $outcome_id Optional foreign key to “success” table
 *
 * @property Npc $npc
 * @property Npc[] $npcs
 * @property Outcome $outcome
 * @property Reply[] $replies
 * @property Reply[] $replies0
 */
class Dialog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'dialog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['outcome_id'], 'default', 'value' => null],
            [['npc_id', 'text'], 'required'],
            [['npc_id', 'outcome_id'], 'integer'],
            [['text'], 'string'],
            [['npc_id'], 'exist', 'skipOnError' => true, 'targetClass' => Npc::class, 'targetAttribute' => ['npc_id' => 'id']],
            [['outcome_id'], 'exist', 'skipOnError' => true, 'targetClass' => Outcome::class, 'targetAttribute' => ['outcome_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'npc_id' => 'Foreign key to “npc” table',
            'text' => 'What the NPC says',
            'outcome_id' => 'Optional foreign key to “success” table',
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
     * Gets query for [[Npcs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNpcs() {
        return $this->hasMany(Npc::class, ['first_dialog_id' => 'id']);
    }

    /**
     * Gets query for [[Outcome]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOutcome() {
        return $this->hasOne(Outcome::class, ['id' => 'outcome_id']);
    }

    /**
     * Gets query for [[Replies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReplies() {
        return $this->hasMany(Reply::class, ['dialog_id' => 'id']);
    }

    /**
     * Gets query for [[Replies0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReplies0() {
        return $this->hasMany(Reply::class, ['next_dialog_id' => 'id']);
    }

}
