<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "action_type".
 *
 * @property int $id Primary key
 * @property string $name Action type
 * @property string|null $description Short description
 * @property string|null $icon Icon
 *
 * @property ActionTypeSkill[] $actionTypeSkills
 * @property Action[] $actions
 * @property Skill[] $skills
 */
class ActionType extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'action_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'icon'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description'], 'string'],
            [['name', 'icon'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Action type',
            'description' => 'Short description',
            'icon' => 'Icon',
        ];
    }

    /**
     * Gets query for [[ActionTypeSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActionTypeSkills() {
        return $this->hasMany(ActionTypeSkill::class, ['action_type_id' => 'id']);
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActions() {
        return $this->hasMany(Action::class, ['action_type_id' => 'id']);
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['id' => 'skill_id'])->viaTable('action_type_skill', ['action_type_id' => 'id']);
    }

}
