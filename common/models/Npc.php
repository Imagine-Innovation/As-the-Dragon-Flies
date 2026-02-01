<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "npc".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to “mission” table
 * @property int $npc_type_id Foreign key to “npc_type” table. Defaulted to “Commoner” (id=7)
 * @property int $language_id Foreign key to “language” table. Defaulted to “Common” (id=1)
 * @property string $name NPC name
 * @property string|null $description Short description
 * @property string|null $image Image
 * @property int|null $first_dialog_id Optional foreign key to “dialog” table
 *
 * @property Action[] $actions
 * @property Dialog[] $dialogs
 * @property Dialog $firstDialog
 * @property Language $language
 * @property Mission $mission
 * @property NpcType $npcType
 */
class Npc extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'npc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'image', 'first_dialog_id'], 'default', 'value' => null],
            [['npc_type_id'], 'default', 'value' => 7],
            [['language_id'], 'default', 'value' => 1],
            [['mission_id', 'name'], 'required'],
            [['mission_id', 'npc_type_id', 'language_id', 'first_dialog_id'], 'integer'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name', 'image'], 'string', 'max' => 64],
            [['npc_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => NpcType::class, 'targetAttribute' => ['npc_type_id' => 'id']],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
            [['first_dialog_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dialog::class, 'targetAttribute' => ['first_dialog_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to “mission” table',
            'npc_type_id' => 'Foreign key to “npc_type” table. Defaulted to “Commoner” (id=7)',
            'language_id' => 'Foreign key to “language” table. Defaulted to “Common” (id=1)',
            'name' => 'NPC name',
            'description' => 'Short description',
            'image' => 'Image',
            'first_dialog_id' => 'Optional foreign key to “dialog” table',
        ];
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery<Action>
     */
    public function getActions()
    {
        return $this->hasMany(Action::class, ['npc_id' => 'id']);
    }

    /**
     * Gets query for [[Dialogs]].
     *
     * @return \yii\db\ActiveQuery<Dialog>
     */
    public function getDialogs()
    {
        return $this->hasMany(Dialog::class, ['npc_id' => 'id']);
    }

    /**
     * Gets query for [[FirstDialog]].
     *
     * @return \yii\db\ActiveQuery<Dialog>
     */
    public function getFirstDialog()
    {
        return $this->hasOne(Dialog::class, ['id' => 'first_dialog_id']);
    }

    /**
     * Gets query for [[Language]].
     *
     * @return \yii\db\ActiveQuery<Language>
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    /**
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery<Mission>
     */
    public function getMission()
    {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

    /**
     * Gets query for [[NpcType]].
     *
     * @return \yii\db\ActiveQuery<NpcType>
     */
    public function getNpcType()
    {
        return $this->hasOne(NpcType::class, ['id' => 'npc_type_id']);
    }
}
