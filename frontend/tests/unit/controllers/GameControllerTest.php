<?php

declare(strict_types=1);

namespace frontend\tests\unit\controllers;

use common\models\Outcome;
use frontend\controllers\GameController;

class TestGameController extends GameController
{
    /**
     * The helper only serves the Ajax response of the controller: its
     * detection logic is what the response `hasItemOutcome` field relies on.
     *
     * @param array<Outcome> $outcomes
     * @return bool
     */
    public static function hasItemOutcome(array $outcomes): bool
    {
        return parent::hasItemOutcome($outcomes);
    }
}

class GameControllerTest extends \Codeception\Test\Unit
{

    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;

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
        $outcome = $this->outcomeWithItem(218);
        $this->assertTrue(TestGameController::hasItemOutcome([$outcome]));
    }

    public function testSuccessOutcomeWithoutItemIsNotDetected(): void
    {
        $outcome = $this->outcomeWithoutItem();
        $this->assertFalse(TestGameController::hasItemOutcome([$outcome]));
    }

    public function testSuccessOutcomeWithUnsetItemIdIsNotDetected(): void
    {
        $outcome = new Outcome();
        $this->assertFalse(TestGameController::hasItemOutcome([$outcome]));
    }

    public function testEmptyOutcomeListIsNotDetected(): void
    {
        $this->assertFalse(TestGameController::hasItemOutcome([]));
    }

    public function testItemIsDetectedAmongMixedOutcomes(): void
    {
        $outcomes = [
            $this->outcomeWithoutItem(),
            $this->outcomeWithItem(218),
            $this->outcomeWithoutItem(),
            $this->outcomeWithItem(92),
        ];
        $this->assertTrue(TestGameController::hasItemOutcome($outcomes));
    }
}
