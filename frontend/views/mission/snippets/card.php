<?php

use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\db\ActiveRecord[] $properties */
/** @var int $missionId */
/** @var string $type */
/** @var string $class */
if ($properties) {
    $firstModel = $properties[0];
    $propertyNames = $firstModel->attributes();
}
?>
<div class="<?= $class ?? 'col' ?>">
    <article class="card mb-3 h-100" id="Mission<?= $type ?>">
        <div class="card-header">
            <h6 class="card-title"><?= $type ?></h6>
        </div>
        <div class="card-body">
            <?php
            if ($properties) {
                echo "<ul>";
                foreach ($properties as $property) {
                    $id1 = $propertyNames[0];
                    $id2 = $propertyNames[1];
                    $id3 = $propertyNames[2];
                    $params = [
                        $id1 => $property->$id1,
                        $id2 => $property->$id2,
                        $id3 => $property->$id3,
                    ];
                    $hrefEdit = Url::toRoute(['mission/edit-detail', 'jsonParams' => json_encode($params), 'type' => $type]);
                    echo "<li>{$property->name} ";
                    echo "<a href=\"{$hrefEdit}\" role=\"button\"><i class=\"bi bi-pencil-square\"></i></a>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No {$type} has been defined yet</p>";
            }
            ?>
            <?=
            Button::widget([
                'url' => Url::toRoute(['mission/add-detail', 'missionId' => $missionId, 'type' => $type]),
                'icon' => 'dnd-badge',
                'title' => "Add a {$type}",
                'isCta' => true,
            ])
            ?>
        </div>
    </article>
</div>
