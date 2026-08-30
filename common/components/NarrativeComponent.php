<?php

namespace common\components;

use common\models\Mission;
use common\widgets\MarkDown;
use Yii;
use yii\base\Component;

class NarrativeComponent extends Component
{

    const SECTIONS = ['decors', 'npcs', 'monsters'];

    public ?Mission $mission = null;
    public ?bool $title = true; // defines if mission name should be rendered or not

    /** @var array<string> $sections */
    public array $sections = self::SECTIONS; // List of sections to be included

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
     * @return string
     */
    public function renderDescription(): string
    {
        $descriptions = $this->missionDecription();
        $renderedDescription = [];

        foreach ($descriptions as $description) {
            $renderedDescription[] = MarkDown::widget(['content' => $description]);
        }
        $text = implode(PHP_EOL, $renderedDescription);
        return $text;
    }

    /**
     *
     * @return string
     */
    public function rawDescription(): string
    {
        $descriptions = $this->missionDecription();
        $text = implode(PHP_EOL, $descriptions);
        return $text;
    }

    /**
     *
     * @return array<string>
     */
    private function missionDecription(): array
    {
        if ($this->mission === null) {
            return ['The mission has not been found, even by the most learned magicians'];
        }

        $narrative = [];
        if ($this->title) {
            $narrative[] = "Mission: {$this->mission->name}";
        }
        if ($this->mission->description) {
            $narrative[] = $this->mission->description;
        }

        foreach ($this->sections as $section) {
            $narrative = [...$narrative, ...$this->describeDetail($section)];
        }

        return $narrative;
    }

    /**
     *
     * @param string $section
     * @return array<string>
     */
    private function describeDetail(string $section): array
    {
        $narrative = [];
        $detailList = $this->mission->$section;
        foreach ($detailList as $detail) {
            $narrative[] = $detail->description ? $detail->description : $detail->name;
        }
        return $narrative;
    }
}
