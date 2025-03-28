<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "rule_condition".
 *
 * @property int $id Primary key.
 * @property int $rule_id Foreign key to the "rule" table
 * @property int $expression_id Foreign key to the "rule_expression" table
 * @property int $model_id Foreign key to the "rule_component" table
 * @property string|null $method_param If the component is a method, store the parameter value. For instance: method_param="2D8" for the component containing the method "RollDice"
 * @property string $comparator Comparator to be used: "<", "<=", "==", ">=", or ">"
 * @property string $val Value to be compared at
 * @property int $target Flag that indicates whether the condition should be fulfilled (target=true) or not (target=false)
 *
 * @property RuleExpression $expression
 * @property RuleModel $model
 * @property Rule $rule
 */
class RuleCondition extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'rule_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['rule_id', 'expression_id', 'model_id', 'comparator', 'val'], 'required'],
            [['rule_id', 'expression_id', 'model_id', 'target'], 'integer'],
            [['method_param', 'val'], 'string', 'max' => 64],
            [['comparator'], 'string', 'max' => 2],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => RuleModel::class, 'targetAttribute' => ['model_id' => 'id']],
            [['expression_id'], 'exist', 'skipOnError' => true, 'targetClass' => RuleExpression::class, 'targetAttribute' => ['expression_id' => 'id']],
            [['rule_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rule::class, 'targetAttribute' => ['rule_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key.',
            'rule_id' => 'Foreign key to the \"rule\" table',
            'expression_id' => 'Foreign key to the \"rule_expression\" table',
            'model_id' => 'Foreign key to the \"rule_component\" table',
            'method_param' => 'If the component is a method, store the parameter value. For instance: method_param=\"2D8\" for the component containing the method \"RollDice\"',
            'comparator' => 'Comparator to be used: \"<\", \"<=\", \"==\", \">=\", or \">\"',
            'val' => 'Value to be compared at',
            'target' => 'Flag that indicates whether the condition should be fulfilled (target=true) or not (target=false)',
        ];
    }

    /**
     * Gets query for [[Expression]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpression() {
        return $this->hasOne(RuleExpression::class, ['id' => 'expression_id']);
    }

    /**
     * Gets query for [[Model]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModel() {
        return $this->hasOne(RuleModel::class, ['id' => 'model_id']);
    }

    /**
     * Gets query for [[Rule]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRule() {
        return $this->hasOne(Rule::class, ['id' => 'rule_id']);
    }
}
