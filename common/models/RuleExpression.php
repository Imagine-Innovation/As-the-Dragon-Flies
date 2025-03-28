<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "rule_expression".
 *
 * @property int $id Primary key.
 * @property int $rule_id Foreign key to "rule" table
 * @property int|null $parent_id Self-jointure to make a tree with different combination of the final conditions
 * @property string|null $op Boolean operator. Can be AND or OR
 *
 * @property RuleExpression $parent
 * @property Rule $rule
 * @property RuleCondition[] $ruleConditions
 * @property RuleExpression[] $ruleExpressions
 */
class RuleExpression extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'rule_expression';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['rule_id'], 'required'],
            [['rule_id', 'parent_id'], 'integer'],
            [['op'], 'string', 'max' => 3],
            [['rule_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rule::class, 'targetAttribute' => ['rule_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => RuleExpression::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key.',
            'rule_id' => 'Foreign key to \"rule\" table',
            'parent_id' => 'Self-jointure to make a tree with different combination of the final conditions',
            'op' => 'Boolean operator. Can be AND or OR',
        ];
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent() {
        return $this->hasOne(RuleExpression::class, ['id' => 'parent_id']);
    }

    /**
     * Gets query for [[Rule]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRule() {
        return $this->hasOne(Rule::class, ['id' => 'rule_id']);
    }

    /**
     * Gets query for [[RuleConditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRuleConditions() {
        return $this->hasMany(RuleCondition::class, ['expression_id' => 'id']);
    }

    /**
     * Gets query for [[RuleExpressions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRuleExpressions() {
        return $this->hasMany(RuleExpression::class, ['parent_id' => 'id']);
    }
}
