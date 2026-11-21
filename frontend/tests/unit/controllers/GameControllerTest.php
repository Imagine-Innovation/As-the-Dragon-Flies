<?php

declare(strict_types=1);

namespace frontend\tests\unit\controllers;

use common\models\Outcome;
use frontend\controllers\GameController;
use ReflectionMethod;

class GameControllerTest extends \Codeception\Test\Unit
{

    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;

    /**
     * The helper under test is private: it only serves the Ajax response of
     * the controller, and its unitary detection logic is what the response
     * `hasItemOutcome` field relies on.
     *
     * @param list<Outcome> $outcomes
     * @return bool
     */
    private function hasItemOutcome(array $outcomes): bool
    {
        $method = new ReflectionMethod(GameController::class, 'hasItemOutcome');
        $method->setAccessible(true);
        $result = $method->invoke(null, $outcomes);
        $this->assertIsBool($result);
        return $result;
    }

    /**
     *
     * @param non-negative-int $itemId
     * @return Outcome
     */
    private function outcomeWithItem(int $itemId): Outcome
    {
        return new Outcome(['item_id' => $itemId]);
    }

    /**
     *
     * @return Outcome
     */
    private function outcomeWithoutItem(): Outcome
    {
        $outcome = new Outcome();
        $this->assertNull($outcome->item_id);
        return $outcome;
    }

    public function testSuccessOutcomeGrantingAnItemIsDetected(): void
    {
        $outcomes = [$this->outcomeWithItem(218)];
        $this->assertTrue($this->hasItemOutcome($outcomes));
    }

    public function testSuccessOutcomeWithoutItemIsNotDetected(): void
    {
        $outcomes = [$this->outcomeWithoutItem()];
        $this->assertFalse($this->hasItemOutcome($outcomes));
    }

    public function testSuccessOutcomeWithUnsetItemIdIsNotDetected(): void
    {
        $outcomes = [new Outcome()];
        $this->assertFalse($this->hasItemOutcome($outcomes));
    }

    public function testEmptyOutcomeListIsNotDetected(): void
    {
        $outcomes = [];
        $this->assertFalse($this->hasItemOutcome($outcomes));
    }

    public function testItemIsDetectedAmongMixedOutcomes(): void
    {
        $outcomes = [
            $this->outcomeWithoutItem(),
            $this->outcomeWithItem(218),
            $this->outcomeWithoutItem(),
            $this->outcomeWithItem(92),
        ];
        $this->assertTrue($this->hasItemOutcome($outcomes));
    }
}
