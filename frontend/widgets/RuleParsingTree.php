<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use common\models\RuleCondition;
use common\models\RuleExpression;

class RuleParsingTree extends Widget
{

    public int $id;

    /**
     *
     * @return string
     */
    public function run(): string {
        if (!$this->id) {
            return '';
        }

        $model = RuleExpression::findOne(['rule_id' => $this->id, 'parent_id' => null]);

        return $model ? $this->digParsingTree($model) : '';
    }

    /**
     *
     * @param RuleExpression $expression
     * @return string
     */
    private function digParsingTree(RuleExpression $expression): string {
        $tree = [];
        if ($expression->op) {
            if ($expression->ruleConditions) {
                foreach ($expression->ruleConditions as $cond) {
                    $tree[] = $this->renderCondition($cond);
                }
            }
            if ($expression->ruleExpressions) {
                foreach ($expression->ruleExpressions as $expr) {
                    $tree[] = '<li>(' . $this->digParsingTree($expr) . ')</li>';
                }
            }
            if ($expression->op === 'not') {
                $ul = '<ul style="list-style-type: none;">' . implode('', $tree) . '</ul>';
                $html = '<ul style="list-style-type: none;"><li>not</li>' . $ul . '</ul>';
            } else {
                $separator = '<li>' . $expression->op . '</li>';
                $html = '<ul style="list-style-type: none;">' . implode($separator, $tree) . '</ul>';
            }
        } else {
            if ($expression->ruleExpressions) {
                foreach ($expression->ruleExpressions as $expr) {
                    $tree[] = '<li>(' . $this->digParsingTree($expr) . ')</li>';
                }
                $html = '<ul style="list-style-type: none;">' . implode('', $tree) . '</ul>';
            } else {
                $cond = $expression->ruleConditions[0];
                $html = $this->renderCondition($cond);
            }
        }
        return $html;
    }

    /**
     *
     * @param RuleCondition $cond
     * @return string
     */
    private function renderCondition(RuleCondition $cond): string {
        $ruleModel = $cond->model;
        $attribute = $ruleModel->is_method ? "{$ruleModel->attribute}()" : $ruleModel->attribute;
        $left = "{$ruleModel->name}->{$attribute}";
        $target = $cond->target ? 'True' : 'False';

        return "<li>[{$left} {$cond->comparator} {$cond->val}] = {$target}</li>";
    }
}
