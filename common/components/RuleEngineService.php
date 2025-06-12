<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use common\components\LoggerService;
use common\models\Rule;
use common\models\RuleExpression;
use common\models\RuleCondition;
use common\models\RuleAction;
use common\models\RuleModel;
use common\enums\AppStatus;

class RuleEngineService extends Component
{
    private $logger;

    public function __construct(LoggerService $logger, $config = [])
    {
        $this->logger = $logger;
        parent::__construct($config);
    }

    /**
     * Processes a trigger event and executes associated rules.
     *
     * @param string $triggerName The name of the trigger.
     * @param mixed $data The data associated with the trigger.
     * @return array Collected outcomes from executed rule actions.
     */
    public function processTrigger(string $triggerName, $data): array
    {
        $this->logger->log("Processing trigger: {$triggerName}");
        $allOutcomes = [];

        $activeRules = Rule::find()
            ->where(['trigger_name' => $triggerName, 'status' => AppStatus::ACTIVE->value])
            ->all(); // Fetch Rule model instances

        if (empty($activeRules)) {
            $this->logger->log("No active rules found for trigger: {$triggerName}");
            return $allOutcomes;
        }

        foreach ($activeRules as $rule) {
            $this->logger->log("Evaluating rule: {$rule->name} (ID: {$rule->id})");
            if ($this->evaluateRuleConditions($rule, $data)) {
                $this->logger->log("Rule '{$rule->name}' conditions met. Executing actions.");
                $ruleOutcomes = $this->executeRuleActions($rule, $data);
                if (!empty($ruleOutcomes)) {
                    $allOutcomes = array_merge($allOutcomes, $ruleOutcomes);
                }
            } else {
                $this->logger->log("Rule '{$rule->name}' conditions not met.");
            }
        }
        return $allOutcomes;
    }

    /**
     * Evaluates the conditions of a rule by checking its root expression.
     *
     * @param Rule $rule The Rule model instance.
     * @param mixed $data The data to evaluate conditions against.
     * @return bool True if conditions are met, false otherwise.
     */
    private function evaluateRuleConditions(Rule $rule, $data): bool
    {
        $rootExpression = $rule->rootExpression; // Fetches the related RuleExpression model

        if (!$rootExpression) {
            $this->logger->log("No root expression found for rule '{$rule->name}'. Assuming true (or false based on policy). For now, true.");
            // Depending on policy, a rule with no conditions might be always true or always false.
            // The original code assumed true if 'conditions' or 'items' were empty.
            return true;
        }

        return $this->evaluateExpression($rootExpression, $data);
    }

    /**
     * Evaluates a RuleExpression model instance.
     *
     * @param RuleExpression $expression The RuleExpression model instance.
     * @param mixed $data The data to evaluate conditions against.
     * @return bool The result of the expression evaluation.
     */
    private function evaluateExpression(RuleExpression $expression, $data): bool
    {
        $operator = strtoupper($expression->op); // 'AND', 'OR', 'NOT'

        // Child expressions (nested expressions)
        $childExpressions = $expression->ruleExpressions; // Fetches related RuleExpression models
        // Conditions directly under this expression
        $conditions = $expression->ruleConditions; // Fetches related RuleCondition models

        if ($operator === 'AND') {
            foreach ($childExpressions as $childExpr) {
                if (!$this->evaluateExpression($childExpr, $data)) {
                    return false; // Short-circuit AND
                }
            }
            foreach ($conditions as $condition) {
                if (!$this->evaluateSingleCondition($condition, $data)) {
                    return false; // Short-circuit AND
                }
            }
            return true; // All children and conditions are true
        } elseif ($operator === 'OR') {
            if (empty($childExpressions) && empty($conditions)) {
                 $this->logger->log("OR expression with no children or conditions. Rule ID: {$expression->rule_id}, Expr ID: {$expression->id}. Defaulting to false.");
                 return false; // An OR with no options is typically false
            }
            foreach ($childExpressions as $childExpr) {
                if ($this->evaluateExpression($childExpr, $data)) {
                    return true; // Short-circuit OR
                }
            }
            foreach ($conditions as $condition) {
                if ($this->evaluateSingleCondition($condition, $data)) {
                    return true; // Short-circuit OR
                }
            }
            return false; // No child or condition was true
        } elseif ($operator === 'NOT') {
            // A 'NOT' expression should ideally have exactly one child expression or one condition.
            if (count($childExpressions) === 1 && empty($conditions)) {
                return !$this->evaluateExpression($childExpressions[0], $data);
            } elseif (count($conditions) === 1 && empty($childExpressions)) {
                return !$this->evaluateSingleCondition($conditions[0], $data);
            } else {
                $this->logger->log("Invalid 'NOT' expression structure for Rule ID: {$expression->rule_id}, Expr ID: {$expression->id}. Expected 1 child expression or 1 condition.", LoggerService::LEVEL_WARNING);
                return false; // Undefined behavior for badly structured NOT
            }
        } elseif (empty($operator) && !empty($conditions)) {
            // This is a leaf expression node that only has conditions (implicitly ANDed if multiple, though typically one condition per leaf)
            // Or, it could be a root expression that doesn't have an operator but directly links to conditions.
             if (count($conditions) > 1) {
                 $this->logger->log("Expression node (ID: {$expression->id}) has multiple conditions but no explicit 'AND' or 'OR' operator. Evaluating as AND.", LoggerService::LEVEL_DEBUG);
             }
            foreach ($conditions as $condition) {
                if (!$this->evaluateSingleCondition($condition, $data)) {
                    return false;
                }
            }
            return true;
        } elseif (empty($operator) && empty($conditions) && empty($childExpressions)) {
             $this->logger->log("Empty expression (no operator, conditions, or child expressions) for Rule ID: {$expression->rule_id}, Expr ID: {$expression->id}. Defaulting to true.", LoggerService::LEVEL_DEBUG);
            return true; // Or false, depending on policy for empty expressions. Let's keep previous behavior of 'empty is true'.
        } else {
            $this->logger->log("Unsupported expression operator: '{$operator}' or invalid structure for Rule ID: {$expression->rule_id}, Expr ID: {$expression->id}", LoggerService::LEVEL_WARNING);
            return false;
        }
    }


    /**
     * Evaluates a single RuleCondition model instance.
     *
     * @param RuleCondition $condition The RuleCondition model instance.
     * @param mixed $data The data to evaluate the condition against.
     * @return bool True if the condition is met, false otherwise.
     */
    private function evaluateSingleCondition(RuleCondition $condition, $data): bool
    {
        $ruleModel = $condition->model; // Fetches related RuleModel
        if (!$ruleModel) {
            $this->logger->log("RuleCondition ID {$condition->id} has no associated RuleModel. Cannot evaluate.", LoggerService::LEVEL_WARNING);
            return false;
        }

        // Construct the attribute path.
        // $ruleModel->name could be the top-level key in $data (e.g., 'user', 'order')
        // $ruleModel->attribute is the path within that object (e.g., 'profile.name', 'totalAmount')
        // If $ruleModel->name is not meant to be part of the path (e.g. $data *is* the 'user' object), adjust this.
        // Assuming $data is a general context object, and $ruleModel->name is a key in it.
        // If $ruleModel->attribute already contains the full path like 'user.profile.name', then $ruleModel->name might be redundant here.
        // Based on RuleModel's 'attribute' description ("name" or "race->name" when model is "Player"),
        // it seems $ruleModel->name is the key in $data, and $ruleModel->attribute is the sub-path.
        $attributeKey = $ruleModel->name; // e.g. 'user'
        $subPath = $ruleModel->attribute; // e.g. 'status' or 'profile.age'

        $fullPath = $subPath; // Default to subPath if attributeKey is not a valid key for $data, or if $data is already specific object
        if (is_array($data) && isset($data[$attributeKey])) {
            $contextObject = $data[$attributeKey];
        } elseif (is_object($data) && property_exists($data, $attributeKey)) {
            $contextObject = $data->{$attributeKey};
        } else {
            // $attributeKey is not found in $data, or $data is not an array/object that can contain $attributeKey.
            // This might mean $data is already the specific object $attributeKey refers to.
            // Or, $ruleModel->attribute is the full path.
            // For now, we assume $subPath might be the full path relative to $data if $attributeKey isn't directly applicable.
            // This logic might need refinement based on how $data is structured and how RuleModel is used.
            // A safer bet if $ruleModel->name is 'user' and $ruleModel->attribute is 'status':
            // $fullPath = $ruleModel->name . '.' . $ruleModel->attribute; // results in 'user.status'
            // And $contextObject = $data;
            // Let's try constructing path like 'user.profile.name' and fetch from $data.
            $fullPath = $attributeKey . '.' . $subPath;
            $contextObject = $data; // fetchValueFromObject will take $data and the full path
             $this->logger->log("Attempting to fetch '{$fullPath}' from main data context.", LoggerService::LEVEL_DEBUG);
        }


        // TODO: Handle $ruleModel->is_method and $condition->method_param if methods need to be called.
        // Current fetchValueFromObject does not support method calls. This is a known limitation.
        if ($ruleModel->is_method) {
            $this->logger->log("Method call evaluation for '{$fullPath}' is not implemented. Condition ID: {$condition->id}", LoggerService::LEVEL_WARNING);
            // return false; // Or handle as per requirements for unimplemented features
        }

        $actualValue = $this->fetchValueFromObject($contextObject, $subPath); // Path is relative to the context object found via attributeKey
                                                                         // OR $this->fetchValueFromObject($data, $fullPath) if $contextObject was just $data

        $operator = $condition->comparator;
        $expectedValue = $condition->val;
        // The 'val' from DB might be a string representation of boolean/integer. Coerce if necessary.
        // For example, if $actualValue is boolean true, $expectedValue 'true' should match.
        // This is tricky. For now, direct comparison. Add type casting if issues arise.
        if (is_bool($actualValue)) {
            if (strtolower($expectedValue) === 'true') $expectedValue = true;
            elseif (strtolower($expectedValue) === 'false') $expectedValue = false;
        } elseif (is_numeric($actualValue) && is_numeric($expectedValue)) {
            // Potentially cast $expectedValue if it's a numeric string from DB
             $expectedValue = is_string($expectedValue) ? (ctype_digit($expectedValue) ? (int)$expectedValue : (float)$expectedValue) : $expectedValue;
        }


        $allowNull = false; // RuleCondition doesn't have an 'allowNull' field. Assume false.
                           // The 'target' field in RuleCondition is different: "whether the condition should be fulfilled (target=true) or not (target=false)"
                           // This means if target=false, the result of the comparison should be negated.

        if ($actualValue === null && !$allowNull) {
            $this->logger->log("Attribute '{$fullPath}' is null (and null is not allowed by default), condition not met for evaluation. Condition ID: {$condition->id}");
            // This check might be redundant if 'is_null' operator is used.
            // For other operators, null value might lead to unexpected results, so failing early can be good.
            // However, an operator like '!=' might expect null. e.g. value != 'some_string' (true if value is null)
            // Let operators handle null, except for specific cases.
            // The original check was: if ($actualValue === null && ArrayHelper::getValue($condition, 'allowNull', false) !== true)
            // Since we don't have 'allowNull', this specific early exit is removed.
        }

        $result = false;
        switch ($operator) {
            case '==':
                // Using loose comparison to match behavior of many template/config engines.
                // For strict comparison, use '==='.
                return $actualValue == $expectedValue;
            case '===': // Added strict equality
                return $actualValue === $expectedValue;
            case '!=':
                return $actualValue != $expectedValue;
            case '!==': // Added strict inequality
                return $actualValue !== $expectedValue;
            case '>':
                return $actualValue > $expectedValue;
            case '<':
                return $actualValue < $expectedValue;
            case '>=':
                return $actualValue >= $expectedValue;
            case '<=':
                return $actualValue <= $expectedValue;
            case 'contains':
                return is_string($actualValue) && strpos($actualValue, $expectedValue) !== false;
            case 'not_contains':
                 return !is_string($actualValue) || strpos($actualValue, $expectedValue) === false;
            case 'in':
                // Ensure $actualValue is scalar or can be checked in an array. $expectedValue must be an array.
                return is_array($expectedValue) && in_array($actualValue, $expectedValue);
            case 'not_in':
                return is_array($expectedValue) && !in_array($actualValue, $expectedValue);
            case 'is_null': // Added is_null check
                return $actualValue === null;
            case 'is_not_null': // Added is_not_null check
                return $actualValue !== null;
            // Add more operators as needed (e.g., regex matching)
            default:
                $this->logger->log("Unsupported operator: {$operator} for attribute '{$fullPath}', Condition ID: {$condition->id}");
                return false;
        }
    }

    /**
     * Fetches a value from an object or array using a dot-separated path.
     *
     * @param mixed $objectOrArray The object or array to fetch from.
     * @param string $path The dot-separated path (e.g., 'user.profile.name').
     * @param mixed $defaultValue The default value if the path is not found.
     * @return mixed The fetched value or the default value.
     */
    private function fetchValueFromObject($objectOrArray, string $path, $defaultValue = null)
    {
        // Ensure $objectOrArray is an array for ArrayHelper::getValue
        // Using ArrayHelper::getValue for robust deep fetching from arrays or objects with array-like access.
        // If $objectOrArray is an object, ArrayHelper::getValue can often still access public properties
        // or properties accessible via ArrayAccess interface.
        // For more complex object graph traversal (e.g., calling getter methods), this might need enhancement.
        $value = ArrayHelper::getValue($objectOrArray, $path, $defaultValue);

        // Logging for when value is not found (i.e., default value is returned)
        // and the default value was used.
        if ($value === $defaultValue) {
            // To avoid logging spam if defaultValue is commonly used and expected,
            // one might add more specific checks, e.g., if $defaultValue itself is a special marker object.
            // For now, just logging if path might be unresolved.
            // A more sophisticated check could try to see if the path partially resolves.
            $this->logger->log("Path '{$path}' resolved to default value. Actual value may be missing or same as default.", LoggerService::LEVEL_DEBUG);
        }

        return $value;
    }


    /**
     * Executes the actions defined in a rule.
     *
     * @param Rule $rule The Rule model instance.
     * @param mixed $data The data that met the rule conditions.
     * @return array Collected outcomes from the executed actions.
     */
    private function executeRuleActions(Rule $rule, $data): array
    {
        $outcomes = [];
        $ruleActions = $rule->ruleActions; // Fetches related RuleAction models

        if (empty($ruleActions)) {
            $this->logger->log("No actions to execute for rule '{$rule->name}'.");
            return $outcomes;
        }

        foreach ($ruleActions as $ruleAction) {
            $actionName = $ruleAction->name; // e.g., 'assignRole', 'sendEmail'

            // Params for actions:
            // This is the part that was unclear. Assuming RuleAction has a `params` attribute (e.g., JSON decoded).
            // If params are derived from $ruleAction->model, the logic would be:
            // $actionParams = [];
            // if ($ruleAction->model) {
            //    $actionParams[$ruleAction->model->name] = $ruleAction->model->attribute;
            // }
            // For now, let's assume $ruleAction->params exists as an array (e.g. from a JSON column in rule_action table)
            // This property `params` is not defined in the RuleAction model snippet, so this is an assumption.
            $params = [];
            if (property_exists($ruleAction, 'params') && is_string($ruleAction->params)) {
                 $decodedParams = json_decode($ruleAction->params, true);
                 if (json_last_error() === JSON_ERROR_NONE) {
                     $params = $decodedParams;
                 } else {
                     $this->logger->log("Failed to decode params JSON for RuleAction ID {$ruleAction->id}: " . json_last_error_msg(), LoggerService::LEVEL_ERROR);
                 }
            } elseif (property_exists($ruleAction, 'params') && is_array($ruleAction->params)) {
                $params = $ruleAction->params;
            } else {
                 // Fallback or alternative way to get params if `params` attribute doesn't exist.
                 // For example, using the associated RuleModel on RuleAction (if any, $ruleAction->model):
                 if ($ruleAction->model) {
                     // This structure is a guess: model->name as key, model->attribute as value.
                     // Or it could be that $ruleAction->model itself contains multiple parameter fields.
                     // This part is highly dependent on the actual design of RuleAction and its parameters.
                     $params[$ruleAction->model->name] = $ruleAction->model->attribute;
                     $this->logger->log("Using RuleAction's associated RuleModel for params: key='{$ruleAction->model->name}', value='{$ruleAction->model->attribute}'", LoggerService::LEVEL_DEBUG);
                 } else {
                    $this->logger->log("RuleAction ID {$ruleAction->id} has no 'params' attribute (or it's not a valid JSON string/array) and no associated RuleModel for parameters.", LoggerService::LEVEL_DEBUG);
                 }
            }

            $this->logger->log("Executing action '{$actionName}' for rule '{$rule->name}' with params: " . json_encode($params));

            // Example: Dynamically call a method within this service or another service
            // This needs to be securely implemented, e.g., by whitelisting callable actions
            // or mapping action names to specific service methods.

            $methodName = 'action' . ucfirst($actionName); // e.g., actionAssignRole

            if (method_exists($this, $methodName)) {
                try {
                    // Pass original data and action-specific parameters
                    $actionOutcome = $this->$methodName($data, $params);
                    if (is_array($actionOutcome) && isset($actionOutcome['status'])) {
                        $outcomes[] = $actionOutcome;
                        $this->logger->log("Action '{$actionName}' executed successfully with outcome.", ['outcome_status' => $actionOutcome['status']]);
                    } else {
                        $this->logger->log("Action '{$actionName}' executed but returned an invalid or no outcome structure.", [], LoggerService::LEVEL_WARNING);
                    }
                } catch (\Exception $e) {
                    $this->logger->log("Error executing action '{$actionName}': " . $e->getMessage(), LoggerService::LEVEL_ERROR);
                    // Optionally, create a failure outcome
                    // $outcomes[] = ['status' => 'error', 'action_name' => $actionName, 'message' => $e->getMessage()];
                }
            } else {
                $this->logger->log("Action method '{$methodName}' not found in RuleEngineService.", LoggerService::LEVEL_WARNING);
                // Potentially delegate to other services or components
                // Yii::$app->runAction('controller/action', $params); // If it's a controller action
                // Yii::$app->get('someService')->executeAction($actionName, $data, $params);
            }
        }
    }

    // Placeholder for actual action implementations
    // These would interact with other parts of your application and should return structured outcomes.

    protected function actionAssignRole($data, array $params): ?array
    {
        $userId = $this->fetchValueFromObject($data, 'player.id'); // Corrected path assuming 'player' is a key in $data context
        $role = ArrayHelper::getValue($params, 'role'); // 'role' would be $ruleAction->model->name

        if ($userId && $role) {
            $this->logger->log("Attempting to assign role '{$role}' to user ID '{$userId}'. (Placeholder)");
            // Actual role assignment logic would be here.
            return [
                'status' => 'success',
                'message_key' => 'role_assigned', // For potential i18n on client
                'data' => ['user_id' => $userId, 'role_assigned' => $role],
                'broadcast_scope' => 'player', // 'player', 'quest', 'session', 'all'
                'broadcast_target_id' => $this->fetchValueFromObject($data, 'clientId'), // For 'player' scope
                'broadcast_type' => 'PLAYER_ROLE_UPDATED' // Client-side message type
            ];
        } else {
            $this->logger->log("Missing user ID or role for actionAssignRole.", LoggerService::LEVEL_WARNING);
            return ['status' => 'failure', 'message_key' => 'assign_role_failed_params', 'data' => $params];
        }
    }

    protected function actionSendWelcomeEmail($data, array $params): ?array
    {
        $email = $this->fetchValueFromObject($data, 'player.email');  // Corrected path
        $template = ArrayHelper::getValue($params, 'template'); // 'template' from $ruleAction->model->name

        if ($email && $template) {
            $this->logger->log("Attempting to send welcome email template '{$template}' to '{$email}'. (Placeholder)");
            // Actual email sending logic
            return [
                'status' => 'success',
                'message_key' => 'welcome_email_sent',
                'data' => ['email' => $email, 'template' => $template],
                'broadcast_scope' => 'none', // Or 'player_internal' if no client broadcast needed
                'broadcast_type' => 'INTERNAL_NOTIFICATION'
            ];
        } else {
            $this->logger->log("Missing email or template for actionSendWelcomeEmail.", LoggerService::LEVEL_WARNING);
            return ['status' => 'failure', 'message_key' => 'send_email_failed_params', 'data' => $params];
        }
    }

    protected function actionSendAdminNotification($data, array $params): ?array
    {
        $message = ArrayHelper::getValue($params, 'message', 'Admin notification triggered.');
        $adminEmail = 'admin@example.com'; // This should be a configuration
        $this->logger->log("Attempting to send admin notification: '{$message}'. (Placeholder)");
        // Actual notification logic
        return [
            'status' => 'success',
            'message_key' => 'admin_notified',
            'data' => ['message' => $message],
            'broadcast_scope' => 'none', // Typically admin notifications are not broadcast to players
            'broadcast_type' => 'ADMIN_ALERT'
        ];
    }

    protected function actionApplyDiscount($data, array $params): ?array
    {
        // Assuming $data contains an 'order' object/array, and 'order_id' is a param
        $orderId = $this->fetchValueFromObject($data, 'eventData.details.order_id'); // Example: if order_id is in event details
        $percentage = ArrayHelper::getValue($params, 'percentage');

        if ($orderId && $percentage) {
            $this->logger->log("Attempting to apply {$percentage}% discount to order ID '{$orderId}'. (Placeholder)");
            // Actual discount logic
            return [
                'status' => 'success',
                'message_key' => 'discount_applied',
                'data' => ['order_id' => $orderId, 'discount_percentage' => $percentage],
                'broadcast_scope' => 'player', // Notify the player who owns the order
                'broadcast_target_id' => $this->fetchValueFromObject($data, 'clientId'),
                'broadcast_type' => 'ORDER_UPDATED'
            ];
        } else {
            $this->logger->log("Missing order ID or percentage for actionApplyDiscount.", LoggerService::LEVEL_WARNING);
            return ['status' => 'failure', 'message_key' => 'apply_discount_failed_params', 'data' => $params];
        }
    }
}
