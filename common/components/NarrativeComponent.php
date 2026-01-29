<?php

namespace common\components;

use common\models\Mission;
use yii\helpers\Html;
use Yii;
use yii\base\Component;

class NarrativeComponent extends Component
{

    const DETAILS = ['decors', 'npcs', 'passages', 'monsters'];

    public ?Mission $mission = null;

    /**
     *
     * @param array<string, mixed> $config
     */
    public function __construct($config = []) {
        parent::__construct($config);
    }

    /**
     *
     * @return array<string>
     */
    public function missionDecription(): array {
        if ($this->mission === null) {
            return ['The mission has not been found, even by the most learned magicians'];
        }

        $narrative = ["Mission: " . Html::encode($this->mission->name)];
        if ($this->mission->description) {
            $narrative[] = nl2br($this->mission->description);
        }

        foreach (self::DETAILS as $details) {
            $narrative = [...$narrative, ...$this->describeDetail($details)];
        }

        return $narrative;
    }

    /**
     *
     * @return string
     */
    public function renderDescription(): string {
        $descriptions = $this->missionDecription();
        $text = '';
        $i = 0;
        foreach ($descriptions as $description) {
            $tag = ($i++ === 0) ? "h3" : "p";
            $text .= "<{$tag} class=\"card-text\">{$description}</{$tag}>";
        }
        return $text;
    }

    /**
     *
     * @param string $details
     * @return array<string>
     */
    private function describeDetail(string $details): array {
        $narrative = [];
        $detailList = $this->mission->$details;
        foreach ($detailList as $detail) {
            $narrative[] = $detail->description ? nl2br($detail->description) : Html::encode($detail->name);
        }
        return $narrative;
    }
}
