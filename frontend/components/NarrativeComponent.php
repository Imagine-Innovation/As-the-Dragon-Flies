<?php

namespace frontend\components;

use common\models\Mission;
use Yii;
use yii\base\Component;

class NarrativeComponent extends Component
{

    const DETAILS = ['decors', 'npcs', 'passages', 'monsters'];

    public function missionDecription(Mission &$mission): array {
        if (!$mission) {
            return 'The mission has not been found, even by the most learned magicians';
        }

        $narrative = [];
        if ($mission->description) {
            $narrative[] = nl2br($mission->description);
        }

        foreach (self::DETAILS as $details) {
            $narrative = [...$narrative, ...$this->describeDetail($mission, $details)];
        }

        return $narrative;
    }

    private function describeDetail(Mission &$mission, string $details): array {
        $narrative = [];
        if ($mission->$details) {
            foreach ($mission->$details as $detail) {
                $narrative[] = $detail->description ? nl2br($detail->description) : $detail->name;
            }
        }
        return $narrative;
    }
}
