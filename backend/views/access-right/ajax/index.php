<?php

use common\helpers\SpecialCheckBox;
use common\widgets\Pagination;
use common\widgets\RecordCount;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\AccessRight[] $models */
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
            'model' => 'access right',
            'adjective' => 'defined',
            'actions' => [
                [
                    'url' => Url::toRoute(['access-right/create']),
                    'icon' => 'bi-shield-plus',
                    'tooltip' => 'Add a new access right',
                ],
            ],
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Route</th>
                        <th class="text-center">Admin</th>
                        <th class="text-center">Designer</th>
                        <th class="text-center">Player</th>
                        <th class="text-center">Has Player</th>
                        <th class="text-center">In Quest</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <td scope="row"><?= $model->application ?></td>
                            <th >
                                <a href="<?= Url::toRoute(['access-right/view', 'id' => $model->id]) ?>">
                                    <?= $model->route . '/' . $model->action ?>
                                </a>
                            </th>
                            <td class="text-center"><?= SpecialCheckBox::setAccessRight($model, 'is_admin') ?></td>
                            <td class="text-center"><?= SpecialCheckBox::setAccessRight($model, 'is_designer') ?></td>
                            <td class="text-center"><?= SpecialCheckBox::setAccessRight($model, 'is_player') ?></td>
                            <td class="text-center"><?= SpecialCheckBox::setAccessRight($model, 'has_player') ?></td>
                            <td class="text-center"><?= SpecialCheckBox::setAccessRight($model, 'in_quest') ?></td>
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
