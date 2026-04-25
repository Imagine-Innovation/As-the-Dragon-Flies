<?php

namespace common\components;

use common\models\Mission;
use common\widgets\MarkDown;
use Yii;
use yii\base\Component;

class NarrativeComponent extends Component
{

    const DETAILS = ['decors', 'npcs', 'monsters'];

    public ?Mission $mission = null;

    /**
     *
     * @param array<string, mixed> $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     *
     * @return array<string>
     */
    public function missionDecription(): array
    {
        if ($this->mission === null) {
            return ['The mission has not been found, even by the most learned magicians'];
        }

        $narrative = ["Mission: {$this->mission->name}"];
        if ($this->mission->description) {
            $narrative[] = MarkDown::widget(['content' => $this->mission->description]);
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
    public function renderDescription(): string
    {
        $descriptions = $this->missionDecription();
        $text = implode(PHP_EOL, $descriptions);
        return $text;
    }

    /**
     *
     * @param string $details
     * @return array<string>
     */
    private function describeDetail(string $details): array
    {
        $narrative = [];
        $detailList = $this->mission->$details;
        foreach ($detailList as $detail) {
            $narrative[] = $detail->description ? MarkDown::widget(['content' => $detail->description]) : $detail->name;
        }
        return $narrative;
    }
}
