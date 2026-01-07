<?php

namespace common\components;

use yii\base\Component;

/**
 * Represents a node in the rule's abstract syntax tree (AST).
 */
class RuleNode extends Component
{

    /** @var string The node type (e.g., 'condition', 'negate', 'booleanExpression') */
    public string $type;

    /** @var RuleNode|null For nested expressions or negations */
    public ?RuleNode $node = null;
    // --- Condition Properties ---
    public ?string $component = null;
    public ?string $comparator = null;
    public ?string $value = null;
    // --- Boolean Expression Properties ---
    public ?RuleNode $left = null;

    /** @var 'and'|'or'|null */
    public ?string $boolOperator = null;
    public ?RuleNode $right = null;
}
