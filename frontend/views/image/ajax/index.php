<?php

use common\models\CharacterClass;
use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;
use frontend\widgets\CheckBox;

/** @var yii\web\View $this */
/** @var common\models\Image $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
$classes = CharacterClass::find()->All();

$checked = [];

foreach ($models as $model) {
    foreach ($classes as $class) {
        $checked[$model->id][$class->id] = false;
        foreach ($model->classImages as $classImage) {
            if ($classImage->class_id === $class->id) {
                $checked[$model->id][$class->id] = true;
                break;
            }
        }
    }
}
?>
<div class="card">
    <div class="card-body">
        <?=
        RecordCount::widget([
            'count' => $count,
            'model' => 'image',
            'adjective' => 'available',
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Thumbnails</th>
                        <?php foreach ($classes as $class): ?>
                            <th class="text-center"><?= $class->name ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <th scope="row">
                                <img src="img/character/<?= $model->file_name ?>" class="image-thumbnail">
                            </th>
                            <?php foreach ($classes as $class): ?>
                                <td class="text-center">
                                    <?=
                                    CheckBox::widget([
                                        'id' => "image-{$model->id}-{$class->id}",
                                        'onclick' => "ImageManager.setClass({$model->id}, {$class->id}, '{$class->name}');",
                                        'checked' => $checked[$model->id][$class->id] ? "checked" : "",
                                        'title' => $class->name
                                    ])
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <?=
        Pagination::widget([
            'page' => $page,
            'pageCount' => $pageCount,
            'limit' => $limit,
        ])
        ?>
        <!-- End Pagination -->
    </div>
</div>
