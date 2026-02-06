<?php

namespace frontend\widgets;

use common\components\AppStatus;
use common\models\Outcome;
use frontend\widgets\Button;
use yii\base\Widget;

class ActionOutcomes extends Widget
{
    const HR = '<hr class="border border-warning border-1 opacity-50 w-50"><hr>';

    /** @var array<Outcome> $outcomes */
    public ?array $outcomes = [];
    public ?string $diceRoll = null;
    public ?AppStatus $status;
    public ?int $hpLoss = 0;
    public ?bool $isFree = true;
    public ?int $questProgressId = null;
    public ?int $nextMissionId = null;

    /**
     *
     * @return string
     */
    public function run(): string
    {
        $canReplay = false;
        $status = $this->status ?? AppStatus::SUCCESS;
        $html = "<p>{$this->diceRoll}: the action {$status->getActionAdjective()}</p>" . PHP_EOL;
        $html .= $this->hpLoss > 0 ? "<p>You lost {$this->hpLoss} hit points</p>" . PHP_EOL : '';

        if (empty($this->outcomes)) {
            $html .= "Something happened, that's for sure, but I don't really know what" . PHP_EOL;
        } else {
            foreach ($this->outcomes as $outcome) {
                $html .= self::HR;
                $actionOutcome = $this->getActionOutcome($outcome);
                $html .= $this->render('action-outcome', ['outcome' => $outcome, 'actionOutcome' => $actionOutcome]);
                $canReplay = $canReplay || $outcome->can_replay;
            }
        }
        $html .= self::HR;

        if ($this->isFree) {
            $button = Button::widget([
                'icon' => 'bi-arrow-repeat',
                'title' => 'Try another action',
                'isCta' => true,
                'ariaParams' => ['data-bs-dismiss' => 'modal'],
            ]);
        } else {
            $button = Button::widget([
                'icon' => 'bi-escape',
                'title' => 'Finish your turn',
                'isCta' => true,
                'onclick' => "vtt.moveToNextPlayer({$this->questProgressId}, {$this->nextMissionId}); return false;",
                'ariaParams' => ['data-bs-dismiss' => 'modal'],
            ]);
        }
        return $html . $button;
    }

    private function getActionOutcome(Outcome $outcome): string
    {
        $actionOutcome = '';
        if ($outcome->description) {
            $actionOutcome .= '<p>' . nl2br($outcome->description) . '</p>' . PHP_EOL;
        }

        if ($outcome->gained_gp > 0) {
            $actionOutcome .= "<p>You gained {$outcome->gained_gp} gold pieces</p>" . PHP_EOL;
        }

        if ($outcome->gained_xp > 0) {
            $actionOutcome .= "<p>You gained {$outcome->gained_xp} experience points</p>" . PHP_EOL;
        }

        if ($outcome->item_id) {
            $actionOutcome .= "<p>You now have a {$outcome->item?->name} in your back bag</p>" . PHP_EOL;
        }
        return $actionOutcome;
    }
}
