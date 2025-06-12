<?php

namespace common\tests\unit\components;

use PHPUnit\Framework\TestCase;
use common\components\RuleEngineService;
use common\components\LoggerService; // Assuming this is the correct LoggerService
use common\models\Rule;
use common\models\RuleExpression;
use common\models\RuleCondition;
use common\models\RuleAction;
use common\models\RuleModel;
use stdClass; // For mocking simple model objects

// Mock AppStatus if it's not available in the test environment
if (!class_exists('common\enums\AppStatus')) {
    // This is a very basic mock. Adjust if it needs specific enum behavior.
    class MockAppStatus {
        const ACTIVE = 10;
        public $value;
        public function __construct($value) { $this->value = $value; }
        public static function ACTIVE() { return new self(self::ACTIVE); }
    }
    // Define the alias if RuleEngineService uses it directly
    class_alias('common\tests\unit\components\MockAppStatus', 'common\enums\AppStatus');
}


class RuleEngineServiceTest extends TestCase
{
    private $ruleEngineService;
    private $loggerMock;

    protected function setUp(): void
    {
        // Mock LoggerService
        // Using PHPUnit's built-in mocking capabilities if available,
        // otherwise a simple stdClass or a custom mock class.
        // For this environment, I'll assume a simple approach.
        $this->loggerMock = $this->createMock(LoggerService::class);
        // $this->loggerMock = new class extends LoggerService {
        //     public function __construct() {} // Override constructor if it has complex deps
        //     public function log($message, $data = null, $level = null) {}
        //     public function logStart($message, $data = null) {}
        //     public function logEnd($message, $data = null) {}
        // };

        $this->ruleEngineService = new RuleEngineService($this->loggerMock);
    }

    /**
     * Helper to call private/protected methods for testing.
     */
    private function callPrivateMethod($object, $methodName, array $parameters)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    // --- Tests for evaluateSingleCondition ---

    private function createMockRuleCondition(string $attributePath, string $operator, $expectedValue, bool $isMethod = false): array
    {
        // In the actual service, $condition is a RuleCondition model.
        // We mock the necessary structure or an object that provides these properties.
        // The current `evaluateSingleCondition` in RuleEngineService expects an array.
        // Let's adapt based on the latest version of RuleEngineService which uses RuleCondition objects.

        $mockModel = new stdClass(); // Simulates RuleModel
        // Path construction in evaluateSingleCondition is a bit complex.
        // It tries $data[$attributeKey] then $data[$attributeKey]->{$subPath} or $data with full path.
        // For simplicity, let's assume attributePath is the final path to fetch.
        // Example: 'user.status' or 'eventData.details.value'
        // The RuleModel would have 'name' (e.g. 'user') and 'attribute' (e.g. 'status')

        $pathParts = explode('.', $attributePath, 2);
        $mockModel->name = $pathParts[0];
        $mockModel->attribute = $pathParts[1] ?? ''; // If no dot, attribute is empty, name is the key
        $mockModel->is_method = $isMethod;

        $mockCondition = new stdClass(); // Simulates RuleCondition
        $mockCondition->model = $mockModel; // Relation to RuleModel
        $mockCondition->comparator = $operator;
        $mockCondition->val = $expectedValue;
        // $mockCondition->target = true; // Assuming default target is true (not yet used by current evaluateSingleCondition)
        // $mockCondition->method_param = null; // For method calls

        return ['condition' => $mockCondition, 'model' => $mockModel];
    }

    /**
     * @dataProvider singleConditionDataProvider
     */
    public function testEvaluateSingleCondition($conditionSetup, $data, $expectedResult)
    {
        // The actual evaluateSingleCondition expects a RuleCondition object.
        // The helper createMockRuleCondition creates stdClass, which is fine for property access.
        $mocks = $this->createMockRuleCondition($conditionSetup['attribute'], $conditionSetup['operator'], $conditionSetup['value']);

        $result = $this->callPrivateMethod(
            $this->ruleEngineService,
            'evaluateSingleCondition',
            [$mocks['condition'], $data]
        );
        $this->assertEquals($expectedResult, $result, "Failed for {$conditionSetup['attribute']} {$conditionSetup['operator']} {$conditionSetup['value']}");
    }

    public static function singleConditionDataProvider(): array
    {
        $userObj = new stdClass();
        $userObj->id = 1;
        $userObj->status = 'active';
        $userObj->level = 10;
        $userObj->points = 100;
        $userObj->tags = ['vip', 'new'];
        $userObj->profile = new stdClass();
        $userObj->profile->name = 'John Doe';
        $userObj->profile->is_verified = true;
        $userObj->nullable_field = null;

        $data = ['user' => $userObj, 'score' => 50, 'event_type' => 'login_success'];

        return [
            // Operator: ==
            [['attribute' => 'user.status', 'operator' => '==', 'value' => 'active'], $data, true],
            [['attribute' => 'user.status', 'operator' => '==', 'value' => 'inactive'], $data, false],
            [['attribute' => 'user.level', 'operator' => '==', 'value' => 10], $data, true],
            [['attribute' => 'user.level', 'operator' => '==', 'value' => '10'], $data, true], // Coercion
            [['attribute' => 'user.profile.is_verified', 'operator' => '==', 'value' => true], $data, true],
            [['attribute' => 'user.profile.is_verified', 'operator' => '==', 'value' => 'true'], $data, true],


            // Operator: ===
            [['attribute' => 'user.level', 'operator' => '===', 'value' => 10], $data, true],
            [['attribute' => 'user.level', 'operator' => '===', 'value' => '10'], $data, false], // Strict
            [['attribute' => 'user.profile.is_verified', 'operator' => '===', 'value' => true], $data, true],

            // Operator: !=
            [['attribute' => 'user.status', 'operator' => '!=', 'value' => 'inactive'], $data, true],
            [['attribute' => 'user.level', 'operator' => '!=', 'value' => 5], $data, true],

            // Operator: !==
            [['attribute' => 'user.level', 'operator' => '!==', 'value' => '10'], $data, true], // Strict

            // Operator: >
            [['attribute' => 'user.level', 'operator' => '>', 'value' => 5], $data, true],
            [['attribute' => 'user.level', 'operator' => '>', 'value' => 10], $data, false],

            // Operator: >=
            [['attribute' => 'user.level', 'operator' => '>=', 'value' => 10], $data, true],
            [['attribute' => 'user.level', 'operator' => '>=', 'value' => 9], $data, true],

            // Operator: <
            [['attribute' => 'user.points', 'operator' => '<', 'value' => 200], $data, true],
            [['attribute' => 'user.points', 'operator' => '<', 'value' => 100], $data, false],

            // Operator: <=
            [['attribute' => 'user.points', 'operator' => '<=', 'value' => 100], $data, true],
            [['attribute' => 'user.points', 'operator' => '<=', 'value' => 101], $data, true],

            // Operator: contains (for strings)
            [['attribute' => 'user.status', 'operator' => 'contains', 'value' => 'act'], $data, true],
            [['attribute' => 'user.status', 'operator' => 'contains', 'value' => 'ive'], $data, true],
            [['attribute' => 'user.status', 'operator' => 'contains', 'value' => 'xyz'], $data, false],

            // Operator: not_contains
            [['attribute' => 'user.status', 'operator' => 'not_contains', 'value' => 'xyz'], $data, true],
            [['attribute' => 'user.status', 'operator' => 'not_contains', 'value' => 'act'], $data, false],

            // Operator: in (for array membership)
            [['attribute' => 'user.status', 'operator' => 'in', 'value' => ['active', 'pending']], $data, true],
            [['attribute' => 'user.status', 'operator' => 'in', 'value' => ['pending', 'archived']], $data, false],
            // Test 'in' where the fetched value is one of the elements
            [['attribute' => 'user.level', 'operator' => 'in', 'value' => [5, 10, 15]], $data, true],


            // Operator: not_in
            [['attribute' => 'user.status', 'operator' => 'not_in', 'value' => ['pending', 'archived']], $data, true],
            [['attribute' => 'user.status', 'operator' => 'not_in', 'value' => ['active', 'pending']], $data, false],

            // Operator: is_null
            [['attribute' => 'user.nullable_field', 'operator' => 'is_null', 'value' => null], $data, true], // Value for is_null is not used by operator but good to pass
            [['attribute' => 'user.status', 'operator' => 'is_null', 'value' => null], $data, false],
            [['attribute' => 'user.non_existent_field', 'operator' => 'is_null', 'value' => null], $data, true], // Non-existent fields are fetched as null

            // Operator: is_not_null
            [['attribute' => 'user.status', 'operator' => 'is_not_null', 'value' => null], $data, true],
            [['attribute' => 'user.nullable_field', 'operator' => 'is_not_null', 'value' => null], $data, false],
            [['attribute' => 'user.non_existent_field', 'operator' => 'is_not_null', 'value' => null], $data, false],

            // Direct key access from $data
            [['attribute' => 'score', 'operator' => '==', 'value' => 50], $data, true],
        ];
    }

    // --- Tests for evaluateExpression ---

    private function createMockExpression(string $operator = null, array $childConditions = [], array $childExpressions = []): stdClass
    {
        $mockExpr = new stdClass(); // Simulates RuleExpression
        $mockExpr->op = $operator;

        $conditionObjects = [];
        foreach ($childConditions as $condSetup) {
            $mocks = $this->createMockRuleCondition($condSetup['attribute'], $condSetup['operator'], $condSetup['value']);
            $conditionObjects[] = $mocks['condition'];
        }
        $mockExpr->ruleConditions = $conditionObjects; // Relation

        $expressionObjects = [];
        foreach ($childExpressions as $childExprSetup) {
            $expressionObjects[] = $this->createMockExpression(
                $childExprSetup['operator'] ?? null,
                $childExprSetup['conditions'] ?? [],
                $childExprSetup['expressions'] ?? []
            );
        }
        $mockExpr->ruleExpressions = $expressionObjects; // Relation
        $mockExpr->rule_id = 1; // Dummy rule_id for logging
        $mockExpr->id = rand(1,1000); // Dummy expression id for logging
        return $mockExpr;
    }

    /**
     * @dataProvider expressionDataProvider
     */
    public function testEvaluateExpression($expressionSetup, $data, $expectedResult)
    {
        $expression = $this->createMockExpression(
            $expressionSetup['operator'] ?? null,
            $expressionSetup['conditions'] ?? [],
            $expressionSetup['expressions'] ?? []
        );

        $result = $this->callPrivateMethod(
            $this->ruleEngineService,
            'evaluateExpression',
            [$expression, $data]
        );
        $this->assertEquals($expectedResult, $result, "Expression evaluation failed for operator {$expression->op}");
    }

    public static function expressionDataProvider(): array
    {
        $userObj = new stdClass();
        $userObj->status = 'active';
        $userObj->level = 10;
        $userObj->is_flagged = false;
        $data = ['user' => $userObj, 'system_mode' => 'maintenance'];

        return [
            // AND: all true
            [['operator' => 'AND', 'conditions' => [
                ['attribute' => 'user.status', 'operator' => '==', 'value' => 'active'],
                ['attribute' => 'user.level', 'operator' => '>', 'value' => 5],
            ]], $data, true],
            // AND: one false
            [['operator' => 'AND', 'conditions' => [
                ['attribute' => 'user.status', 'operator' => '==', 'value' => 'active'],
                ['attribute' => 'user.level', 'operator' => '<', 'value' => 5], // false
            ]], $data, false],
            // OR: one true
            [['operator' => 'OR', 'conditions' => [
                ['attribute' => 'user.status', 'operator' => '==', 'value' => 'inactive'], // false
                ['attribute' => 'user.level', 'operator' => '>', 'value' => 5],    // true
            ]], $data, true],
            // OR: all false
            [['operator' => 'OR', 'conditions' => [
                ['attribute' => 'user.status', 'operator' => '==', 'value' => 'inactive'],
                ['attribute' => 'user.level', 'operator' => '<', 'value' => 5],
            ]], $data, false],
            // NOT: condition is false, so NOT is true
            [['operator' => 'NOT', 'conditions' => [
                ['attribute' => 'user.is_flagged', 'operator' => '==', 'value' => true], // this is false
            ]], $data, true],
            // NOT: condition is true, so NOT is false
            [['operator' => 'NOT', 'conditions' => [
                ['attribute' => 'user.status', 'operator' => '==', 'value' => 'active'], // this is true
            ]], $data, false],
            // Nested: (user.status == active) AND (user.level > 20 OR user.is_flagged == false)
            // (true) AND (false OR true) -> true
            [['operator' => 'AND', 'conditions' => [
                ['attribute' => 'user.status', 'operator' => '==', 'value' => 'active'], // true
            ], 'expressions' => [
                ['operator' => 'OR', 'conditions' => [
                    ['attribute' => 'user.level', 'operator' => '>', 'value' => 20],     // false
                    ['attribute' => 'user.is_flagged', 'operator' => '==', 'value' => false], // true
                ]], // This OR expression is true
            ]], $data, true],
             // Nested: (user.status == active) AND (user.level > 20 AND user.is_flagged == true)
            // (true) AND (false AND false) -> false
            [['operator' => 'AND', 'conditions' => [
                ['attribute' => 'user.status', 'operator' => '==', 'value' => 'active'], // true
            ], 'expressions' => [
                ['operator' => 'AND', 'conditions' => [
                    ['attribute' => 'user.level', 'operator' => '>', 'value' => 20],     // false
                    ['attribute' => 'user.is_flagged', 'operator' => '==', 'value' => true], // false
                ]], // This AND expression is false
            ]], $data, false],
            // Expression with no operator, only conditions (implicit AND)
            [['conditions' => [
                ['attribute' => 'user.status', 'operator' => '==', 'value' => 'active'],
                ['attribute' => 'user.level', 'operator' => '>=', 'value' => 10],
            ]], $data, true],
            // Empty expression (no op, no conditions, no child expressions) -> should default to true as per logic
            [[], $data, true],
            // OR expression with no children or conditions -> should default to false
            [['operator' => 'OR'], $data, false],
        ];
    }

    // --- Tests for executeRuleActions ---
    public function testExecuteRuleActions()
    {
        $ruleMock = $this->createMock(Rule::class);
        $ruleMock->name = 'Test Rule For Actions';

        $action1Mock = $this->createMock(RuleAction::class);
        $action1Mock->name = 'assignRole'; // This corresponds to 'actionAssignRole' method in RuleEngineService
        // Mocking params for actionAssignRole
        $action1RuleModel = new stdClass();
        $action1RuleModel->name = 'role';
        $action1RuleModel->attribute = 'admin';
        $action1Mock->model = $action1RuleModel; // If params are derived from model
        // Or, if RuleAction has a direct 'params' property (e.g. JSON decoded)
        // $action1Mock->params = ['role' => 'admin']; // This is an assumption on RuleAction model

        $action2Mock = $this->createMock(RuleAction::class);
        $action2Mock->name = 'sendWelcomeEmail';
        $action2RuleModel = new stdClass();
        $action2RuleModel->name = 'template';
        $action2RuleModel->attribute = 'new_user_template_v2';
        $action2Mock->model = $action2RuleModel;
        // $action2Mock->params = ['template' => 'new_user_template_v2'];


        $ruleMock->ruleActions = [$action1Mock, $action2Mock]; // Relation

        $player = new stdClass(); $player->id = 'player123'; $player->email = 'test@example.com';
        $data = ['player' => $player, 'clientId' => 'clientAbc'];

        $outcomes = $this->callPrivateMethod($this->ruleEngineService, 'executeRuleActions', [$ruleMock, $data]);

        $this->assertCount(2, $outcomes);

        // Check outcome from actionAssignRole
        $this->assertEquals('success', $outcomes[0]['outcomeStatus']);
        $this->assertEquals('PLAYER_ROLE_UPDATED', $outcomes[0]['outcomeType']);
        $this->assertEquals(['user_id' => 'player123', 'role_assigned' => 'admin'], $outcomes[0]['outcomeData']);
        $this->assertEquals('player', $outcomes[0]['broadcastScope']);
        $this->assertEquals('clientAbc', $outcomes[0]['broadcastTargetId']);


        // Check outcome from actionSendWelcomeEmail
        $this->assertEquals('success', $outcomes[1]['outcomeStatus']);
        $this->assertEquals('INTERNAL_NOTIFICATION', $outcomes[1]['outcomeType']);
        $this->assertEquals(['email' => 'test@example.com', 'template' => 'new_user_template_v2'], $outcomes[1]['outcomeData']);
        $this->assertEquals('none', $outcomes[1]['broadcastScope']);
    }

    // --- Tests for processTrigger ---
    // Mocking Rule::find() is complex without a proper Yii environment or DI for rule fetching.
    // This test will be simplified or focus on the logic *after* rules are hypothetically fetched.
    // For now, I'll skip the full processTrigger test due to the static call `Rule::find()`.
    // A better approach would be to refactor RuleEngineService to accept a rule provider.

    // Example of how one might test processTrigger if Rule::find() could be mocked easily or rules injected:
    /*
    public function testProcessTriggerFetchesAndProcessesRules()
    {
        // This requires a way to mock Rule::find()->where()->all()
        // For example, using a mocking library like Mockery for static methods, or Yii's test utilities.
        // Or refactor RuleEngineService to inject a "RuleProvider".

        // Simplified: Assume rules are passed directly (if service was refactored)
        $mockRule1 = $this->createMock(Rule::class);
        $mockRule1->name = "Rule 1";
        $mockRootExpr1 = $this->createMockExpression('AND', [['attribute' => 'user.status', 'operator' => '==', 'value' => 'active']]);
        $mockRule1->method('getRootExpression')->willReturn($mockRootExpr1); // Mocking relation
        $mockAction1 = $this->createMock(RuleAction::class);
        $mockAction1->name = 'assignRole';
        $mockAction1->params = ['role' => 'member'];
        $mockRule1->ruleActions = [$mockAction1];


        $mockRule2 = $this->createMock(Rule::class);
        $mockRule2->name = "Rule 2";
        $mockRootExpr2 = $this->createMockExpression('AND', [['attribute' => 'user.level', 'operator' => '>', 'value' => 100]]); // Condition false
        $mockRule2->method('getRootExpression')->willReturn($mockRootExpr2);
        $mockRule2->ruleActions = [];


        // This part is tricky due to Rule::find() being static.
        // $ruleModelStaticMock = // ... some static mocking for Rule::find()
        // $ruleModelStaticMock->expects($this->once()) // ...
        //                     ->method('find') // ...
        //                     ->willReturnSelf() // ... for chain
        //                     ->method('where') // ...
        //                     ->willReturnSelf() // ...
        //                     ->method('all') // ...
        //                     ->willReturn([$mockRule1, $mockRule2]);


        $user = new stdClass(); $user->status = 'active'; $user->level = 10;
        $data = ['user' => $user, 'clientId' => 'testClient'];

        // If RuleEngineService could take rules directly for testing:
        // $outcomes = $this->ruleEngineService->processTriggerWithRules("test_trigger", $data, [$mockRule1, $mockRule2]);

        // For now, we can't test the DB fetching part easily.
        // We can, however, test the logic if we assume rules are correctly passed to evaluateRuleConditions and executeRuleActions.
        // This is indirectly covered by tests for executeRuleActions and evaluateExpression.

        $this->markTestIncomplete('Full processTrigger test requires mocking static AR methods or service refactoring.');
    }
    */

}

// Note: If RuleModel, RuleCondition, RuleAction, RuleExpression models have complex logic
// in their getters or relations that are relied upon by RuleEngineService,
// then using stdClass for mocking them might not be sufficient, and proper mocks
// (e.g. with $this->createMock(RuleModel::class)) would be needed,
// along with mocking their relevant methods.
// The current tests for evaluateSingleCondition and evaluateExpression use stdClass
// for simplicity, assuming direct property access is what's needed.
// The test for executeRuleActions uses PHPUnit's createMock for Rule and RuleAction.

?>
