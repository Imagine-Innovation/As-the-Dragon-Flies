<?php

namespace common\components;

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
 *     */
class RuleParser extends Component
{

    public string $errorMessage = "";

    /** @var array<string, mixed> $parsingTree */
    public array $parsingTree = [];
    public string $expected = "";
    // For internal use

    /** @var list<array{type: string, value: string}> $tokens */
    private array $tokens = [];
    private int $tokenNb = 0;
    private int $pos = 0;
    private int $emergencyStop = 0;
    // for debug purpose
    private int $nestingLevel = 0;

    /**
     * Parses a rule from the given input string.
     *
     * This function tokenizes the input rule string and stores the tokens. It then parses the rule using
     * a recursive descent parser. If parsing fails, an error message is generated.
     *
     * @param string $inputString The input string containing the rule to be parsed.
     * @return bool The parsing tree if the rule is successfully parsed, or false if parsing fails.
     */
    public function parseRule(string $inputString): bool {
        // Tokenize the input rule string
        $this->tokens = $this->tokenize($inputString);
        // Store the total number of tokens in the token array
        $this->tokenNb = count($this->tokens);
        // Debugging: log the tokens and the input string
        $this->_debug("——> tokens = {$this->_parsingTokens($this->tokenNb - 1)}");
        $this->_debug("——> inputString = {$inputString}");

        // Parse the rule using the recursive descent parser
        $match = $this->rootExpression($inputString);
        if (!$match) {
            // Generate error message if parsing failed
            $this->errorMessage = $this->makeErrorMessage();
        }

        // Return the parsing tree if match found, otherwise false
        return $match;
    }

    /**
     * Parses the root expression from the given input string.
     *
     * This function attempts to match the root expression of the parsing tree with the input string.
     * If a match is found, it logs a debug message indicating that the input string is a valid rule.
     *
     * @param string $inputString The input string containing the rule to be parsed.
     * @return bool The matched parsing tree if the rule is valid, or false if the rule is invalid.
     */
    private function rootExpression(string $inputString): bool {
        // Attempt to match the root expression of the parsing tree
        $match = $this->expression($this->parsingTree);

        // If a match is found, log a debug message
        if ($match) {
            $this->_debug("'{$inputString}' is a valid rule");
        }

        // Return the matched parsing tree or false if no match is found
        return $match;
    }

    /**
     * Parses an expression and updates the provided expression reference.
     *
     * This function attempts to parse an expression, which can be a negated expression,
     * a boolean expression, or a basic condition. It handles negation by consuming the 'not' token.
     * If a valid expression is found, it updates the provided expression reference.
     *
     * @param array<string, mixed> &$expression A reference to the expression array that will be updated.
     * @return bool True if a valid expression is found, false otherwise.
     */
    private function expression(array &$expression): bool {
        // Set a recovery point in case the expression parsing fails
        $context = $this->setRecoveryPoint('expression');
        $expr = [];

        // Check if the expression is negated
        $negate = ($this->nextTokenType() === 'not');
        if ($negate) {
            $this->getToken(); // Consume the 'not' token
            $this->_debug("Negative expression, next token={$this->nextToken()}");

            // Recursively parse the negated expression
            $match = $this->expression($expr);

            // Update the parsing status for the negated expression
            return $this->parsingStatus($match, $expression, 'negate', $expr, $context);
        }

        // Attempt to match a boolean expression or a basic condition
        $match = $this->booleanExpression($expr) ? true : $this->basicCondition($expr);

        // If a match is found, update the provided expression reference
        if ($match) {
            $expression = $expr['node'];
        } else {
            // If no match is found, rewind to the recovery point
            $this->rewind($context);
        }

        // Return whether a valid expression was found
        return $match;
    }

    /**
     * Parses a nested expression and updates the provided expression reference.
     *
     * This function attempts to parse a nested expression enclosed in parentheses. It first checks for an opening
     * parenthesis '(', then parses the expression inside, and finally checks for a closing parenthesis ')'.
     * If the nested expression is valid, it updates the provided expression reference.
     *
     * @param array<string, mixed> &$expression A reference to the expression array that will be updated.
     * @return bool True if a valid nested expression is found, false otherwise.
     */
    private function nestedExpression(&$expression) {
        // Set a recovery point in case the nested expression parsing fails
        $context = $this->setRecoveryPoint('nestedExpression');
        $expr = [];

        // Check for the opening parenthesis '('
        $expected = '(';
        $match = ($this->getToken() === $expected);
        if ($match) {
            // Parse the expression inside the parentheses
            $match = $this->expression($expr);
            if ($match) {
                // Check for the closing parenthesis ')'
                $expected = ')';
                $match = ($this->getToken() === $expected);
            }
        }

        // If a match is found, update the provided expression reference
        if ($match) {
            $expression = $expr;
        } else {
            // If no match is found, rewind to the recovery point
            $this->rewind($context);
        }

        // Return whether a valid nested expression was found
        return $match;
    }

    /**
     * Parses a basic condition and updates the provided condition reference.
     *
     * This function attempts to parse a basic condition consisting of an object, a comparator, and a value.
     * It checks each component in sequence and constructs a condition node if all components are matched.
     * If the basic condition is valid, it updates the provided condition reference.
     *
     * @param array<string, mixed> $condition A reference to the condition array that will be updated.
     * @return bool True if a valid basic condition is found, false otherwise.
     */
    private function basicCondition(array &$condition): bool {
        // Set a recovery point in case the basic condition parsing fails
        $context = $this->setRecoveryPoint('basicCondition');
        $className = '';
        $comparator = '';
        $value = '';

        // Attempt to match the object component of the condition
        $match = $this->object($className);
        if ($match) {
            // Attempt to match the comparator component of the condition
            $match = $this->comparator($comparator);
            if ($match) {
                // Attempt to match the value component of the condition
                $match = $this->value($value);
            }
        }

        // Construct the condition node with the matched components
        $node = ['component' => $className, 'comparator' => $comparator, 'value' => $value];

        // Update the parsing status and the provided condition reference
        return $this->parsingStatus($match, $condition, 'condition', $node, $context);
    }

    /**
     * Parses a boolean expression and updates the provided expression reference.
     *
     * This function attempts to parse a boolean expression consisting of nested expressions, basic conditions,
     * and boolean operators ('and', 'or'). It handles nested expressions and combines them using boolean operators
     * if present. If the boolean expression is valid, it updates the provided expression reference.
     *
     * @param array<string, mixed> &$expression A reference to the expression array that will be updated.
     * @return bool True if a valid boolean expression is found, false otherwise.
     */
    private function booleanExpression(&$expression) {
        // Set a recovery point in case the boolean expression parsing fails
        $context = $this->setRecoveryPoint('booleanExpression');
        $left = [];
        $right = [];
        $expr = [];

        // Attempt to match a nested expression or a basic condition
        $match = $this->nestedExpression($left) ? true : $this->basicCondition($left);
        if ($match) {
            $expr = $left;
            $boolOperator = $this->nextTokenType();

            // Check if the next token is a boolean operator ('and' or 'or')
            if ($boolOperator === 'or' || $boolOperator === 'and') {
                $this->getToken(); // Consume the boolean operator token
                // Recursively parse the right side of the boolean expression
                $match = $this->booleanExpression($right);

                // Construct the boolean expression node
                $expr = [
                    'type' => 'booleanExpression',
                    'left' => $left,
                    'boolOperator' => $boolOperator,
                    'right' => $right['type'] === 'negate' ? $right : $right['node'],
                ];
            }
        }

        // Update the parsing status and the provided expression reference
        return $this->parsingStatus($match, $expression, 'expression', $expr, $context);
    }

    /**
     * ************************************
     * Grammatical termination elements
     * ************************************ */

    /**
     * Parses an object and updates the provided object reference.
     *
     * This function attempts to parse an object based on the next token type. It checks if the next token
     * type is 'className' or 'string' and parses the object accordingly using the property or method parser.
     * If the object is valid, it updates the provided object reference.
     *
     * @param string $className A reference to the object array that will be updated.
     * @return bool True if a valid object is found, false otherwise.
     */
    private function object(string $className) {
        // Set a recovery point in case the object parsing fails
        $context = $this->setRecoveryPoint('object');

        // Determine the next token type and parse accordingly
        $match = match ($this->nextTokenType()) {
            'className' => $this->property($className),
            'string' => $this->method($className),
            default => false
        };

        // If a match is found, log the parsed object for debugging
        if ($match) {
            $this->_debug("object=[{$className}]");
        } else {
            // If no match is found, rewind to the recovery point
            $this->rewind($context);
        }

        // Return whether a valid object was found
        return $match;
    }

    /**
     * Parses a property and updates the provided property reference.
     *
     * This function attempts to parse a property assuming the current token type is a className.
     * It collects the tokens representing the property chain and constructs the full property string.
     * If the property is valid, it updates the provided property reference.
     *
     * @param string &$property A reference to the property string that will be updated.
     * @return bool True if a valid property is found, false otherwise.
     */
    private function property(&$property) {
        // Set a recovery point in case the property parsing fails
        $context = $this->setRecoveryPoint('property');
        /** @var array<string> $properties */
        $properties = [];

        // Assuming the current token type is a className, collect the token
        $properties[] = $this->getToken();

        // Check if the next token is '->' indicating a nested property
        $match = ($this->nextToken() === '->');
        if ($match) {
            // Parse the nested property
            $match = $this->nestedProperty($properties);
        }

        // If a match is found, construct the full property string
        if ($match) {
            $property = implode('->', $properties);
            $this->_debug("property=[{$property}]");
        } else {
            // If no match is found, rewind to the recovery point
            $this->rewind($context);
        }

        // Return whether a valid property was found
        return $match;
    }

    /**
     * Parses a nested property and updates the provided properties array.
     *
     * This function attempts to parse a nested property chain. It handles nested properties by recursively
     * consuming '->' tokens and matching methods or strings. The loop terminates when a method is encountered
     * or when a comparator token type is reached.
     *
     * @param array<string> $properties A reference to the properties array that will be updated.
     * @return bool True if a valid nested property chain is found, false otherwise.
     */
    private function nestedProperty(array &$properties): bool {
        // Check if the next token is '->' indicating a nested property
        if ($this->nextToken() === '->') {
            $this->getToken(); // Consume the '->' token
            $method = "";

            // Attempt to match a method
            $match = $this->method($method);

            if ($match) {
                // The nested property loop terminates with a method
                $properties[] = $method;
            } elseif ($this->nextTokenType() === 'string') {
                // If the next token type is a string, add it to the properties array
                $properties[] = $this->getToken();
                // Recursively parse the next nested property
                $match = $this->nestedProperty($properties);
            }
        } else {
            // The nested property loop terminates with a comparator
            $match = ($this->nextTokenType() === 'comparator');
        }

        // Return whether a valid nested property chain was found
        return $match;
    }

    /**
     * Parses a method and updates the provided method reference.
     *
     * This function attempts to parse a method assuming the current token type is a string representing
     * the method name. It checks for the opening parenthesis '(' to start parsing the parameter list.
     * If the method is valid, it constructs the full method string including parameters and updates
     * the provided method reference.
     *
     * @param string &$method A reference to the method string that will be updated.
     * @return bool True if a valid method is found, false otherwise.
     */
    private function method(&$method) {
        // Set a recovery point in case the method parsing fails
        $context = $this->setRecoveryPoint('method');

        // Assuming the current token type is a string, collect the method name
        $methodName = $this->getToken();
        $expected = '(';

        // Check if the next token is an opening parenthesis '('
        $match = ($this->getToken() === $expected);
        /** @var array<string> $parameters */
        $parameters = [];
        if ($match) {
            // Parse the parameter list
            $match = $this->parseParameterList($parameters);
        }

        // If a match is found, construct the full method string including parameters
        if ($match) {
            $paramList = implode(', ', $parameters);
            $method = "{$methodName}({$paramList})";
            $this->_debug("method=[{$method}]");
        } else {
            // If no match is found, rewind to the recovery point
            $this->rewind($context);
        }

        // Return whether a valid method was found
        return $match;
    }

    /**
     * Parses a parameter list and updates the provided parameters array.
     *
     * This function attempts to parse a list of parameters. It iterates through the tokens, collecting
     * parameters until a closing parenthesis ')' is encountered. Parameters can be strings, numbers, or
     * quoted values. The function handles commas ',' between parameters.
     *
     * @param array<string> $parameters A reference to the parameters array that will be updated.
     * @return bool True if a valid parameter list is found, false otherwise.
     */
    private function parseParameterList(array &$parameters): bool {
        // Get the type of the next token
        $tokenType = $this->nextTokenType();
        // Get the current token
        $parameter = $this->getToken();
        $this->_debug('parseParameterList => tokenType=[' . $tokenType . ']; parameter=[' . $parameter . ']');

        // Depending on the token type, handle different cases
        switch ($tokenType) {
            case 'parenthesis':
                // Check if the current token is a closing parenthesis
                $match = ($parameter === ')');
                break;
            case 'string':
            case 'number':
            case 'quoted':
                // If the token type is a string, number, or quoted value, add it to the parameters array
                $parameters[] = $parameter;
            case 'comma':
                // If the token type is a comma, continue parsing the parameter list
                $match = $this->parseParameterList($parameters);
                break;
            default:
                // Invalid token type
                $match = false;
                break;
        }

        // Return whether a valid parameter list was found
        return $match;
    }

    /**
     * Parses a comparator and updates the provided comparator reference.
     *
     * This function attempts to parse a comparator token. It checks if the next token type is 'comparator',
     * and if so, collects the comparator token. If a valid comparator is found, it updates the provided
     * comparator reference.
     *
     * @param string &$comparator A reference to the comparator string that will be updated.
     * @return bool True if a valid comparator is found, false otherwise.
     */
    private function comparator(&$comparator) {
        $this->_debug('--------------------');
        $this->_debug('try comparator');

        // Check if the next token type is a comparator
        $match = ($this->nextTokenType() === 'comparator');

        // If a comparator is found, collect the token
        if ($match) {
            $comparator = $this->getToken();
        }

        // If a match is found, log the parsed comparator for debugging
        if ($match) {
            $this->_debug("comparator=[{$comparator}]");
        } else {
            $this->_debug("not a comparator");
        }

        // Return whether a valid comparator was found
        return $match;
    }

    /**
     * Parses a value and updates the provided value reference.
     *
     * This function attempts to parse a value token. It checks if the next token type is 'number', 'quoted',
     * or 'string', and if so, collects the token as the value. If a valid value is found, it updates the
     * provided value reference.
     *
     * @param string &$value A reference to the value string that will be updated.
     * @return bool True if a valid value is found, false otherwise.
     */
    private function value(&$value) {
        $this->_debug('--------------------');
        $this->_debug('try value');

        // Get the type of the next token
        $type = $this->nextTokenType();

        // Check if the token type is 'number', 'quoted', or 'string'
        $match = ($type === 'number' || $type === 'quoted' || $type === 'string');

        // If a valid value token is found, collect the token
        if ($match) {
            $value = $this->getToken();
        }

        // If a match is found, log the parsed value for debugging
        if ($match) {
            $this->_debug("value=[{$value}]");
        } else {
            $this->_debug("not a value");
        }

        // Return whether a valid value was found
        return $match;
    }

    /**
     * ********************************
     * Parsing engine routines
     * ****************************** */

    /**
     * Sets a recovery point for error handling during parsing.
     *
     * This function sets a recovery point to handle errors during parsing. It logs debugging information
     * including the message describing the recovery point and the current parsing context. It returns
     * a context array containing information about the current parsing state.
     *
     * @param string $msg A message describing the recovery point.
     * @return array<string, mixed> The context array containing parsing state information.
     */
    private function setRecoveryPoint(string $msg): array {
        $this->_debug("--------------------");
        $this->_debug("'Try {$msg}");
        $this->_traceBack();

        // Extract the caller information from the message
        $caller = explode(' ', $msg);

        // Construct and return the context array with parsing state information
        return [
            'nestingLevel' => $this->nestingLevel++,
            'msg' => $msg,
            'caller' => $caller[0],
            'pos' => $this->pos,
            'parsingTree' => $this->parsingTree
        ];
    }

    /**
     * Rewinds parsing to a specified recovery point.
     *
     * This function rewinds parsing to a specified recovery point by restoring the parsing state
     * to the state captured in the provided context array. It updates the parsing position, nesting level,
     * parsing tree, and the expected token if provided. It logs debugging information about the rewind operation.
     *
     * @param array<string, mixed> $context The context array containing parsing state information to rewind to.
     * @param string|null $expected (Optional) The expected token after rewinding.
     * @return void
     */
    private function rewind(array $context, $expected = null): void {
        // Restore parsing state from the provided context
        $this->nestingLevel = $context['nestingLevel'];
        $this->pos = $context['pos'];
        $this->parsingTree = $context['parsingTree'];
        $this->expected = $expected ?? $context['caller'];

        // Log debugging information about the rewind operation
        $this->_traceBack();
        $this->_debug("rewind pos={$this->pos}, not a '{$context['caller']}'" . ($expected ? ", expected '{$expected}' not found" : ''));
        $this->_debug("--------------------");
    }

    /**
     * Recursively displays the parsing tree.
     *
     * This function recursively displays the parsing tree in a formatted string representation.
     * It traverses the parsing tree and constructs a string representing the types of nodes encountered.
     *
     * @param array<string, mixed> $parsingTree The parsing tree to display.
     * @return string The formatted string representation of the parsing tree.
     */
    private function _displayParsingTree(array $parsingTree): string {
        $trace = "";
        if (isset($parsingTree['type'])) {
            $trace = $parsingTree['type'];
            if (isset($parsingTree['node'])) {
                // Recursively display the child node
                $trace = $trace . '>' . $this->_displayParsingTree($parsingTree['node']);
            }
        }
        return $trace;
    }

    /**
     * Handles parsing status and updates the parsing tree.
     *
     * This function handles the parsing status by updating the parsing tree based on whether a match
     * is found or not. If a match is found, it updates the parsing tree with the provided type and node.
     * If no match is found, it rewinds parsing to the specified recovery point and updates the expected token
     * if provided. It logs debugging information about the parsing status.
     *
     * @param bool $match Indicates whether a match is found.
     * @param array<string, mixed> $parsingTree A reference to the parsing tree to update.
     * @param string $type The type of the current node in the parsing tree.
     * @param mixed $node The node data to update in the parsing tree.
     * @param array<string, mixed> $context The context array containing parsing state information.
     * @param string|null $expected (Optional) The expected token after rewinding.
     * @return bool The parsing status indicating whether a match is found.
     */
    private function parsingStatus(bool $match, array &$parsingTree, string $type, mixed $node, array $context, string $expected = null): bool {
        if ($match) {
            // Update the parsing tree with the provided type and node
            $parsingTree['type'] = $type;
            $parsingTree['node'] = $node;

            // Update the nesting level and log debugging information
            $this->nestingLevel = $context['nestingLevel'];
            $this->_debug("--------------------");
            $this->_debug("——> '{$type}' is matching");
            $displayParsingTree = $this->_displayParsingTree($parsingTree);
            $this->_debug("——> ParsingTree: '{$displayParsingTree}'");
            $this->_traceBack();
        } else {
            // If no match is found, rewind parsing to the specified recovery point
            $this->rewind($context, $expected);
        }

        return $match;
    }

    /**
     * Displays a traceback for debugging purposes.
     *
     * This function displays a traceback containing information about the function calls leading
     * up to the current function. It includes file names, function names, and line numbers of
     * the function calls in the traceback. Only traces from the current file are considered.
     *
     * @return void
     */
    private function _traceBack(): void {
        // Get debug backtrace
        $backTrace = debug_backtrace();
        $traces = [];

        // Iterate through the backtrace
        foreach ($backTrace as $trace) {
            // Check if file information is available and matches the current file
            $file = $trace['file'] ?? "??";
            if ($file === 'C:\Users\franc\OneDrive\devenv\htdocs\DnD\common\components\RuleParser.php') {
                // Extract function name and line number
                $function = $trace['function'];
                $line = $trace['line'] ?? "??";
                // Add trace to the list
                array_unshift($traces, "{$line}=>{$function}");
            }
        }
        // Display the traceback
        $this->_debug('——> Traceback = ' . implode(' > ', $traces));
    }

    /**
     * Logs a debug message with nesting level.
     *
     * This function logs a debug message with the current nesting level prepended to the message.
     * It formats the message with leading zeros for nesting level and dashes for visual indentation.
     *
     * @param string $msg The debug message to log.
     * @return void
     */
    private function _debug(string $msg): void {
        // Get the current nesting level
        $level = $this->nestingLevel;
        // Format the debug message with nesting level and visual indentation
        Yii::debug("*** Debug *** " . sprintf("%'.03d\n", $level) . ' ' . str_repeat('—', $level) . $msg, __METHOD__);
    }

    /**
     * Retrieves a token from the token stream.
     *
     * This function retrieves a token from the token stream at the specified position.
     * It checks if there are more tokens available and updates the `token` property accordingly.
     * If the end of the token stream is reached, it returns an empty string.
     *
     * @param int $pos The position of the token in the token stream.
     * @param string $element The element of the token to retrieve.
     * @return string The retrieved token.
     * @throws InvalidParamException if an emergency stop condition is encountered.
     */
    private function _token(int $pos, string $element): string {
        // Check for an emergency stop condition
        if ($this->emergencyStop++ > 200) {
            // Throw an exception indicating an emergency stop condition
            throw new InvalidParamException('Emergency stop');
        }

        // Check if there are more tokens available
        if ($pos < $this->tokenNb) {
            // If more tokens are available, retrieve the token at the specified position
            $token = $this->tokens[$pos][$element];
        } else {
            // If the end of the token stream is reached, set the token to an empty string
            $token = "";
        }

        // Display a traceback for debugging purposes
        $this->_traceBack();

        // Return the retrieved token
        return $token;
    }

    /**
     * Generates a string representation of parsing tokens up to the specified index.
     *
     * This function generates a string representation of parsing tokens up to the specified index.
     * It iterates through the tokens and constructs a string containing the type and value of each token.
     *
     * @param int $n The index of the last token to include in the string.
     * @return string The string representation of parsing tokens.
     */
    private function _parsingTokens(int $n): string {
        $str = "";
        // Check if there are tokens available
        if ($this->tokenNb > 0) {
            // Iterate through tokens up to the specified index
            for ($i = 0; $i <= $n; $i++) {
                // Construct a string representation of the token type and value
                $str = $str . '[' . $this->tokens[$i]['type'] . ' => ' . $this->tokens[$i]['value'] . '] ';
            }
        }
        // Return the string representation of parsing tokens
        return $str;
    }

    /**
     * Retrieves the current token and advances the position pointer.
     *
     * This function retrieves the current token from the token stream at the current position.
     * It advances the position pointer to point to the next token if available.
     *
     * @return string The retrieved token.
     */
    private function getToken(): string {
        // Retrieve the current token
        $token = $this->_token($this->pos, 'value');
        // Increment the position pointer if more tokens are available
        if ($this->pos < $this->tokenNb - 1) {
            $this->pos++;
        }
        // Log debugging information about the retrieved token
        $this->_debug('getToken=[' . $token . ']');
        // Return the retrieved token
        return $token;
    }

    /**
     * Retrieves the next token without advancing the position pointer.
     *
     * This function retrieves the next token from the token stream without advancing the position pointer.
     *
     * @return string The next token.
     */
    private function nextToken(): string {
        // Retrieve the next token without advancing the position pointer
        $token = $this->_token($this->pos, 'value');
        // Log debugging information about the next token
        $this->_debug('nextToken=[' . $token . ']');
        // Return the next token
        return $token;
    }

    /**
     * Retrieves the type of the next token without advancing the position pointer.
     *
     * This function retrieves the type of the next token from the token stream without advancing
     * the position pointer.
     *
     * @return string The type of the next token.
     */
    private function nextTokenType(): string {
        // Retrieve the type of the next token without advancing the position pointer
        $tokenType = $this->_token($this->pos, 'type');
        // Log debugging information about the type of the next token
        $this->_debug('nextTokenType=[' . $tokenType . ']');
        // Return the type of the next token
        return $tokenType;
    }

    /**
     * Tokenizes the input string.
     *
     * This function tokenizes the input string by matching patterns defined for various token types.
     * It maps each token to its corresponding type and value based on the matched pattern.
     *
     * Regular Expression Explanation:
     * - \s* matches zero or more whitespace characters (spaces, tabs, etc.)
     * - (->|<=|>=|<>|!=|==|=|<|>|,|\(|\)|and|or|not| # Matches operators, parentheses, and keywords
     * - [a-zA-Z_][a-zA-Z0-9_]*|\d+)                  # Matches names or numbers
     * - \s* matches zero or more whitespace characters (spaces, tabs, etc.)
     *
     * @param string $inputString The input string to tokenize.
     * @return list<array{
     *     type: string,
     *     value: string
     * }> An array of tokens with their corresponding types and values.
     */
    private function tokenize(string $inputString): array {
        // Define the pattern for tokenizing
        $pattern = "/\\s*(->|<=|>=|<>|!=|==|=|<|>|,|\\(|\\)|[a-zA-Z_][a-zA-Z0-9_]*|\\d+|'[^']*'|\"[^\"]*\")\\s*/";

        // Perform regex matching to tokenize the input string
        $matches = [];
        preg_match_all($pattern, $inputString, $matches);

        // Trim the matched tokens
        $tokens = array_map('trim', $matches[0]);
        $tokenize = [];

        // Define token types and corresponding regex patterns
        $tokenTypes = [
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

        // Iterate through tokens and determine their types
        foreach ($tokens as $token) {
            $type = 'other';
            foreach ($tokenTypes as $tokenType => $regex) {
                if (preg_match($regex, $token)) {
                    $type = $tokenType;
                    break;
                }
            }
            // Adjust token type for class names and comparators
            if ($type === 'className' && !ModelHelper::exists($token)) {
                $type = 'string';
            } elseif ($type === 'comparator2') {
                $type = 'comparator';
            }

            // Add token with its type to the tokenized array
            $tokenize[] = ['type' => $type, 'value' => $token];
        }

        // Return the tokenized array
        return $tokenize;
    }

    /**
     * Generates an error message based on the expected token and the token found.
     *
     * This function generates an error message indicating the expected token and the token found
     * based on the current parsing context. It constructs the error message using information
     * about the expected token, the token found, and the token stream up to the current position.
     *
     * @return string The error message.
     */
    private function makeErrorMessage(): string {
        // Check if an expected token is set
        if ($this->expected) {
            // Determine the token found, considering the end of the token stream
            $found = $this->nextToken() === "" ? "end of rule definition" : $this->nextToken();

            // Construct the input string up to the current position
            $in = $this->tokens[0]['value'];
            for ($i = 1; $i <= $this->pos; $i++) {
                $in = "{$in} {$this->tokens[$i]['value']}";
            }

            // Generate the error message
            $errorMessage = "Expected '$this->expected', found '$found' in [$in]";

            return $errorMessage;
        }

        // Return an empty string if no expected token is set
        return "";
    }
}
