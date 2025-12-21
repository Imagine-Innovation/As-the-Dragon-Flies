<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use common\models\RuleExpression;

class RuleParsingTree extends Widget
{

    public $id;

    public function run() {
        if (!$this->id) {
            return '';
        }

        $model = RuleExpression::findOne(['rule_id' => $this->id, 'parent_id' => null]);

        return $model ? $this->digParsingTree($model) : '';
    }

    private function digParsingTree($expression) {
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

    private function renderCondition($cond) {
        $ruleModel = $cond->model;
        $left = $ruleModel->name . '->' . $ruleModel->attribute . ($ruleModel->is_method ? '()' : '');
        return '<li>[' . $left . ' ' . $cond->comparator . ' ' . $cond->val . '] = ' . ($cond->target ? 'True' : 'False') . '</li>';
    }
}
