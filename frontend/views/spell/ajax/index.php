<?php

use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Spell[] $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
$icon = '<i class="bi bi-check-lg"></i>';
?>
<div class="card">
    <div class="card-body">
        <?=
        RecordCount::widget([
            'count' => $count,
            'model' => 'Spell',
            'adjective' => 'available',
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="text-center">Level</th>
                        <th class="text-center">Components</th>
                        <th class="text-center">Ritual</th>
                        <th>School</th>
                        <th>Range</th>
                        <th>Casting time</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <th scope="row">
                                <a href="<?= Url::toRoute(['spell/view', 'id' => $model->id]) ?>">
                                    <?= Html::encode($model->name) ?>
                                </a>
                            </th>
                            <td class="text-center"><?= $model->spell_level ?></td>
                            <?php
                            $components = [];
                            foreach ($model->components as $c) {
                                $components[] = $c->code;
                            }
                            ?>
                            <td class="text-center"><?= implode(', ', $components) ?></td>
                            <td class="text-center"><?= $model->is_ritual ? $icon : "&nbsp;" ?></td>
                            <td><?= $model->school->name ?></td>
                            <td><?= $model->range->name ?></td>
                            <td><?= $model->castingTime->name ?></td>
                            <td><?= $model->duration->name ?></td>
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
