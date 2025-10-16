<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Url;

class MissionElement extends Widget
{

    public $properties;
    public $type;
    public $propertyNames;

    public function run() {
        return $this->content();
    }

    private function content() {
        $html = "";
        if ($this->properties) {
            $id1 = $this->propertyNames[0];
            $id2 = $this->propertyNames[1];
            $id3 = $this->propertyNames[2];

            Yii::debug("*** Debug **** MissionElement - id1={$id1}, id2={$id2}, id3={$id3}");

            $html .= "<ul>";
            foreach ($this->properties as $property) {
                Yii::debug($property);
                $params = [
                    $id1 => $property->$id1,
                    $id2 => $property->$id2,
                    $id3 => $property->$id3,
                ];

                Yii::debug($params);

                $displayName = match ($this->type) {
                    'Prerequisite' => $property->previousAction->name,
                    'Trigger' => $property->nextAction->name,
                    default => $property->name,
                };

                $hrefEdit = Url::toRoute(['mission/edit-detail', 'jsonParams' => json_encode($params), 'type' => $this->type]);
                $html .= "<li><a href=\"{$hrefEdit}\" role=\"button\"><i class=\"bi bi-pencil-square\"></i> {$displayName}</a></li>";
            }
            $html .= "</ul>";
        } else {
            $html .= "<p>No {$this->type} has been defined yet</p>";
        }
        return $html;
    }
}
