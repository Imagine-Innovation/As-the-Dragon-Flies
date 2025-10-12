<?php

use frontend\widgets\Button;
use frontend\widgets\MissionElement;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\db\ActiveRecord[] $properties */
/** @var int $parentId */
/** @var string $type */
/** @var string $class */
if ($properties) {
    $firstModel = $properties[0];
    $propertyNames = $firstModel->attributes();
} else {
    $propertyNames = [];
}

$url = Url::toRoute(['mission/add-detail', 'parentId' => $parentId, 'type' => $type]);
$aOrAn = strtoupper(substr($type, 0, 1)) === 'A' ? "an" : "a";
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
                'icon' => 'bi-plus-square',
                'title' => "Add {$aOrAn} “{$type}”",
                'isCta' => true,
            ])
            ?>
        </div>
    </article>
</div>
