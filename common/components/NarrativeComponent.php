<?php

namespace common\components;

use common\models\Mission;
use Yii;
use yii\base\Component;

class NarrativeComponent extends Component
{

    const DETAILS = ['decors', 'npcs', 'passages', 'monsters'];

    public Mission $mission;

    public function __construct($config = []) {
        parent::__construct($config);
    }

    public function missionDecription(): array {
        if ($this->mission->isNewRecord) {
            return ['The mission has not been found, even by the most learned magicians'];
        }

        $narrative = ["Mission: {$this->mission->name}"];
        if ($this->mission->description) {
            $narrative[] = nl2br($this->mission->description);
        }

        foreach (self::DETAILS as $details) {
            $narrative = [...$narrative, ...$this->describeDetail($details)];
        }

        return $narrative;
    }

    public function renderDescription(): string {
        $descriptions = $this->missionDecription();
        $text = '';
        $i = 0;
        foreach ($descriptions as $description) {
            $tag = $i++ == 0 ? "h3" : "p";
            $text .= "<{$tag} class=\"card-text\">{$description}</{$tag}>";
        }
        return $text;
    }

    private function describeDetail(string $details): array {
        $narrative = [];
        $detailList = $this->mission->$details;
        foreach ($detailList as $detail) {
            $narrative[] = $detail->description ? nl2br($detail->description) : $detail->name;
        }
        return $narrative;
    }
}
