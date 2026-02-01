<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "rule_action".
 *
 * @property int $id Primary key.
 * @property int $model_id Foreign key to “rule_component” table.
 * @property int $rule_id Foreign key to “rule” table.
 * @property string $name Action
 * @property string|null $description Short description of the expected action
 *
 * @property RuleModel $model
 * @property Rule $rule
 */
class RuleAction extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rule_action';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['model_id', 'rule_id', 'name'], 'required'],
            [['model_id', 'rule_id'], 'integer'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name'], 'string', 'max' => 64],
            [['rule_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rule::class, 'targetAttribute' => ['rule_id' => 'id']],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => RuleModel::class, 'targetAttribute' => ['model_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key.',
            'model_id' => 'Foreign key to “rule_component” table.',
            'rule_id' => 'Foreign key to “rule” table.',
            'name' => 'Action',
            'description' => 'Short description of the expected action',
        ];
    }

    /**
     * Gets query for [[Model]].
     *
     * @return \yii\db\ActiveQuery<RuleModel>
     */
    public function getModel()
    {
        return $this->hasOne(RuleModel::class, ['id' => 'model_id']);
    }

    /**
     * Gets query for [[Rule]].
     *
     * @return \yii\db\ActiveQuery<Rule>
     */
    public function getRule()
    {
        return $this->hasOne(Rule::class, ['id' => 'rule_id']);
    }
}
