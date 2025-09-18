<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "dialog".
 *
 * @property int $id Primary key
 * @property int|null $mission_id Foreign key to "mission_npc" table
 * @property int|null $npc_id Foreign key to "mission_npc" table
 * @property string $caption What the NPC looks like
 * @property string $text What the NPC says
 *
 * @property MissionNpc $npc
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
            [['mission_id', 'npc_id'], 'default', 'value' => null],
            [['mission_id', 'npc_id'], 'integer'],
            [['caption', 'text'], 'required'],
            [['caption', 'text'], 'string'],
            [['npc_id', 'mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => MissionNpc::class, 'targetAttribute' => ['npc_id' => 'npc_id', 'mission_id' => 'mission_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to \"mission_npc\" table',
            'npc_id' => 'Foreign key to \"mission_npc\" table',
            'caption' => 'What the NPC looks like',
            'text' => 'What the NPC says',
        ];
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
