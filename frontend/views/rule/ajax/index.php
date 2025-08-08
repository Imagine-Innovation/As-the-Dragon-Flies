<?php

use common\helpers\Utilities;
use common\helpers\Status;
use yii\helpers\Url;
use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;
use frontend\widgets\ActionButtons;

/** @var yii\web\View $this */
/** @var common\models\Rule $models */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
?>
<div class="card">
    <div class="card-body">
        <?=
        RecordCount::widget([
            'count' => $count,
            'model' => 'rule',
            'adjective' => 'defined',
            'actions' => [
                [
                    'url' => Url::toRoute(['rule/create']),
                    'icon' => 'bi-file-earmark-plus',
                    'tooltip' => 'Create a new rule',
                ],
            ]
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Rule</th>
                        <th>Definition</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <th scope="row"><?= Status::hyperlink($model) ?></th>
                            <td><?= Utilities::encode($model->definition) ?></td>
                            <td class="text-center"><?= Status::icon($model->status) ?></td>
                            <td><?= $model->description ?></td>
                            <td>
                                <?=
                                ActionButtons::widget([
                                    'model' => $model,
                                    'mode' => 'table'
                                ])
                                ?>
                            </td>
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
