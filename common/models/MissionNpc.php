<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "mission_npc".
 *
 * @property int $npc_id Foreign key to "npc" table
 * @property int $mission_id Foreign key to "mission" table
 * @property string $name NPC name
 * @property string|null $description Short description
 * @property string|null $image Image
 *
 * @property Dialog[] $dialogs
 * @property Interaction[] $interactions
 * @property Mission $mission
 * @property Npc $npc
 */
class MissionNpc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'mission_npc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['npc_id', 'mission_id', 'name'], 'required'],
            [['npc_id', 'mission_id'], 'integer'],
            [['description'], 'string'],
            [['name', 'image'], 'string', 'max' => 32],
            [['npc_id', 'mission_id'], 'unique', 'targetAttribute' => ['npc_id', 'mission_id']],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
            [['npc_id'], 'exist', 'skipOnError' => true, 'targetClass' => Npc::class, 'targetAttribute' => ['npc_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'npc_id' => 'Foreign key to "npc" table',
            'mission_id' => 'Foreign key to "mission" table',
            'name' => 'NPC name',
            'description' => 'Short description',
            'image' => 'Image',
        ];
    }

    /**
     * Gets query for [[Dialogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDialogs() {
        return $this->hasMany(Dialog::class, ['npc_id' => 'npc_id', 'mission_id' => 'mission_id']);
    }

    /**
     * Gets query for [[Interactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInteractions() {
        return $this->hasMany(Interaction::class, ['npc_id' => 'npc_id', 'mission_id' => 'mission_id']);
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

}
