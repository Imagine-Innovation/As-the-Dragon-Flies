<?php

namespace frontend\widgets;

use common\components\AppStatus;
use common\helpers\WebResourcesHelper;
use common\models\Outcome;
use common\widgets\MarkDown;
use Yii;
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
    public ?int $storyId = null;
    public ?string $language = 'en';

    /**
     *
     * @return string
     */
    public function run(): string
    {
        $status = $this->status ?? AppStatus::SUCCESS;
        $actionStatus = Yii::t('app/game', "the action {$status->getActionAdjective()}", $language ?? 'en');
        $html = "<p>{$this->diceRoll}: {$actionStatus}</p>\n";

        $lostHp = Yii::t('app/game', 'You lost {hpLoss} hit points', ['hpLoss' => $this->hpLoss], $language ?? 'en');
        $html .= $this->hpLoss > 0 ? "<p>{$lostHp}</p>\n" : '';

        if (empty($this->outcomes)) {
            $html .= Yii::t('app/game', 'Something happened but I don\'t know what', $language ?? 'en') . PHP_EOL;
        } else {
            $storyRoot = WebResourcesHelper::storyRootPath($this->storyId);
            foreach ($this->outcomes as $outcome) {
                $html .= self::HR;
                Yii::debug("*** debug *** ActionOutcomes widget - MD description = " . implode("<br>", explode("\n", $outcome->description)));
                $description = MarkDown::widget(['content' => $outcome->description]);
                Yii::debug("*** debug *** ActionOutcomes widget - sanitizeWithCache description = " . implode("<br>", explode("\n", $description)));
                $actionOutcome = $this->getActionOutcome($outcome);
                $html .= $this->render('action-outcome', [
                    'outcomeName' => $outcome->name,
                    'image' => $outcome->image,
                    'description' => $description,
                    'actionOutcome' => $actionOutcome,
                    'storyRoot' => $storyRoot,
                ]);
            }
        }
        $html .= self::HR;

        return $html;
    }

    /**
     *
     * @param Outcome $outcome
     * @return string
     */
    private function getActionOutcome(Outcome $outcome): string
    {
        $actionOutcome = '';
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
