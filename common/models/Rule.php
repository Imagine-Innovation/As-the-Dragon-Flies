<?php

namespace common\models;

use common\components\AppStatus;
use common\components\RuleNode;
use common\components\RuleParser;
use common\components\RuleValidator;
use common\helpers\ModelHelper;
use common\helpers\RichTextHelper;
use common\models\RuleCondition;
use common\models\RuleExpression;
use common\models\RuleModel;
use Yii;

/**
 * This is the model class for table "rule".
 *
 * @property int $id Primary key.
 * @property string $name Name of the rule
 * @property string $definition A text description of the rule in human readable terms
 * @property string|null $description Any additional explanations
 * @property int $status Status of the rule (Deleted=0, Inactive=9, Active=10)
 * @property int|null $created_at Creation timestamp
 * @property int|null $updated_at Last update timestamp
 *
 * @property RuleAction[] $ruleActions
 * @property RuleCondition[] $ruleConditions
 * @property RuleExpression[] $ruleExpressions
 * @property RuleExpression $rootExpression
 */
class Rule extends \yii\db\ActiveRecord
{

    private ?RuleNode $parsingTree = null;
    public string $errorMessage;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['name', 'definition'], 'required'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['definition'], 'string', 'max' => 256],
            [['definition'], RuleValidator::class],
            ['status', 'default', 'value' => AppStatus::INACTIVE->value],
            ['status', 'in', 'range' => AppStatus::getValuesForPlayer()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key.',
            'name' => 'Name of the rule',
            'definition' => 'A text description of the rule in human readable terms',
            'description' => 'Any additional explanations',
            'status' => 'Status of the rule (Deleted=0, Inactive=9, Active=10)',
            'created_at' => 'Creation timestamp',
            'updated_at' => 'Last update timestamp',
        ];
    }

    /**
     * Gets query for [[RuleActions]].
     *
     * @return \yii\db\ActiveQuery<RuleAction>
     */
    public function getRuleActions()
    {
        return $this->hasMany(RuleAction::class, ['rule_id' => 'id']);
    }

    /**
     * Gets query for [[RuleConditions]].
     *
     * @return \yii\db\ActiveQuery<RuleCondition>
     */
    public function getRuleConditions()
    {
        return $this->hasMany(RuleCondition::class, ['rule_id' => 'id']);
    }

    /**
     * Gets query for [[RuleExpressions]].
     *
     * @return \yii\db\ActiveQuery<RuleExpression>
     */
    public function getRuleExpressions()
    {
        return $this->hasMany(RuleExpression::class, ['rule_id' => 'id']);
    }

    /**
     * Gets query for [[RootExpression]].
     *
     * @return \yii\db\ActiveQuery<RuleExpression>
     */
    public function getRootExpression()
    {
        return $this->hasOne(RuleExpression::class, ['rule_id' => 'id', 'parent_id' => null]);
    }

    /**
     *
     * Custom properties
     *
     */

    /**
     * Validates the rule definition.
     *
     * This function checks if the rule definition is valid by using the
     * RuleParser component.
     * It parses the rule definition and sets the parsing tree and error message
     * accordingly.
     *
     * @return bool Whether the rule definition is valid.
     */
    public function isValidDefinition(): bool
    {
        // Initialize the match variable to false.
        $match = false;

        // Check if the rule definition exists.
        if ($this->definition) {
            // Create a new instance of the RuleParser component.
            $parser = new RuleParser();

            // Parse the rule definition using the RuleParser.
            // The parseRule method returns whether the parsing was successful.
            $match = $parser->parseRule($this->definition);

            // Set the parsing tree from the parser.
            $this->parsingTree = $parser->parsingTree;

            // Set the error message from the parser.
            $this->errorMessage = $parser->errorMessage;
        }

        // Return whether the rule definition is valid.
        return $match;
    }

    /**
     * Saves the rule definition.
     *
     * This method validates the rule definition and then saves it by calling the
     * saveParsingTree method with the parsing tree and the rule ID.
     * If the rule definition is valid and the save operation is successful,
     * the method returns true. If the rule definition is invalid or the save
     * operation fails, the method throws an exception.
     *
     * @return bool Whether the save operation is successful (true) or not.
     * @throws \Exception if the rule model is missing or the rule definition is invalid.
     */
    public function saveRuleDefinition(): bool
    {
        // Check if there is an error message indicating a missing rule model.
        if ($this->errorMessage !== '') {
            // Log the error message for debugging purposes.
            Yii::debug("*** Debug *** saveRuleDefinition => ERROR: $this->errorMessage", __METHOD__);

            // If there is an error message, throw an exception indicating a missing rule model.
            throw new \Exception('Missing Rule model');
        }

        // Save the parsing tree with the rule ID.
        // The saveParsingTree method is expected to handle the actual saving process.
        return $this->saveParsingTree($this->parsingTree, $this->id);
    }

    /**
     * Saves the parsing tree of the rule.
     *
     * This method saves the parsing tree of the rule by delegating the saving
     * operation to the appropriate method based on the type of expression.
     * It handles different types of expressions such as condition, boolean
     * expression, regular expression, and negate.
     *
     * @param RuleNode|null $expression The parsing tree expression to save.
     * @param int|null $ruleId The ID of the rule associated with the parsing tree.
     * @param int|null $parentId The ID of the parent node in the parsing tree (optional).
     * @return bool Whether the save operation is successful (true) or not.
     */
    private function saveParsingTree(?RuleNode $expression = null, ?int $ruleId = null, ?int $parentId = null): bool
    {

        if ($expression === null || $ruleId === null) {
            return false;
        }

        if ($expression->type === 'condition') {
            $parentId = $parentId ?? $this->newRuleExpression($ruleId, null);
        }

        return match ($expression->type) {
            'condition' => $this->saveRuleCondition($expression->node, $ruleId, $parentId),
            'booleanExpression' => $this->saveRuleBoolExpression($expression, $ruleId, $parentId),
            'expression' => $this->saveRuleExpression($expression, $ruleId, $parentId),
            'negate' => $this->saveNegativeExpression($expression->node, $ruleId, $parentId),
            default => false
        };
    }

    /**
     * Saves a rule condition node in the parsing tree.
     *
     * This method creates and saves a rule condition node in the parsing tree.
     * It initializes a new RuleCondition model, assigns values to its attributes,
     * and saves it in the database.
     *
     * @param RuleNode|null $condition The condition node to save.
     * @param int|null $ruleId The ID of the rule associated with the condition.
     * @param int|null $parentId The ID of the parent node in the parsing tree.
     * @return bool Whether the save operation is successful (true) or not.
     * @throws \Exception
     */
    private function saveRuleCondition(?RuleNode $condition = null, ?int $ruleId = null, ?int $parentId = null): bool
    {
        if ($condition === null || $ruleId === null) {
            return false;
        }

        // Get the Rule model associated with the condition component.
        $ruleModel = $this->getRuleModel($condition->component ?? 'Unknown');

        // Create a new instance of RuleCondition model with the provided attributes.
        $cond = new RuleCondition([
            'rule_id' => $ruleId,
            'expression_id' => $parentId,
            'model_id' => $ruleModel->id,
            'comparator' => $condition->comparator,
            'val' => $condition->value
        ]);

        $successfullySaved = $cond->save();
        if ($successfullySaved) {
            return true;
        }
        throw new \Exception(implode("<br />", ArrayHelper::getColumn($cond->errors, 0, false)));
    }

    /**
     * Creates and saves a new rule expression node.
     *
     * This method creates a new RuleExpression model, assigns values to its
     * attributes, saves it in the database, and returns the ID of the newly
     * created RuleExpression.
     *
     * @param int $ruleId The ID of the rule associated with the expression.
     * @param int|null $parentId The ID of the parent node in the parsing tree (optional).
     * @param string|null $op The operator for the rule expression (optional).
     * @return int The ID of the newly created RuleExpression.
     * @throws \Exception
     */
    private function newRuleExpression(int $ruleId, ?int $parentId = null, ?string $op = null): int
    {
        $expr = new RuleExpression([
            'rule_id' => $ruleId,
            'parent_id' => $parentId,
            'op' => $op
        ]);

        $successfullySaved = $expr->save();
        if ($successfullySaved) {
            return $expr->id;
        }
        throw new \Exception(implode("<br />", ArrayHelper::getColumn($expr->errors, 0, false)));
    }

    /**
     * Saves a rule expression node in the parsing tree.
     *
     * This method creates and saves a rule expression node in the parsing tree.
     * It initializes a new RuleExpression model, assigns values to its attributes,
     * and saves it in the database. Additionally, it recursively saves the child
     * nodes (left and right expressions) if they exist.
     *
     * @param RuleNode $expression The expression node to save.
     * @param int $ruleId The ID of the rule associated with the expression.
     * @param int|null $parentId The ID of the parent node in the parsing tree (optional).
     * @return bool Whether the save operation is successful (true) or not.
     */
    private function saveRuleExpression(RuleNode $expression, int $ruleId, ?int $parentId = null): bool
    {
        // Initialize the save status to false.
        $saveStatus = false;

        // Create and save a new rule expression node, and get its ID.
        $id = $this->newRuleExpression($ruleId, $parentId);

        // If the expression node was successfully saved, proceed to save its child node.
        if ($id) {
            // Recursively save the child node of the expression.
            $saveStatus = $this->saveParsingTree($expression->node, $ruleId, $id);
        }

        // Return the result of the save operation.
        return $saveStatus;
    }

    /**
     * Saves a rule negative expression node in the parsing tree.
     *
     * This method creates and saves a rule expression node in the parsing tree.
     * It initializes a new RuleExpression model with a 'not' operator, assigns values
     * to its attributes, and saves it in the database. Additionally, it recursively
     * saves the child nodes (left and right expressions) if they exist.
     *
     * @param RuleNode|null $expression The expression node to save.
     * @param int|null $ruleId The ID of the rule associated with the expression.
     * @param int|null $parentId The ID of the parent node in the parsing tree (optional).
     * @return bool Whether the save operation is successful (true) or not.
     */
    private function saveNegativeExpression(?RuleNode $expression = null, ?int $ruleId = null, ?int $parentId = null)
    {
        if ($expression === null || $ruleId === null) {
            return false;
        }

        $id = $this->newRuleExpression($ruleId, $parentId, 'not');

        $saveStatus = false;
        if ($id) {
            $saveStatus = $this->saveParsingTree($expression, $ruleId, $id);
        }
        return $saveStatus;
    }

    /**
     * Saves a rule boolean expression node in the parsing tree.
     *
     * This method creates and saves a rule expression node in the parsing tree.
     * It initializes a new RuleExpression model, assigns values to its attributes,
     * and saves it in the database. Additionally, it recursively saves the child
     * nodes (left and right expressions) if they exist.
     *
     * @param RuleNode|null $expression The expression node to save.
     * @param int|null $ruleId The ID of the rule associated with the expression.
     * @param int|null $parentId The ID of the parent node in the parsing tree (optional).
     * @return bool Whether the save operation is successful (true) or not.
     */
    private function saveRuleBoolExpression(?RuleNode $expression = null, ?int $ruleId = null, ?int $parentId = null): bool
    {
        if ($expression === null || $ruleId === null) {
            return false;
        }

        $id = $this->newRuleExpression($ruleId, $parentId, $expression->boolOperator);

        $saveStatus = false;
        if ($id) {
            $saveStatus = $this->saveParsingTree($expression->left, $ruleId, $id);

            if ($saveStatus && $expression->boolOperator !== null) {
                // If saving the left expression is successful and
                // the boolean operator is not empty, save the right expression node.
                $saveStatus = $this->saveParsingTree($expression->right, $ruleId, $id);
            }
        }
        return $saveStatus;
    }

    /**
     * Retrieves or creates a RuleModel instance based on the provided component.
     *
     * This method parses the component string to extract the model name and
     * attribute/method. It then searches for an existing RuleModel instance with
     * the same name and attribute/method. If no matching instance is found,
     * it creates a new RuleModel instance and saves it in the database.
     *
     * @param string $component the component string (model and attribute/method)
     * @return RuleModel the retrieved or created RuleModel instance
     * @throws \Exception if failed to save the RuleModel model
     */
    private function getRuleModel(string $component): RuleModel
    {
        Yii::debug("*** debug *** getRuleModel - component={$component}");

        // Split the field name into model and attribute/method parts
        $parts = explode('->', $component, 2);
        $modelName = $parts[0];

        // Initialize variables for attribute/method and method indicator
        $modelAttribute = null;
        $isMethod = false;

        // Check if the attribute part exists
        if (isset($parts[1])) {
            // Check if the attribute part is a method (ends with '()')
            $parenthesis = strpos($parts[1], '(');
            $isMethod = ($parenthesis !== false);
            // Remove '()' from the attribute part if it's a method
            $modelAttribute = $isMethod ? substr($parts[1], 0, $parenthesis) : $parts[1];
        }

        Yii::debug("*** debug *** getRuleModel - modelName={$modelName}, modelAttribute={$modelAttribute}");

        // Find an existing RuleModel instance by name and attribute/method
        $model = RuleModel::findOne(['name' => $modelName, 'attribute' => $modelAttribute]);

        // If no matching RuleModel instance is found, create a new one
        if ($model === null) {
            $path = ModelHelper::path($modelName);
            $data = [
                'path' => $path,
                'name' => $modelName,
                'attribute' => $modelAttribute,
                'is_method' => ($isMethod ? 1 : 0)
            ];

            Yii::debug($data);

            $model = new RuleModel($data);

            // Save the new RuleModel instance in the database
            $successfullySaved = $model->save();
            if (!$successfullySaved) {
                throw new \Exception(implode("<br />", ArrayHelper::getColumn($model->errors, 0, false)));
            }
        }

        // Return the retrieved or created RuleModel instance
        return $model;
    }
}
