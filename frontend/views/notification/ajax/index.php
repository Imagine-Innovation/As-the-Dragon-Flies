<?php

use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;

/** @var yii\web\View $this */
/** @var common\models\Notification $models */
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
            'model' => 'notification',
            'adjective' => 'raised',
        ])
        ?>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Quest</th>
                        <th>Raised by</th>
                        <th>At</th>
                        <th>Message</th>
                        <th>Acknowledged</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($models as $model): ?>
                        <tr>
                            <th scope="row"><?= $model->quest->name ?></th>
                            <td><?= $model->sender->name ?></td>
                            <td class="text-center"><?= Yii::$app->formatter->asDateTime($model->created_at, 'dd/MM/yyyy HH:mm') ?></td>
                            <td><?= $model->message ?></td>
                            <td><?= $model->acknowledged ? '<i class="bi bi-check-lg"></i>' : '&nbsp;' ?></td>
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
