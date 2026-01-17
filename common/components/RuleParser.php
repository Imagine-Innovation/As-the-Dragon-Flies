<?php

namespace common\components;

use common\components\RuleNode;
use common\helpers\ModelHelper;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * RuleParser is a component for parsing rule strings and generating parsing trees.
 *
 * This component tokenizes the input rule string, parses it using a recursive
 * descent parser, and generates a parsing tree representing the structure of the rule.
 * It supports various operations such as parsing expressions, atomicConditions, components,
 * comparators, values, and more.
 *
 *
 * Grammar for the RuleParser component:
 *
 *    <rule>
 *    └── <expression>
 *        ├── <boolean-expression>
 *        │   ├── <expression>
 *        │   │   ├── <boolean-expression>
 *        │   │   └── <basic-condition>
 *        │   └── <expression>
 *        ├── <basic-condition>
 *        └── '(' <expression> ')'
 *
 *    <boolean-expression>
 *    └── <expression> <bool-operator> <expression>
 *
 *    <basic-condition>
 *    └── <object> <comparator> <value>
 *
 *    <object>
 *    ├── <property>
 *    │   └── <class-name> | <class-name> '->' <property>
 *    └── <method>
 *
 *    <method>
 *    └── <string> '(' <parameter-list> ')'
 *
 *    <parameter-list>
 *    └── <parameter> | <parameter> ',' <parameter-list>
 *
 *    <parameter>
 *    └── <string> | <number> | <quoted>
 *
 *    <comparator>
 *    └── '==' | '!=' | '<' | '>' | '<=' | '>=' | '<>' | '='
 *
 *    <bool-operator>
 *    └── 'AND' | 'OR'
 *
 *    <class-name>
 *    └── [A-Z][a-zA-Z0-9]*
 *
 *    <string>
 *    └── [a-zA-Z_][a-zA-Z0-9_]*
 *
 *    <number>
 *    └── \d+
 *
 *    <quoted>
 *    └── '([^']*)' | "([^"]*)"
 *
 */
class RuleParser extends Component
{

    const TOKEN_TYPE_REGEX = [
        'parenthesis' => "/^(\(|\))$/",
        'comparator' => "/^(<=|>=|<>|!=|==|=|<|>)\$/",
        'comparator2' => "/^[<>]$/",
        'and' => "/\\bAND\\b/i",
        'or' => "/\\bOR\\b/i",
        'not' => "/\\bNOT\\b/i",
        'comma' => "/^\,\$/",
        'className' => "/^[A-Z][a-zA-Z0-9]*$/",
        'string' => "/^[a-zA-Z_][a-zA-Z0-9_]*$/",
        'number' => "/^\d+$/",
        'quoted' => '/^([\'"])(.*?)\1$/',
    ];

    /** @var string Detailed error message if parsing fails */
    public string $errorMessage = '';

    /** @var RuleNode|null The resulting abstract syntax tree (AST) */
    public ?RuleNode $parsingTree = null;

    /** @var string The token or grammar rule the parser was looking for when it failed */
    public string $expected = '';

    /** @var list<array{type: string, value: string}> Internal token stream */
    private array $tokens = [];
    private int $tokenNb = 0;
    private int $pos = 0;
    private int $emergencyStop = 0;
    private int $nestingLevel = 0;

    /**
     * Parses a rule from the given input string.
     *
     * This function tokenizes the input rule string and stores the tokens. It then parses the rule using
     * a recursive descent parser. If parsing fails, an error message is generated.
     *
     * @param string $inputString The rule string to be parsed.
     * @return bool True if the rule is successfully parsed, false otherwise.
     */
    public function parseRule(string $inputString): bool {
        $this->tokens = $this->tokenize($inputString);

        $this->tokenNb = count($this->tokens);
        $this->parsingTree = null;

        $this->_debug("——> tokens = {$this->_parsingTokens($this->tokenNb - 1)}");

        $match = $this->rootExpression($inputString);

        if (!$match) {
            $this->errorMessage = $this->makeErrorMessage();
        }

        return $match;
    }

    /**
     * Parses the root expression of a rule.
     *
     * @param string $inputString The rule string to be parsed.
     * @return bool True if the root expression is successfully parsed, false otherwise.
     */
    private function rootExpression(string $inputString): bool {
        $match = $this->expression($this->parsingTree);
        if ($match) {
            $this->_debug("'{$inputString}' is a valid rule");
        }
        return $match;
    }

    /**
     * Parses an expression and updates the provided node reference.
     *
     * @param RuleNode|null &$expression Reference to be populated with the parsed node.
     * @return bool True if a valid expression is found.
     */
    private function expression(?RuleNode &$expression): bool {
        $context = $this->setRecoveryPoint('expression');
        $expr = null;

        // Handle NOT negation
        if ($this->nextTokenType() === 'not') {
            $this->getToken();
            $this->_debug("Negative expression, next token={$this->nextToken()}");

            $match = $this->expression($expr);

            $node = new RuleNode(['type' => 'negate', 'node' => $expr]);

            return $this->parsingStatus($match, $expression, $node, $context);
        }

        // Try boolean expression first, then fallback to basic condition
        $match = $this->booleanExpression($expr) ? true : $this->basicCondition($expr);
        if ($match) {
            $expression = $expr;
        } else {
            $this->rewind($context);
        }
        return $match;
    }

    /**
     * Parses a nested expression enclosed in parentheses.
     *
     * @param RuleNode|null &$expression
     * @return bool True if a valid nested expression is found.
     */
    private function nestedExpression(?RuleNode &$expression): bool {
        $context = $this->setRecoveryPoint('nestedExpression');
        $expr = null;

        $match = ($this->getToken() === '(');
        if ($match) {
            $match = $this->expression($expr);
            if ($match) {
                $match = ($this->getToken() === ')');
            }
        }

        if ($match) {
            $expression = $expr;
        } else {
            $this->rewind($context, ')');
        }
        return $match;
    }

    /**
     * Parses a basic condition (object comparator value).
     *
     * @param RuleNode|null &$condition
     * @return bool True if a valid condition is found.
     */
    private function basicCondition(?RuleNode &$condition): bool {
        $context = $this->setRecoveryPoint('basicCondition');
        $className = '';
        $comparator = '';
        $value = '';

        $match = $this->object($className);
        if ($match) {
            $match = $this->comparator($comparator);
            if ($match) {
                $match = $this->value($value);
            }
        }

        $node = new RuleNode([
            'type' => 'condition',
            'component' => $className,
            'comparator' => $comparator,
            'value' => $value
        ]);

        return $this->parsingStatus($match, $condition, $node, $context);
    }

    /**
     * Parses a boolean expression (AND/OR).
     *
     * @param RuleNode|null &$expression
     * @return bool True if a valid boolean expression is found.
     */
    private function booleanExpression(?RuleNode &$expression): bool {
        $context = $this->setRecoveryPoint('booleanExpression');
        $left = null;
        $right = null;

        // A boolean expression starts with either a nested expression or a condition
        $match = $this->nestedExpression($left) ? true : $this->basicCondition($left);

        if ($match) {
            $expr = $left;
            $boolOperator = $this->nextTokenType();

            // Check if followed by AND/OR
            if ($boolOperator === 'or' || $boolOperator === 'and') {
                $this->getToken();
                $match = $this->booleanExpression($right);

                $expr = new RuleNode([
                    'type' => 'booleanExpression',
                    'left' => $left,
                    'boolOperator' => $boolOperator,
                    'right' => $right
                ]);
            }
            $expression = $expr;
        }

        return $this->parsingStatus($match, $expression, $expression, $context);
    }

    /**
     * ************************************
     * Grammatical termination elements
     * ************************************ */

    /**
     * Parses an object and updates the provided object reference.
     *
     * @param string $className A reference to the object array that will be updated.
     * @return bool True if a valid object is found, false otherwise.
     */
    private function object(string $className): bool {
        $context = $this->setRecoveryPoint('object');
        $match = match ($this->nextTokenType()) {
            'className' => $this->property($className),
            'string' => $this->method($className),
            default => false
        };

        if (!$match) {
            $this->rewind($context);
        }
        return $match;
    }

    /**
     * Parses a property and updates the provided property reference.
     *
     * @param string &$property A reference to the property string that will be updated.
     * @return bool True if a valid property is found, false otherwise.
     */
    private function property(&$property): bool {
        $context = $this->setRecoveryPoint('property');
        $properties = [$this->getToken()];

        $match = ($this->nextToken() === '->') ? $this->nestedProperty($properties) : true;

        if ($match) {
            $property = implode('->', $properties);
        } else {
            $this->rewind($context);
        }
        return $match;
    }

    /**
     * Parses a nested property
     *
     * @param array<string> $properties A reference to the properties array that will be updated.
     * @return bool True if a valid nested property chain is found, false otherwise.
     */
    private function nestedProperty(array &$properties): bool {
        if ($this->nextToken() === '->') {
            $this->getToken();
            $method = '';
            $match = $this->method($method);

            if ($match) {
                $properties[] = $method;
            } elseif ($this->nextTokenType() === 'string') {
                $properties[] = $this->getToken();
                $match = $this->nestedProperty($properties);
            }
        } else {
            // End of chain
            $match = ($this->nextTokenType() === 'comparator');
        }
        return $match;
    }

    /**
     * Parses a method call
     *
     * @param string &$method A reference to the method string that will be updated.
     * @return bool True if a valid method is found, false otherwise.
     */
    private function method(string &$method): bool {
        $context = $this->setRecoveryPoint('method');
        $methodName = $this->getToken();

        $match = ($this->getToken() === '(');
        $parameters = [];
        if ($match) {
            $match = $this->parseParameterList($parameters);
        }

        if ($match) {
            $paramList = implode(', ', $parameters);
            $method = "{$methodName}({$paramList})";
        } else {
            $this->rewind($context);
        }
        return $match;
    }

    /**
     * Parses a parameter list and updates the provided parameters array.
     *
     * @param array<string> $parameters A reference to the parameters array that will be updated.
     * @return bool True if a valid parameter list is found, false otherwise.
     */
    private function parseParameterList(array &$parameters): bool {
        $tokenType = $this->nextTokenType();
        $parameter = $this->getToken();

        if (in_array($tokenType, ['string', 'number', 'quoted'])) {
            $parameters[] = $parameter;
        }

        return match ($tokenType) {
            'parenthesis' => ($parameter === ')'),
            'string', 'number', 'quoted', 'comma' => $this->parseParameterList($parameters),
            default => false,
        };
    }

    /**
     * Parses a comparator token
     *
     * @param string &$comparator A reference to the comparator string that will be updated.
     * @return bool True if a valid comparator is found, false otherwise.
     */
    private function comparator(string &$comparator): bool {
        if ($this->nextTokenType() === 'comparator') {
            $comparator = $this->getToken();
            return true;
        }
        return false;
    }

    /**
     * Parses a literal value
     *
     * @param string &$value A reference to the value string that will be updated.
     * @return bool True if a valid value is found, false otherwise.
     */
    private function value(string &$value): bool {
        $type = $this->nextTokenType();
        if (in_array($type, ['number', 'quoted', 'string'])) {
            $value = $this->getToken();
            return true;
        }
        return false;
    }

    /**
     * ********************************
     * Parsing engine routines
     * ****************************** */

    /**
     * Captures current parser state for backtracking.
     *
     * @param string $msg Context description for debugging.
     * @return array{
     *      nestingLevel: int,
     *      caller: string,
     *      pos: int,
     *      msg: string,
     *      parsingTree: RuleNode|null}
     */
    private function setRecoveryPoint(string $msg): array {
        $caller = explode(' ', $msg);
        return [
            'nestingLevel' => $this->nestingLevel++,
            'caller' => $caller[0],
            'pos' => $this->pos,
            'msg' => $msg,
            'parsingTree' => $this->parsingTree
        ];
    }

    /**
     * Restores parser state.
     *
     * @param array{nestingLevel: int, caller: string, pos: int, msg: string, parsingTree: RuleNode|null} $context The context array containing parsing state information.
     * @param string|null $expected (Optional) Override the 'expected' token for error reporting.
     * @return void
     */
    private function rewind(array $context, $expected = null): void {
        $this->nestingLevel = $context['nestingLevel'];
        $this->expected = $expected ?? $context['caller'];
        $this->pos = $context['pos'];
        $this->parsingTree = $context['parsingTree'];
    }

    /**
     * Handles parsing status
     *
     * @param bool $match Indicates whether a match is found.
     * @param RuleNode|null $target A reference to the parsing tree to update.
     * @param RuleNode|null $node The node data to update in the parsing tree.
     * @param array{nestingLevel: int, caller: string, pos: int, msg: string, parsingTree: RuleNode|null} $context The context array containing parsing state information.
     * @return bool The parsing status indicating whether a match is found.
     */
    private function parsingStatus(bool $match, ?RuleNode &$target, ?RuleNode $node, array $context): bool {
        if ($match) {
            $target = $node;
            $this->nestingLevel = $context['nestingLevel'];
        } else {
            $this->rewind($context);
        }
        return $match;
    }

    /**
     * Logging for debug purposes.
     *
     * @param string $msg The debug message to log.
     * @return void
     */
    private function _debug(string $msg): void {
        $level = $this->nestingLevel;
        Yii::debug("*** Debug *** " . sprintf("%'.03d", $level) . ' ' . str_repeat('—', $level) . $msg, __METHOD__);
    }

    /**
     * Retrieves the current token and advances the position pointer.
     *
     * @return string The retrieved token.
     */
    private function getToken(): string {
        $token = $this->_token($this->pos, 'value');
        if ($this->pos < $this->tokenNb - 1)
            $this->pos++;
        return $token;
    }

    /**
     * Retrieves the next token without advancing the position pointer.
     *
     * @return string The next token.
     */
    private function nextToken(): string {
        return $this->_token($this->pos, 'value');
    }

    /**
     * Retrieves the type of the next token without advancing the position pointer.
     *
     * @return string The type of the next token.
     */
    private function nextTokenType(): string {
        return $this->_token($this->pos, 'type');
    }

    /**
     * Retrieves a token from the token stream.
     *
     * @param int $pos The position of the token in the token stream.
     * @param string $element The element of the token to retrieve.
     * @return string The retrieved token.
     * @throws InvalidParamException if an emergency stop condition is encountered.
     */
    private function _token(int $pos, string $element): string {
        if ($this->emergencyStop++ > 500)
            throw new InvalidParamException('Emergency stop triggered: infinite loop suspected.');
        return ($pos < $this->tokenNb) ? $this->tokens[$pos][$element] : '';
    }

    /**
     * Generates a string representation of parsing tokens up to the specified index.
     *
     * @param int $n The index of the last token to include in the string.
     * @return string The string representation of parsing tokens.
     */
    private function _parsingTokens(int $n): string {
        $str = '';
        for ($i = 0; $i <= $n && $i < $this->tokenNb; $i++) {
            $str .= "[{$this->tokens[$i]['type']} => {$this->tokens[$i]['value']}] ";
        }
        return $str;
    }

    /**
     * Lexical analyzer: converts string into token array.
     *
     * Regular Expression Explanation:
     * - \s* matches zero or more whitespace characters (spaces, tabs, etc.)
     * - (->|<=|>=|<>|!=|==|=|<|>|,|\(|\)|and|or|not| # Matches operators, parentheses, and keywords
     * - [a-zA-Z_][a-zA-Z0-9_]*|\d+)                  # Matches names or numbers
     * - \s* matches zero or more whitespace characters (spaces, tabs, etc.)
     *
     * @param string $inputString The raw rule string.
     * @return list<array{type: string, value: string}>
     */
    private function tokenize(string $inputString): array {
        $pattern = "/\\s*(->|<=|>=|<>|!=|==|=|<|>|,|\\(|\\)|[a-zA-Z_][a-zA-Z0-9_]*|\\d+|'[^']*'|\"[^\"]*\")\\s*/";
        preg_match_all($pattern, $inputString, $matches);
        $tokens = array_map('trim', $matches[0]);
        $tokenize = [];

        foreach ($tokens as $token) {
            $type = 'other';
            foreach (self::TOKEN_TYPE_REGEX as $tokenType => $regex) {
                if (preg_match($regex, $token)) {
                    $type = $tokenType;
                    break;
                }
            }
            if ($type === 'className' && !ModelHelper::exists($token))
                $type = 'string';
            elseif ($type === 'comparator2')
                $type = 'comparator';

            $tokenize[] = ['type' => $type, 'value' => $token];
        }
        return $tokenize;
    }

    /**
     * Generates a descriptive error message on parsing failure.
     *
     * @return string The error message.
     */
    private function makeErrorMessage(): string {
        if (!$this->expected) {
            return '';
        }
        $found = $this->nextToken() === '' ? 'end of rule' : $this->nextToken();
        $in = '';
        for ($i = 0; $i <= $this->pos && $i < $this->tokenNb; $i++) {
            $in .= ' ' . $this->tokens[$i]['value'];
        }
        return "Expected '{$this->expected}', found '{$found}' in [{$in}]";
    }
}
