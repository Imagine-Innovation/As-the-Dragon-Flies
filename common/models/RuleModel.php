<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "rule_model".
 *
 * @property int $id Primary key.
 * @property string $path Full path to the model as a starting point. Can be “app\models”, “app\components”...
 * @property string $name Rule Model
 * @property string $attribute Property or method within the root model. For instance, when model is “Player”, can be “name” to retrieve the player's name or “race->name” to retrieve the player's race name.
 * @property int $is_method Indicates that the “attribute” field contains a method name, not a property name.
 *
 * @property RuleAction[] $ruleActions
 * @property RuleCondition[] $ruleConditions
 */
class RuleModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rule_model';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['path'], 'default', 'value' => 'appmodels'],
            [['is_method'], 'default', 'value' => 0],
            [['name', 'attribute'], 'required'],
            [['is_method'], 'integer'],
            [['path', 'name', 'attribute'], 'string', 'max' => 64],
            [['name', 'attribute'], 'unique', 'targetAttribute' => ['name', 'attribute']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key.',
            'path' => 'Full path to the model as a starting point. Can be “app\\models”, “app\\components”...',
            'name' => 'Rule Model',
            'attribute' => 'Property or method within the root model. For instance, when model is “Player”, can be “name” to retrieve the player\'s name or “race->name” to retrieve the player\'s race name.',
            'is_method' => 'Indicates that the “attribute” field contains a method name, not a property name.',
        ];
    }

    /**
     * Gets query for [[RuleActions]].
     *
     * @return \yii\db\ActiveQuery<RuleAction>
     */
    public function getRuleActions()
    {
        return $this->hasMany(RuleAction::class, ['model_id' => 'id']);
    }

    /**
     * Gets query for [[RuleConditions]].
     *
     * @return \yii\db\ActiveQuery<RuleCondition>
     */
    public function getRuleConditions()
    {
        return $this->hasMany(RuleCondition::class, ['model_id' => 'id']);
    }
}
