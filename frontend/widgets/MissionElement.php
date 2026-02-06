<?php

namespace frontend\widgets;

use common\components\AppStatus;
use Yii;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\helpers\Url;

class MissionElement extends Widget
{
    /** @var \yii\db\ActiveRecord[] $properties: list of properties associated to a mission */
    public array $properties = [];

    /** @var string $type: property type */
    public string $type = 'unkown';

    /** @var array<string> $propertyNames array of every attributes defines within the property */
    public array $propertyNames = [];

    public function run()
    {
        if ($this->properties) {
            return $this->listContent($this->properties);
        }
        return "<p>No {$this->type} has been defined yet</p>";
    }

    /**
     *
     * @param array<ActiveRecord> $properties
     * @return string
     */
    private function listContent(array $properties): string
    {
        $liElements = '';
        foreach ($properties as $property) {
            $liElements .= $this->liElement($property, $this->propertyNames);
        }
        $listContent = "<ul>{$liElements}</ul>";
        return $listContent;
    }

    /**
     *
     * @param ActiveRecord $property
     * @return string
     */
    private function getPropertyStatusLabel(ActiveRecord $property): string
    {
        if (!$property->hasAttribute('status') || $property->status === null) {
            return '';
        }

        try {
            $appStatus = AppStatus::from($property->status);
            $label = $appStatus->getLabel();
        } catch (\ValueError $e) {
            $label = 'Unknown Status';
        }
        return $label;
    }

    /**
     *
     * @param ActiveRecord $property
     * @return string
     */
    private function displayName(ActiveRecord $property): string
    {
        $status = strtolower($this->getPropertyStatusLabel($property));
        return match ($this->type) {
            'Prerequisite' => "\"{$property->previousAction->name}\" if {$status}",
            'Trigger' => "\"{$property->nextAction->name}\" when {$status}",
            'Outcome' => "\"{$property->name}\" when {$status}",
            default => $property->name,
        };
    }

    /**
     *
     * @param ActiveRecord $property
     * @param array<string> $propertyNames
     * @return string
     */
    private function liElement(ActiveRecord $property, array $propertyNames): string
    {
        $attribute1 = $propertyNames[0];
        $attribute2 = $propertyNames[1];
        $attribute3 = $propertyNames[2];

        $params = [
            $attribute1 => $property->$attribute1,
            $attribute2 => $property->$attribute2,
            $attribute3 => $property->$attribute3,
        ];

        $displayName = $this->displayName($property);

        $hrefEdit = Url::toRoute(['mission/edit-detail', 'jsonParams' => json_encode($params), 'type' => $this->type]);

        return "<li><a href=\"{$hrefEdit}\" role=\"button\"><i class=\"bi bi-pencil-square\"></i> {$displayName}</a></li>";
    }
}
