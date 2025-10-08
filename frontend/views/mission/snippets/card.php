<?php

use frontend\widgets\Button;
use frontend\widgets\MissionElement;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\db\ActiveRecord[] $properties */
/** @var int $missionId */
/** @var string $type */
/** @var string $class */
if ($properties) {
    $firstModel = $properties[0];
    $propertyNames = $firstModel->attributes();
} else {
    $propertyNames = [];
}

$url = Url::toRoute(['mission/add-detail', 'missionId' => $missionId, 'type' => $type]);
?>
<div class="<?= $class ?? 'col' ?>">
    <article class="card mb-3 h-100" id="Mission<?= $type ?>">
        <div class="card-header">
            <h6 class="card-title"><?= $type ?></h6>
        </div>
        <div class="card-body">
            <?=
            MissionElement::widget([
                'properties' => $properties,
                'propertyNames' => $propertyNames,
                'type' => $type,
            ])
            ?>
            <?=
            Button::widget([
                'url' => $url,
                'icon' => 'dnd-badge',
                'title' => "Add a {$type}",
                'isCta' => true,
            ])
            ?>
        </div>
    </article>
</div>
